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

if (!function_exists('render_editor_content')) {
    function render_editor_content(?string $payload): string
    {
        if ($payload === null || $payload === '') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if (!is_array($decoded) || !isset($decoded['blocks']) || !is_array($decoded['blocks'])) {
            return '';
        }

        $html = '';

        foreach ($decoded['blocks'] as $block) {
            $type = $block['type'] ?? 'paragraph';
            $data = $block['data'] ?? [];

            switch ($type) {
                case 'header':
                    $level = (int) ($data['level'] ?? 2);
                    $level = max(1, min($level, 6));
                    $text = htmlspecialchars((string) ($data['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html .= sprintf('<h%d class="editor-block mb-2">%s</h%d>', $level, $text, $level);
                    break;
                case 'list':
                    $style = $data['style'] ?? 'unordered';
                    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                    $tag = $style === 'ordered' ? 'ol' : 'ul';
                    $html .= '<' . $tag . ' class="editor-block ms-3">';
                    foreach ($items as $item) {
                        $content = htmlspecialchars(is_array($item) ? (string) ($item['content'] ?? '') : (string) $item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $html .= '<li>' . $content . '</li>';
                    }
                    $html .= '</' . $tag . '>';
                    break;
                case 'quote':
                    $text = htmlspecialchars((string) ($data['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $caption = htmlspecialchars((string) ($data['caption'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html .= '<blockquote class="editor-block">' . $text;
                    if ($caption !== '') {
                        $html .= '<footer class="blockquote-footer mt-1">' . $caption . '</footer>';
                    }
                    $html .= '</blockquote>';
                    break;
                case 'checklist':
                    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                    $html .= '<ul class="editor-block checklist list-unstyled">';
                    foreach ($items as $item) {
                        $text = htmlspecialchars((string) ($item['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $checked = !empty($item['checked']) ? 'checked' : '';
                        $html .= '<li><input type="checkbox" disabled ' . $checked . '> <span>' . $text . '</span></li>';
                    }
                    $html .= '</ul>';
                    break;
                default:
                    $text = htmlspecialchars((string) ($data['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    if ($text !== '') {
                        $html .= '<p class="editor-block">' . $text . '</p>';
                    }
            }
        }

        return $html;
    }
}

if (!function_exists('editor_extract_text')) {
    function editor_extract_text(?string $payload, int $limit = 160): string
    {
        if ($payload === null || $payload === '') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if (!is_array($decoded) || !isset($decoded['blocks']) || !is_array($decoded['blocks'])) {
            return '';
        }

        $chunks = [];

        foreach ($decoded['blocks'] as $block) {
            $data = $block['data'] ?? [];
            $text = '';

            if (isset($data['text'])) {
                $text = strip_tags((string) $data['text']);
            } elseif (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $chunks[] = strip_tags(is_array($item) ? (string) ($item['content'] ?? '') : (string) $item);
                }
                continue;
            }

            if ($text !== '') {
                $chunks[] = $text;
            }
        }

        $text = trim(preg_replace('/\s+/', ' ', implode(' ', $chunks)));

        if ($limit > 0 && mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit - 1) . '…';
        }

        return $text;
    }
}

if (!function_exists('format_filesize')) {
    function format_filesize(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $index = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return number_format($size, $index === 0 ? 0 : 1, '.', ' ') . ' ' . $units[$index];
    }
}
