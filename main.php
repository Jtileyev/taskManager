<?php
require __DIR__ . '/functions.php';
ensure_logged_in();

$user = $_SESSION['user'];
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager — Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="/main.php">Task Manager</a>
        <div class="d-flex align-items-center ms-auto">
            <span class="text-white-50 me-3"><?= sanitize($user['full_name']) ?> (<?= sanitize($user['role']) ?>)</span>
            <a class="btn btn-outline-light btn-sm" href="/logout.php">Выйти</a>
        </div>
    </div>
</nav>
<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert">
                <?= sanitize($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Добро пожаловать, <?= sanitize($user['full_name']) ?>!</h1>
                        <p class="mb-0 text-muted">Здесь может находиться панель управления задачами вашей команды. Используйте верхнее меню для навигации, добавляйте задачи и отслеживайте прогресс.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Информация об аккаунте</h2>
                        <ul class="list-unstyled mb-0 text-muted">
                            <li class="mb-2"><strong>Почта:</strong> <?= sanitize($user['email']) ?></li>
                            <li class="mb-2"><strong>Телефон:</strong> <?= sanitize($user['phone']) ?></li>
                            <li><strong>Роль:</strong> <?= sanitize($user['role']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="app-footer py-3 text-center text-white-50">
    © <?= date('Y') ?> Bekpharm Task Manager
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
