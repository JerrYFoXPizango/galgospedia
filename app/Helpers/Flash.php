<?php
declare(strict_types=1);
namespace Helpers;

class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    public static function get(string $type): ?string
    {
        $msg = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $msg;
    }

    public static function all(): array
    {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }

    public static function has(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }

    /** Render all flash messages as HTML */
    public static function render(): string
    {
        $messages = self::all();
        if (!$messages) return '';

        $html = '';
        $classes = [
            'success' => 'bg-green-50 border-green-400 text-green-800',
            'error'   => 'bg-red-50 border-red-400 text-red-800',
            'info'    => 'bg-blue-50 border-blue-400 text-blue-800',
            'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
        ];

        foreach ($messages as $type => $msg) {
            $cls = $classes[$type] ?? 'bg-gray-50 border-gray-400 text-gray-800';
            $html .= '<div class="border-l-4 p-4 mb-4 rounded ' . $cls . '" role="alert">'
                   . htmlspecialchars($msg)
                   . '</div>';
        }
        return $html;
    }
}
