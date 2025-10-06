<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager — Авторизация и регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/styles.css" rel="stylesheet">
</head>
<body class="bg-light" data-active-tab="<?= $activeTab === 'register' ? 'register' : 'login' ?>">
<div class="auth-wrapper">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-center text-white">
                        <h1 class="h4 mb-0">Task Manager</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($flash)): ?>
                            <div class="alert alert-<?= e($flash['type']) ?>">
                                <?= e($flash['message']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- УБРАНО: верхнее меню вкладок -->

                        <div class="tab-content">
                            <!-- LOGIN -->
                            <div class="tab-pane fade <?= $activeTab === 'login' ? 'show active' : '' ?>" id="login" role="tabpanel">
                                <form method="post" action="/login" novalidate>
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

                                <!-- Переключатель на РЕГИСТРАЦИЮ -->
                                <p class="text-center text-muted mt-4 mb-0">
                                    У вас нету учетной записи?
                                    <button class="btn btn-link p-0 align-baseline" id="to-register" type="button">
                                        Создать учетную запись
                                    </button>
                                </p>
                            </div>

                            <!-- REGISTER -->
                            <div class="tab-pane fade <?= $activeTab === 'register' ? 'show active' : '' ?>" id="register" role="tabpanel">
                                <form method="post" action="/register" novalidate>
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

                                <!-- Переключатель на ЛОГИН -->
                                <p class="text-center text-muted mt-4 mb-0">
                                    У вас есть учетная запись?
                                    <button class="btn btn-link p-0 align-baseline" id="to-login" type="button">
                                        Войти
                                    </button>
                                </p>
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

<!-- Bootstrap остаётся (вдруг используешь Toast/Modal и т.п.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tabs = ['login', 'register'];

    function switchTab(id) {
      if (!tabs.includes(id)) id = 'login';

      document.querySelectorAll('.tab-pane').forEach(pane => {
        const active = pane.id === id;
        pane.classList.toggle('show', active);
        pane.classList.toggle('active', active);
      });

      document.body.dataset.activeTab = id;

      // Автофокус на первое поле формы
      const autofocusSelector = id === 'login' ? '#loginEmail' : '#fullName';
      const el = document.querySelector(autofocusSelector);
      if (el) {
        // Небольшая задержка, чтобы класс .show применился
        setTimeout(() => el.focus(), 0);
      }
    }

    // Инициализация из PHP: <body data-active-tab="...">
    switchTab(document.body.dataset.activeTab || 'login');

    // Клики по нижним ссылкам
    document.getElementById('to-register')?.addEventListener('click', () => switchTab('register'));
    document.getElementById('to-login')?.addEventListener('click', () => switchTab('login'));
  });
</script>
</body>
</html>
