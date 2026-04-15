<?php
declare(strict_types=1);
namespace Helpers;

class Paginator
{
    public int $total;
    public int $page;
    public int $perPage;
    public int $totalPages;

    public function __construct(int $total, int $page, int $perPage)
    {
        $this->total      = $total;
        $this->page       = max(1, $page);
        $this->perPage    = $perPage;
        $this->totalPages = max(1, (int) ceil($total / $perPage));
    }

    public function hasPrev(): bool { return $this->page > 1; }
    public function hasNext(): bool { return $this->page < $this->totalPages; }
    public function prevPage(): int { return $this->page - 1; }
    public function nextPage(): int { return $this->page + 1; }

    /** Render simple prev/next links */
    public function render(string $baseUrl): string
    {
        if ($this->totalPages <= 1) return '';

        $html  = '<nav class="flex items-center justify-between mt-8">';
        $html .= '<span class="text-sm text-gray-600">'
               . "Página {$this->page} de {$this->totalPages} — {$this->total} resultados"
               . '</span>';
        $html .= '<div class="flex gap-2">';

        if ($this->hasPrev()) {
            $url   = $baseUrl . '?page=' . $this->prevPage();
            $html .= '<a href="' . $url . '" class="btn-secondary">← Anterior</a>';
        }
        if ($this->hasNext()) {
            $url   = $baseUrl . '?page=' . $this->nextPage();
            $html .= '<a href="' . $url . '" class="btn-secondary">Siguiente →</a>';
        }

        $html .= '</div></nav>';
        return $html;
    }
}
