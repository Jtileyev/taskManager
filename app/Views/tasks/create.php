<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager — Создание задачи</title>
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
                <h1 class="h3 mb-1">Новая задача</h1>
                <p class="text-muted mb-0">Опишите задачу, назначьте исполнителя и установите сроки.</p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary">Вернуться на дашборд</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="task-form" action="/tasks/create" method="post" enctype="multipart/form-data" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <label for="task-title" class="form-label">Название задачи</label>
                            <input type="text" class="form-control" id="task-title" name="title" value="<?= e($old['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Содержание</label>
                            <div id="task-content-editor" class="editor-container"></div>
                            <input type="hidden" name="content" id="task-content-input" value="<?= e($old['content'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="task-status" class="form-label">Статус</label>
                            <select class="form-select" id="task-status" name="status" required>
                                <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= (($old['status'] ?? 'draft') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="task-priority" class="form-label">Приоритет</label>
                            <select class="form-select" id="task-priority" name="priority" required>
                                <?php foreach ($priorities as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= (($old['priority'] ?? 'planned') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="task-assignee" class="form-label">Исполнитель</label>
                            <select class="form-select" id="task-assignee" name="assignee_id">
                                <option value="">Не назначено</option>
                                <?php foreach ($assignees as $assignee): ?>
                                    <option value="<?= (int) $assignee['id'] ?>" <?= isset($old['assignee_id']) && (int) $old['assignee_id'] === (int) $assignee['id'] ? 'selected' : '' ?>>
                                        <?= e($assignee['full_name']) ?><?= $assignee['role'] === 'Админ' ? ' (Админ)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="task-start" class="form-label">Дата начала</label>
                            <input type="date" class="form-control" id="task-start" name="start_date" value="<?= e($old['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="task-due" class="form-label">Дедлайн</label>
                            <input type="date" class="form-control" id="task-due" name="due_date" value="<?= e($old['due_date'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label for="task-tags" class="form-label">Теги</label>
                            <input type="text" class="form-control" id="task-tags" name="tags" placeholder="Например: маркетинг, запуск" value="<?= e($old['tags'] ?? '') ?>">
                            <div class="form-text">Перечислите теги через запятую для удобного поиска.</div>
                        </div>
                        <div class="col-12">
                            <label for="task-attachments" class="form-label">Прикрепить файлы</label>
                            <input type="file" class="form-control" id="task-attachments" name="attachments[]" multiple>
                            <div class="form-text">Можно прикрепить несколько файлов (до 10 МБ каждый).</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="/dashboard" class="btn btn-outline-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить задачу</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<footer class="app-footer py-3 text-center text-white-50">
    © <?= date('Y') ?> Bekpharm Task Manager
</footer>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.6/dist/editorjs.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.1"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.9.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@1.3.0"></script>
<script>
    (function () {
        const holder = document.getElementById('task-content-editor');
        const hiddenInput = document.getElementById('task-content-input');

        let parsedData = null;
        if (hiddenInput.value) {
            try {
                parsedData = JSON.parse(hiddenInput.value);
            } catch (error) {
                parsedData = null;
            }
        }

        const editor = new EditorJS({
            holder: holder,
            tools: {
                header: Header,
                list: List,
                checklist: Checklist,
            },
            placeholder: 'Опишите задачу, добавьте детали и чек-лист действий…',
            data: parsedData ?? undefined,
        });

        document.getElementById('task-form').addEventListener('submit', function (event) {
            event.preventDefault();

            editor.save().then(function (output) {
                hiddenInput.value = JSON.stringify(output);
                event.target.submit();
            }).catch(function () {
                hiddenInput.value = '';
                event.target.submit();
            });
        });
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
