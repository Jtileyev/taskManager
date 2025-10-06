<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager — <?= e($task['title']) ?></title>
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
                <h1 class="h3 mb-1"><?= e($task['title']) ?></h1>
                <p class="text-muted mb-0">Статус: <?= e($task['status_label']) ?> · Приоритет: <?= e($task['priority_label']) ?></p>
            </div>
            <a href="/dashboard" class="btn btn-outline-secondary">Назад к задачам</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                            <span>Автор: <?= e($task['author_name']) ?></span>
                            <?php if (!empty($task['assignee_name'])): ?>
                                <span>Исполнитель: <?= e($task['assignee_name']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($task['start_date'])): ?>
                                <span>Старт: <?= e(date('d.m.Y', strtotime($task['start_date']))) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($task['due_date'])): ?>
                                <span>Дедлайн: <?= e(date('d.m.Y', strtotime($task['due_date']))) ?></span>
                            <?php endif; ?>
                            <span>Создано: <?= e(date('d.m.Y H:i', strtotime($task['created_at']))) ?></span>
                            <span>Обновлено: <?= e(date('d.m.Y H:i', strtotime($task['updated_at']))) ?></span>
                        </div>
                        <?php if (!empty($task['tags_list'])): ?>
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                <?php foreach ($task['tags_list'] as $tag): ?>
                                    <span class="badge rounded-pill bg-light text-body-secondary border">#<?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="editor-output">
                            <?= $task['content_html'] ?>
                        </div>
                    </div>
                </div>

                <div id="comments" class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h5 mb-0">Комментарии</h2>
                        </div>

                        <?php if (!empty($commentSuccess)): ?>
                            <div class="alert alert-success" role="alert">
                                <?= e($commentSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($commentErrors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($commentErrors as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($comments)): ?>
                            <p class="text-muted">Комментариев пока нет — будьте первым!</p>
                        <?php else: ?>
                            <div class="vstack gap-4 mb-4">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong><?= e($comment['author_name']) ?></strong>
                                                <div class="small text-muted"><?= e(date('d.m.Y H:i', strtotime($comment['created_at']))) ?></div>
                                            </div>
                                        </div>
                                        <div class="editor-output small mb-2">
                                            <?= $comment['content_html'] ?>
                                        </div>
                                        <?php if (!empty($comment['attachments'])): ?>
                                            <div class="comment-attachments small">
                                                <span class="text-muted d-block mb-1">Файлы:</span>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($comment['attachments'] as $attachment): ?>
                                                        <li>
                                                            <a href="/uploads/comments/<?= e($attachment['stored_name']) ?>" class="link-primary" download>
                                                                <?= e($attachment['original_name']) ?> (<?= e(format_filesize((int) $attachment['file_size'])) ?>)
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form id="comment-form" action="/tasks/comment" method="post" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Ваш комментарий</label>
                                <div id="comment-editor" class="editor-container"></div>
                                <input type="hidden" name="comment_content" id="comment-content-input" value="<?= e($commentOld ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="comment-attachments" class="form-label">Прикрепить файлы</label>
                                <input type="file" class="form-control" id="comment-attachments" name="comment_attachments[]" multiple>
                                <div class="form-text">Поддерживается множественная загрузка файлов до 10 МБ.</div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Добавить комментарий</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Файлы</h2>
                        <?php if (empty($attachments)): ?>
                            <p class="text-muted mb-0">Автор не прикрепил файлы к задаче.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($attachments as $attachment): ?>
                                    <li class="mb-2">
                                        <a href="/uploads/tasks/<?= e($attachment['stored_name']) ?>" class="link-primary" download>
                                            <?= e($attachment['original_name']) ?>
                                        </a>
                                        <div class="small text-muted">Размер: <?= e(format_filesize((int) $attachment['file_size'])) ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
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
        const hiddenInput = document.getElementById('comment-content-input');
        const holder = document.getElementById('comment-editor');

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
            placeholder: 'Напишите обновление, уточнение или вопрос…',
            data: parsedData ?? undefined,
        });

        document.getElementById('comment-form').addEventListener('submit', function (event) {
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
