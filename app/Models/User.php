<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare('SELECT id, full_name, email, phone, role, password_hash FROM users WHERE email = :email');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (:full_name, :email, :phone, :role, :password_hash)'
        );

        $statement->execute([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'] ?? 'Пользователь',
            'password_hash' => $data['password_hash'],
        ]);
    }
}
