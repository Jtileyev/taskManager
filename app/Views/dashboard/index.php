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
        <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Ваши задачи</h1>
                <p class="text-muted mb-0">Следите за назначенными и созданными задачами в одном месте.</p>
            </div>
            <div class="text-lg-end">
                <a href="/tasks/create" class="btn btn-primary shadow-sm">
                    Создать задачу
                </a>
            </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success" role="alert">
                <?= e($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <form method="get" action="/dashboard" class="card card-body shadow-sm mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="filter-status" class="form-label text-muted">Статус</label>
                    <select class="form-select" id="filter-status" name="status">
                        <option value="">Все статусы</option>
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-priority" class="form-label text-muted">Приоритет</label>
                    <select class="form-select" id="filter-priority" name="priority">
                        <option value="">Все приоритеты</option>
                        <?php foreach ($priorities as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filters['priority'] === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-scope" class="form-label text-muted">Отображение</label>
                    <select class="form-select" id="filter-scope" name="scope">
                        <option value="all" <?= $filters['scope'] === 'all' ? 'selected' : '' ?>>Все задачи</option>
                        <option value="assigned" <?= $filters['scope'] === 'assigned' ? 'selected' : '' ?>>Назначенные на меня</option>
                        <option value="authored" <?= $filters['scope'] === 'authored' ? 'selected' : '' ?>>Созданные мной</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-outline-primary">Применить</button>
                    <a href="/dashboard" class="btn btn-outline-secondary">Сбросить</a>
                </div>
            </div>
        </form>

        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <div class="card card-body shadow-sm text-center text-muted py-5">
                    <p class="mb-0">У вас сейчас нет задач по выбранным фильтрам.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($tasks as $task): ?>
                        <div class="col-12">
                            <div class="card task-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column gap-3">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <a class="link-dark text-decoration-none" href="/tasks/view?id=<?= (int) $task['id'] ?>">
                                                    <?= e($task['title']) ?>
                                                </a>
                                            </h5>
                                            <div class="text-muted small">
                                                Автор: <?= e($task['author_name']) ?>
                                                <?php if (!empty($task['assignee_name'])): ?>
                                                    · Исполнитель: <?= e($task['assignee_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-md-end">
                                            <span class="badge bg-status"><?= e($task['status_label']) ?></span>
                                            <span class="badge bg-priority ms-1"><?= e($task['priority_label']) ?></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($task['content_preview'])): ?>
                                        <p class="text-muted mb-0"><?= e($task['content_preview']) ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
                                        <?php if (!empty($task['tags_list'])): ?>
                                            <?php foreach ($task['tags_list'] as $tag): ?>
                                                <span class="badge rounded-pill bg-light text-body-secondary border">#<?= e($tag) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 small text-muted">
                                        <?php if (!empty($task['start_date'])): ?>
                                            <span>Начало: <?= e(date('d.m.Y', strtotime($task['start_date']))) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($task['due_date'])): ?>
                                            <span>Дедлайн: <?= e(date('d.m.Y', strtotime($task['due_date']))) ?></span>
                                        <?php endif; ?>
                                        <span>Обновлено: <?= e(date('d.m.Y H:i', strtotime($task['updated_at']))) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<footer class="app-footer py-3 text-center text-white-50">
    © <?= date('Y') ?> Bekpharm Task Manager
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
