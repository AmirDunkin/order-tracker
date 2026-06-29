<?php
/**
 * Role-based sidebar navigation.
 *
 * @var array<string, mixed> $config
 * @var array{id: int, name: string, email: string, role: string}|null $user
 */

$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$role = $user['role'] ?? '';

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = parse_url($config['app']['url'] ?? '', PHP_URL_PATH) ?: '';

if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}

$isActive = static function (string $path) use ($currentPath): bool {
    if ($path === $currentPath) {
        return true;
    }

    return $path !== '/' && str_starts_with($currentPath, rtrim($path, '/') . '/');
};

$customerLinks = [
    ['href' => '/customer/orders',        'icon' => 'bi-bag',          'label' => 'My Orders'],
    ['href' => '/customer/orders/create',  'icon' => 'bi-plus-circle',  'label' => 'New Order'],
];

$shopperLinks = [
    ['href' => '/shopper/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
    ['href' => '/shopper/orders',    'icon' => 'bi-list-ul',      'label' => 'All Orders'],
];

$links = $role === 'shopper' ? $shopperLinks : $customerLinks;
$roleLabel = $role === 'shopper' ? 'Personal Shopper' : 'Customer';
?>

<div class="sidebar-brand px-3 py-4">
    <a href="<?= $baseUrl ?>/" class="sidebar-brand-link text-decoration-none">
        <span class="sidebar-logo">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="32" height="32" rx="8" fill="#4f46e5"/>
                <path d="M9 16.5L14 21.5L23 10.5" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="sidebar-brand-text">
            <span class="sidebar-app-name"><?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></span>
        </span>
    </a>
</div>

<div class="sidebar-role px-3 mb-2">
    <span class="badge sidebar-role-badge"><?= htmlspecialchars($roleLabel) ?></span>
</div>

<nav class="sidebar-nav flex-grow-1 px-2">
    <ul class="nav flex-column gap-1">
        <?php foreach ($links as $link): ?>
            <?php $active = $isActive($link['href']); ?>
            <li class="nav-item">
                <a
                    href="<?= $baseUrl . $link['href'] ?>"
                    class="nav-link sidebar-link <?= $active ? 'active' : '' ?>"
                >
                    <i class="bi <?= $link['icon'] ?> sidebar-link-icon"></i>
                    <span><?= htmlspecialchars($link['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="sidebar-footer px-3 py-3 small text-white-50">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?>
</div>
