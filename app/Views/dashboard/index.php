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
        <a class="navbar-brand" href="/dashboard">Task Manager</a>
        <div class="d-flex align-items-center ms-auto">
            <span class="text-white-50 me-3"><?= e($user['full_name']) ?> (<?= e($user['role']) ?>)</span>
            <a class="btn btn-outline-light btn-sm" href="/logout">Выйти</a>
        </div>
    </div>
</nav>
<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success" role="alert">
                <?= e($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <div class="text-center py-5 text-muted">
            <p class="mb-2">Здесь скоро появится панель управления задачами.</p>
            <p class="mb-0">Используйте меню для навигации и добавления нового функционала.</p>
        </div>
    </div>
</main>
<footer class="app-footer py-3 text-center text-white-50">
    © <?= date('Y') ?> Bekpharm Task Manager
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
