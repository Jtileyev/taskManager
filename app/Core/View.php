<?php

namespace App\Core;

use RuntimeException;

class View
{
    public static function render(string $template, array $data = []): void
    {
        $path = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('View "%s" not found.', $template));
        }

        extract($data, EXTR_SKIP);

        require $path;
    }
}
