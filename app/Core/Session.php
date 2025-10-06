<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            if (!isset($_SESSION['_flash'][$key])) {
                return null;
            }

            $storedValue = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);

            return $storedValue;
        }

        $_SESSION['_flash'][$key] = $value;

        return $value;
    }

    public static function user(): ?array
    {
        $user = self::get('user');

        return is_array($user) ? $user : null;
    }
}
