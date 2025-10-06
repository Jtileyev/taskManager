<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Task
{
    public const STATUSES = [
        'draft' => 'Черновик',
        'in_progress' => 'В работе',
        'waiting' => 'Ожидает',
        'review' => 'На проверке',
        'done' => 'Готово',
        'cancelled' => 'Отменена',
    ];

    public const PRIORITIES = [
        'planned' => 'Запланировано',
        'low' => 'Низкий',
        'medium' => 'Средний',
        'high' => 'Высокий',
    ];

    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public static function priorities(): array
    {
        return self::PRIORITIES;
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status] ?? $status;
    }

    public static function priorityLabel(string $priority): string
    {
        return self::PRIORITIES[$priority] ?? $priority;
    }

    public static function decodeTags(?string $payload): array
    {
        if ($payload === null || $payload === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($value) => is_string($value) ? trim($value) : '', $decoded), static fn ($value) => $value !== ''));
    }

    public function create(array $data): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO tasks (author_id, assignee_id, title, content, tags, status, priority, start_date, due_date)
             VALUES (:author_id, :assignee_id, :title, :content, :tags, :status, :priority, :start_date, :due_date)'
        );

        $statement->execute([
            'author_id' => $data['author_id'],
            'assignee_id' => $data['assignee_id'] ?: null,
            'title' => $data['title'],
            'content' => $data['content'],
            'tags' => $data['tags'] !== [] ? json_encode($data['tags'], JSON_UNESCAPED_UNICODE) : null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'start_date' => $data['start_date'] ?: null,
            'due_date' => $data['due_date'] ?: null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function updateAttachments(int $taskId, array $files, int $userId): void
    {
        if (!isset($files['name'], $files['tmp_name'], $files['error'], $files['size'], $files['type'])) {
            return;
        }

        $totalFiles = is_array($files['name']) ? count($files['name']) : 0;

        if ($totalFiles === 0) {
            return;
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/tasks';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $statement = $this->connection->prepare(
            'INSERT INTO task_attachments (task_id, user_id, stored_name, original_name, mime_type, file_size)
             VALUES (:task_id, :user_id, :stored_name, :original_name, :mime_type, :file_size)'
        );

        for ($index = 0; $index < $totalFiles; $index++) {
            if ((int) $files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = (string) $files['name'][$index];
            $tmpPath = (string) $files['tmp_name'][$index];
            $mimeType = (string) $files['type'][$index];
            $fileSize = (int) $files['size'][$index];

            if (!is_uploaded_file($tmpPath)) {
                continue;
            }

            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $generatedName = bin2hex(random_bytes(16));

            if ($extension !== '') {
                $generatedName .= '.' . strtolower($extension);
            }

            $destination = $uploadDir . '/' . $generatedName;

            if (!move_uploaded_file($tmpPath, $destination)) {
                continue;
            }

            $statement->execute([
                'task_id' => $taskId,
                'user_id' => $userId,
                'stored_name' => $generatedName,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);
        }
    }

    public function forUser(int $userId, array $filters = []): array
    {
        $query = [];
        $query[] = 'SELECT DISTINCT t.*, author.full_name AS author_name, assignee.full_name AS assignee_name';
        $query[] = 'FROM tasks t';
        $query[] = 'INNER JOIN users author ON author.id = t.author_id';
        $query[] = 'LEFT JOIN users assignee ON assignee.id = t.assignee_id';
        $query[] = 'WHERE (t.author_id = :user_id OR t.assignee_id = :user_id)';

        $parameters = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $query[] = 'AND t.status = :status';
            $parameters['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $query[] = 'AND t.priority = :priority';
            $parameters['priority'] = $filters['priority'];
        }

        $scope = $filters['scope'] ?? 'all';

        if ($scope === 'assigned') {
            $query[] = 'AND t.assignee_id = :assigned_user';
            $parameters['assigned_user'] = $userId;
        } elseif ($scope === 'authored') {
            $query[] = 'AND t.author_id = :author_user';
            $parameters['author_user'] = $userId;
        }

        $query[] = 'ORDER BY t.updated_at DESC, t.created_at DESC';

        $statement = $this->connection->prepare(implode(' ', $query));
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function find(int $taskId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT t.*, author.full_name AS author_name, assignee.full_name AS assignee_name,
                    author.email AS author_email, assignee.email AS assignee_email
             FROM tasks t
             INNER JOIN users author ON author.id = t.author_id
             LEFT JOIN users assignee ON assignee.id = t.assignee_id
             WHERE t.id = :id'
        );

        $statement->execute(['id' => $taskId]);
        $task = $statement->fetch();

        return $task ?: null;
    }

    public function attachments(int $taskId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, stored_name, original_name, mime_type, file_size, created_at
             FROM task_attachments
             WHERE task_id = :task_id
             ORDER BY created_at ASC'
        );

        $statement->execute(['task_id' => $taskId]);

        return $statement->fetchAll();
    }
}

