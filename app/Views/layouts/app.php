<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$userName = $user['name'] ?? 'User';
$nameParts = preg_split('/\s+/', trim($userName)) ?: [];
$initials = '';

foreach (array_slice($nameParts, 0, 2) as $part) {
    $initials .= strtoupper($part[0] ?? '');
}

$initials = $initials !== '' ? $initials : 'U';
$version = $config['app']['version'] ?? '1.0.0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="app-base" content="<?= $baseUrl ?>">
    <?php require __DIR__ . '/../partials/pwa_head.php'; ?>
    <title><?= htmlspecialchars($title ?? 'OrderTrack') ?> — <?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/layout.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/app.css">
</head>
<body class="app-body">
    <?php require __DIR__ . '/../partials/pwa_ui.php'; ?>

    <!-- Mobile offcanvas sidebar -->
    <div class="offcanvas offcanvas-start app-sidebar-offcanvas" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <h5 class="offcanvas-title text-white" id="sidebarOffcanvasLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <?php require __DIR__ . '/../partials/sidebar_nav.php'; ?>
        </div>
    </div>

  <div class="app-shell">
        <!-- Desktop sidebar -->
        <aside class="app-sidebar d-none d-lg-flex flex-column">
            <?php require __DIR__ . '/../partials/sidebar_nav.php'; ?>
        </aside>

        <div class="app-content-wrapper">
            <!-- Top navbar -->
            <header class="app-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button
                        class="btn btn-link text-dark d-lg-none p-0 app-menu-btn"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarOffcanvas"
                        aria-controls="sidebarOffcanvas"
                    >
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <a href="<?= $baseUrl ?>/" class="app-topbar-brand d-lg-none text-decoration-none">
                        <svg width="24" height="24" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <rect width="32" height="32" rx="8" fill="#4f46e5"/>
                            <path d="M9 16.5L14 21.5L23 10.5" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="fw-semibold text-dark ms-2"><?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></span>
                    </a>
                    <div class="d-none d-lg-block">
                        <h1 class="app-page-title h5 mb-0"><?= htmlspecialchars($title ?? '') ?></h1>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <!-- Notification bell (placeholder) -->
                    <div class="dropdown">
                        <button
                            class="btn btn-light btn-icon position-relative"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Notifications"
                        >
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li class="dropdown-header">Notifications</li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-4 text-muted small text-center">No new notifications</li>
                        </ul>
                    </div>

                    <!-- User dropdown -->
                    <div class="dropdown">
                        <button
                            class="btn btn-light d-flex align-items-center gap-2 py-1 px-2"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <span class="user-avatar"><?= htmlspecialchars($initials) ?></span>
                            <span class="d-none d-md-inline small fw-medium text-dark"><?= htmlspecialchars($userName) ?></span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li class="dropdown-header">
                                <div class="fw-semibold"><?= htmlspecialchars($userName) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main content -->
            <main class="app-main">
                <div class="d-lg-none mb-3">
                    <h1 class="app-page-title h5 mb-0"><?= htmlspecialchars($title ?? '') ?></h1>
                </div>

                <?php if (!empty($flash)): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>

            <footer class="app-footer">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small text-muted">
                    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></span>
                    <span>OrderTrack v<?= htmlspecialchars($version) ?></span>
                </div>
            </footer>
        </div>
    </div>

    <!-- AJAX toast container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="ajax-toast-container" style="z-index:1090"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>/js/app.js"></script>
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
