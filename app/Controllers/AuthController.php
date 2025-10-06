<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use PDOException;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function show(): void
    {
        if (Session::user()) {
            $this->redirect('/dashboard');
        }

        if (is_mobile_user_agent()) {
            $this->view('auth/mobile');
            return;
        }

        $flash = Session::flash('auth');
        $requestedTab = $_GET['tab'] ?? null;
        $activeTab = $requestedTab === 'register' ? 'register' : 'login';

        if (is_array($flash) && isset($flash['tab'])) {
            $activeTab = $flash['tab'];
        }

        $this->view('auth/index', [
            'flash' => $flash,
            'activeTab' => $activeTab,
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || $password === '') {
            Session::flash('auth', [
                'type' => 'danger',
                'message' => 'Укажите корректную почту и пароль.',
                'tab' => 'login',
            ]);
            $this->redirect('/');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::flash('auth', [
                'type' => 'danger',
                'message' => 'Неверная почта или пароль.',
                'tab' => 'login',
            ]);
            $this->redirect('/');
        }

        Session::set('user', [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
        ]);

        Session::flash('success', 'Вы успешно авторизовались!');

        $this->redirect('/dashboard');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?tab=register');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
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

        if (strlen($password) < 6) {
            $errors[] = 'Пароль должен содержать не менее 6 символов.';
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = 'Пароли не совпадают.';
        }

        if ($errors) {
            Session::flash('auth', [
                'type' => 'danger',
                'message' => implode(' ', $errors),
                'tab' => 'register',
            ]);
            $this->redirect('/?tab=register');
        }

        try {
            $this->users->create([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'Пользователь',
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $message = 'Пользователь с такой почтой уже существует.';
            } else {
                $message = 'Ошибка при регистрации. Попробуйте позже.';
            }

            Session::flash('auth', [
                'type' => 'warning',
                'message' => $message,
                'tab' => 'register',
            ]);
            $this->redirect('/?tab=register');
        }

        Session::flash('auth', [
            'type' => 'success',
            'message' => 'Регистрация прошла успешно! Теперь вы можете авторизоваться.',
            'tab' => 'login',
        ]);

        $this->redirect('/');
    }

    public function logout(): void
    {
        Session::forget('user');
        Session::flash('auth', [
            'type' => 'success',
            'message' => 'Вы вышли из аккаунта.',
            'tab' => 'login',
        ]);

        $this->redirect('/');
    }
}
