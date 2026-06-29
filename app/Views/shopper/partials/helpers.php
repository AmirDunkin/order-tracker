<?php
/**
 * Build query string preserving current filters.
 *
 * @param array<string, mixed> $filters
 * @param array<string, mixed> $overrides
 */
function shopper_query(array $filters, array $overrides = []): string
{
    $params = array_merge($filters, $overrides);
    $params = array_filter($params, static fn ($v) => $v !== '' && $v !== null);

    return $params === [] ? '' : '?' . http_build_query($params);
}

/**
 * @param array<string, mixed> $filters
 */
function shopper_sort_link(string $column, string $label, array $filters): string
{
    $currentSort = $filters['sort'] ?? 'created_at';
    $currentDir  = strtolower($filters['dir'] ?? 'desc');
    $nextDir     = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $icon        = '';

    if ($currentSort === $column) {
        $icon = $currentDir === 'asc'
            ? ' <i class="bi bi-caret-up-fill small"></i>'
            : ' <i class="bi bi-caret-down-fill small"></i>';
    }

    $href = shopper_query($filters, ['sort' => $column, 'dir' => $nextDir, 'page' => 1]);

    return '<a href="' . htmlspecialchars($href) . '" class="text-decoration-none text-dark sort-link">'
        . htmlspecialchars($label) . $icon . '</a>';
}
