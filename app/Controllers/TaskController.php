<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

class TaskController extends Controller
{
    private Task $tasks;
    private User $users;
    private TaskComment $comments;

    public function __construct()
    {
        $this->tasks = new Task();
        $this->users = new User();
        $this->comments = new TaskComment();
    }

    public function create(): void
    {
        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $errors = Session::flash('task_errors') ?? [];
        $old = Session::flash('task_old') ?? [];

        if (!isset($old['status'])) {
            $old['status'] = 'draft';
        }

        if (!isset($old['priority'])) {
            $old['priority'] = 'planned';
        }

        $this->view('tasks/create', [
            'user' => $user,
            'assignees' => $this->users->all(),
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/tasks/create');
        }

        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $title = trim($_POST['title'] ?? '');
        $status = $_POST['status'] ?? '';
        $priority = $_POST['priority'] ?? '';
        $assigneeId = isset($_POST['assignee_id']) ? (int) $_POST['assignee_id'] : null;
        $startDate = $_POST['start_date'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';
        $tagsInput = trim($_POST['tags'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $errors = [];

        if ($title === '') {
            $errors[] = 'Укажите название задачи.';
        }

        if (!array_key_exists($status, Task::statuses())) {
            $errors[] = 'Выберите корректный статус.';
        }

        if (!array_key_exists($priority, Task::priorities())) {
            $errors[] = 'Выберите корректный приоритет.';
        }

        if ($assigneeId !== null && $assigneeId !== 0) {
            if ($this->users->find($assigneeId) === null) {
                $errors[] = 'Выбранный исполнитель не найден.';
            }
        } else {
            $assigneeId = null;
        }

        $tags = [];
        if ($tagsInput !== '') {
            $tags = array_values(array_filter(array_map(static fn (string $tag): string => trim($tag), explode(',', $tagsInput)), static fn (string $tag): bool => $tag !== ''));
        }

        if ($content === '') {
            $errors[] = 'Добавьте содержание задачи.';
        }

        $decoded = null;
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (!is_array($decoded) || !isset($decoded['blocks']) || !is_array($decoded['blocks']) || count($decoded['blocks']) === 0) {
                $errors[] = 'Содержание задачи заполнено некорректно.';
            }
        }

        if ($startDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $errors[] = 'Дата начала указана неверно.';
        }

        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $errors[] = 'Дедлайн указан неверно.';
        }

        if ($startDate !== '' && $dueDate !== '' && $startDate > $dueDate) {
            $errors[] = 'Дата начала не может быть позже дедлайна.';
        }

        $old = [
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'assignee_id' => $assigneeId,
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'tags' => $tagsInput,
            'content' => $content,
        ];

        if ($errors) {
            Session::flash('task_errors', $errors);
            Session::flash('task_old', $old);
            $this->redirect('/tasks/create');
        }

        $taskId = $this->tasks->create([
            'author_id' => (int) $user['id'],
            'assignee_id' => $assigneeId,
            'title' => $title,
            'content' => $content,
            'tags' => $tags,
            'status' => $status,
            'priority' => $priority,
            'start_date' => $startDate,
            'due_date' => $dueDate,
        ]);

        if (isset($_FILES['attachments'])) {
            $this->tasks->updateAttachments($taskId, $_FILES['attachments'], (int) $user['id']);
        }

        Session::flash('success', 'Задача успешно создана.');

        $this->redirect('/tasks/view?id=' . $taskId);
    }

    public function show(): void
    {
        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $taskId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($taskId <= 0) {
            $this->redirect('/dashboard');
        }

        $task = $this->tasks->find($taskId);

        if (!$task) {
            $this->redirect('/dashboard');
        }

        $hasAccess = (int) $task['author_id'] === (int) $user['id']
            || ((int) ($task['assignee_id'] ?? 0) === (int) $user['id'])
            || ($user['role'] ?? '') === 'Админ';

        if (!$hasAccess) {
            $this->redirect('/dashboard');
        }

        $attachments = $this->tasks->attachments($taskId);
        $comments = $this->comments->forTask($taskId);

        $comments = array_map(static function (array $comment): array {
            $comment['content_html'] = render_editor_content($comment['content']);

            return $comment;
        }, $comments);

        $commentErrors = Session::flash('task_comment_errors') ?? [];
        $commentOld = Session::flash('task_comment_old') ?? null;
        $commentSuccess = Session::flash('task_comment_success');

        $task['status_label'] = Task::statusLabel($task['status']);
        $task['priority_label'] = Task::priorityLabel($task['priority']);
        $task['tags_list'] = Task::decodeTags($task['tags']);
        $task['content_html'] = render_editor_content($task['content']);

        $this->view('tasks/show', [
            'user' => $user,
            'task' => $task,
            'attachments' => $attachments,
            'comments' => $comments,
            'commentErrors' => $commentErrors,
            'commentOld' => $commentOld,
            'commentSuccess' => $commentSuccess,
        ]);
    }

    public function comment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $taskId = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
        $content = trim($_POST['comment_content'] ?? '');

        if ($taskId <= 0) {
            $this->redirect('/dashboard');
        }

        $task = $this->tasks->find($taskId);

        if (!$task) {
            $this->redirect('/dashboard');
        }

        $hasAccess = (int) $task['author_id'] === (int) $user['id']
            || ((int) ($task['assignee_id'] ?? 0) === (int) $user['id'])
            || ($user['role'] ?? '') === 'Админ';

        if (!$hasAccess) {
            $this->redirect('/dashboard');
        }

        $errors = [];

        if ($content === '') {
            $errors[] = 'Комментарий не может быть пустым.';
        }

        $decoded = null;
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (!is_array($decoded) || !isset($decoded['blocks']) || !is_array($decoded['blocks']) || count($decoded['blocks']) === 0) {
                $errors[] = 'Комментарий заполнен некорректно.';
            }
        }

        if ($errors) {
            Session::flash('task_comment_errors', $errors);
            Session::flash('task_comment_old', $content);
            $this->redirect('/tasks/view?id=' . $taskId . '#comments');
        }

        $commentId = $this->comments->create($taskId, (int) $user['id'], $content);

        if (isset($_FILES['comment_attachments'])) {
            $this->comments->attachFiles($commentId, $_FILES['comment_attachments']);
        }

        Session::flash('task_comment_success', 'Комментарий добавлен.');

        $this->redirect('/tasks/view?id=' . $taskId . '#comments');
    }
}

