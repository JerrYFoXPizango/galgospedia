<?php

declare(strict_types=1);

namespace Helpers;

class DogStatus
{
    private const MAX_AGE_YEARS = 14;

    /**
     * Returns 'alive' | 'deceased' | 'presumed'
     */
    public static function of(array $dog): string
    {
        if (!empty($dog['date_of_death'])) {
            return 'deceased';
        }
        if (!empty($dog['date_of_birth'])) {
            try {
                $birth = new \DateTime($dog['date_of_birth']);
                $age   = (new \DateTime())->diff($birth)->y;
                if ($age > self::MAX_AGE_YEARS) {
                    return 'presumed';
                }
            } catch (\Throwable) {}
        }
        return 'alive';
    }

    /**
     * Returns the HTML badge for the dog's status.
     * Alive+active (semental/reproductora): green pulse dot.
     * Alive without role: no badge (keep it clean).
     * Deceased/presumed: cross icon + label.
     */
    public static function badge(array $dog, bool $isActive = false): string
    {
        $status = self::of($dog);

        if ($status === 'deceased') {
            $date = !empty($dog['date_of_death'])
                ? ' · ' . date('d/m/Y', strtotime($dog['date_of_death']))
                : '';
            return '<span class="dog-status-deceased">'
                 . '<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">'
                 . '<path d="M12 2a7 7 0 0 1 7 7c0 2.56-1.38 4.8-3.43 6.03L15 17H9l-.57-1.97A7.003 7.003 0 0 1 5 9a7 7 0 0 1 7-7zm-1 15h2v2h-2v-2zm0 3h2v2h-2v-2z"/>'
                 . '</svg>'
                 . 'Fallecido' . htmlspecialchars($date)
                 . '</span>';
        }

        if ($status === 'presumed') {
            return '<span class="dog-status-presumed">'
                 . '<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">'
                 . '<path d="M12 2a7 7 0 0 1 7 7c0 2.56-1.38 4.8-3.43 6.03L15 17H9l-.57-1.97A7.003 7.003 0 0 1 5 9a7 7 0 0 1 7-7zm-1 15h2v2h-2v-2zm0 3h2v2h-2v-2z"/>'
                 . '</svg>'
                 . 'Fallecido (est.)'
                 . '</span>';
        }

        // alive — only show pulse dot if actively registered as semental/reproductora
        if ($isActive) {
            return '<span class="dog-status-alive">'
                 . '<span class="pulse-dot"></span>'
                 . 'Activo'
                 . '</span>';
        }

        return '';
    }
}
