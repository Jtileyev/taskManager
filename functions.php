<?php
session_start();

function is_mobile_user_agent(): bool
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }

    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $mobileKeywords = [
        'android', 'iphone', 'ipad', 'ipod', 'blackberry', 'windows phone', 'opera mini', 'mobile'
    ];

    foreach ($mobileKeywords as $keyword) {
        if (strpos($userAgent, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

function ensure_logged_in(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: /index.php');
        exit;
    }
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
