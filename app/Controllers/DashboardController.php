<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Task;

class DashboardController extends Controller
{
    private Task $tasks;

    public function __construct()
    {
        $this->tasks = new Task();
    }

    public function index(): void
    {
        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $flashSuccess = Session::flash('success');

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'scope' => $_GET['scope'] ?? 'all',
        ];

        $tasks = $this->tasks->forUser((int) $user['id'], $filters);

        $tasks = array_map(static function (array $task): array {
            $task['status_label'] = Task::statusLabel($task['status']);
            $task['priority_label'] = Task::priorityLabel($task['priority']);
            $task['tags_list'] = Task::decodeTags($task['tags']);
            $task['content_preview'] = editor_extract_text($task['content'], 180);

            return $task;
        }, $tasks);

        $this->view('dashboard/index', [
            'user' => $user,
            'flashSuccess' => $flashSuccess,
            'tasks' => $tasks,
            'filters' => $filters,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }
}
