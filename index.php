<?php
require __DIR__ . '/functions.php';

if (!empty($_SESSION['user'])) {
    header('Location: /main.php');
    exit;
}

if (is_mobile_user_agent()) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Доступно только с компьютера</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                min-height: 100vh;
                background: linear-gradient(135deg, #2B4070, #6FC9EC);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            .message-card {
                background-color: rgba(255, 255, 255, 0.15);
                border-radius: 1rem;
                padding: 2rem;
                backdrop-filter: blur(8px);
                max-width: 420px;
            }
        </style>
    </head>
    <body>
        <div class="message-card">
            <h1 class="h3 mb-3">Доступно только с компьютера</h1>
            <p class="mb-0">Для работы с системой управления задачами используйте настольный браузер.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$shouldOpenRegister = $flash && $flash['type'] !== 'success';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager — Авторизация и регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="auth-wrapper">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-center text-white">
                        <h1 class="h4 mb-0">Task Manager</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= sanitize($flash['type']) ?>">
                                <?= sanitize($flash['message']) ?>
                            </div>
                        <?php endif; ?>
                        <ul class="nav nav-pills nav-fill mb-4" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Авторизация</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Регистрация</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form method="post" action="/login.php" novalidate>
                                    <div class="mb-3">
                                        <label for="loginEmail" class="form-label">Почта</label>
                                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="loginPassword" class="form-label">Пароль</label>
                                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Войти</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form method="post" action="/register.php" novalidate>
                                    <div class="mb-3">
                                        <label for="fullName" class="form-label">ФИО</label>
                                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="registerEmail" class="form-label">Почта</label>
                                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Номер телефона</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Роль</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="Пользователь">Пользователь</option>
                                            <option value="Админ">Админ</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="registerPassword" class="form-label">Пароль</label>
                                        <input type="password" class="form-control" id="registerPassword" name="password" required minlength="6">
                                    </div>
                                    <div class="mb-4">
                                        <label for="registerPasswordConfirm" class="form-label">Повторите пароль</label>
                                        <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirmation" required minlength="6">
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-outline-primary">Создать аккаунт</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center small text-muted">
                        © <?= date('Y') ?> Bekpharm Task Manager
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const triggerTab = (hash) => {
        if (!hash) return;
        const tabTrigger = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    };
    window.addEventListener('DOMContentLoaded', () => {
        const defaultTab = window.location.hash || (<?= $shouldOpenRegister ? "'#register'" : null ?>);
        triggerTab(defaultTab);
    });
    window.addEventListener('hashchange', () => triggerTab(window.location.hash));
</script>
</body>
</html>
