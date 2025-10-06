<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class TaskComment
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }

    public function create(int $taskId, int $userId, string $content): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO task_comments (task_id, user_id, content) VALUES (:task_id, :user_id, :content)'
        );

        $statement->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
            'content' => $content,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function forTask(int $taskId): array
    {
        $statement = $this->connection->prepare(
            'SELECT tc.id, tc.content, tc.created_at, tc.user_id, users.full_name AS author_name
             FROM task_comments tc
             INNER JOIN users ON users.id = tc.user_id
             WHERE tc.task_id = :task_id
             ORDER BY tc.created_at ASC'
        );

        $statement->execute(['task_id' => $taskId]);

        $comments = $statement->fetchAll();

        if (!$comments) {
            return [];
        }

        $commentIds = array_map(static fn (array $comment): int => (int) $comment['id'], $comments);

        $attachmentStatement = $this->connection->prepare(
            'SELECT comment_id, stored_name, original_name, mime_type, file_size, created_at
             FROM task_comment_attachments
             WHERE comment_id IN (' . implode(',', array_fill(0, count($commentIds), '?')) . ')
             ORDER BY created_at ASC'
        );

        $attachmentStatement->execute($commentIds);
        $attachments = $attachmentStatement->fetchAll();

        $grouped = [];

        foreach ($attachments as $attachment) {
            $commentId = (int) $attachment['comment_id'];
            $grouped[$commentId][] = $attachment;
        }

        return array_map(static function (array $comment) use ($grouped): array {
            $commentId = (int) $comment['id'];
            $comment['attachments'] = $grouped[$commentId] ?? [];

            return $comment;
        }, $comments);
    }

    public function attachFiles(int $commentId, array $files): void
    {
        if (!isset($files['name'], $files['tmp_name'], $files['error'], $files['size'], $files['type'])) {
            return;
        }

        $totalFiles = is_array($files['name']) ? count($files['name']) : 0;

        if ($totalFiles === 0) {
            return;
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/comments';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $statement = $this->connection->prepare(
            'INSERT INTO task_comment_attachments (comment_id, stored_name, original_name, mime_type, file_size)
             VALUES (:comment_id, :stored_name, :original_name, :mime_type, :file_size)'
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
                'comment_id' => $commentId,
                'stored_name' => $generatedName,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);
        }
    }
}

