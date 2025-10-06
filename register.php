<?php
require __DIR__ . '/functions.php';
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = trim($_POST['phone'] ?? '');
$role = $_POST['role'] ?? 'Пользователь';
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';

$errors = [];

if ($fullName === '') {
    $errors[] = 'Укажите ФИО.';
}
if (!$email) {
    $errors[] = 'Укажите корректную почту.';
}
if ($phone === '') {
    $errors[] = 'Укажите номер телефона.';
}
if (!in_array($role, ['Пользователь', 'Админ'], true)) {
    $errors[] = 'Выберите корректную роль.';
}
if (strlen($password) < 6) {
    $errors[] = 'Пароль должен содержать не менее 6 символов.';
}
if ($password !== $passwordConfirmation) {
    $errors[] = 'Пароли не совпадают.';
}

if ($errors) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => implode(' ', $errors),
    ];
    header('Location: /index.php#register');
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (:full_name, :email, :phone, :role, :password_hash)');
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Пользователь с такой почтой уже существует.',
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Ошибка при регистрации. Попробуйте позже.',
        ];
    }
    header('Location: /index.php#register');
    exit;
}

$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Регистрация прошла успешно! Теперь вы можете авторизоваться.',
];

header('Location: /index.php');
exit;
