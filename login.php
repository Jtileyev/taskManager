<?php
require __DIR__ . '/functions.php';
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || empty($password)) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Укажите корректную почту и пароль.',
    ];
    header('Location: /index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, full_name, email, phone, role, password_hash FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Неверная почта или пароль.',
    ];
    header('Location: /index.php');
    exit;
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'role' => $user['role'],
];

$_SESSION['flash_success'] = 'Вы успешно авторизовались!';

header('Location: /main.php');
exit;
