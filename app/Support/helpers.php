<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!function_exists('is_mobile_user_agent')) {
    function is_mobile_user_agent(): bool
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $mobileKeywords = [
            'android',
            'iphone',
            'ipad',
            'ipod',
            'blackberry',
            'windows phone',
            'opera mini',
            'mobile',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
