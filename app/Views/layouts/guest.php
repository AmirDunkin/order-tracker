<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$version = $config['app']['version'] ?? '1.0.0';
$guestLayout = $guestLayout ?? 'centered';
$isSplitLayout = $guestLayout === 'split';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="app-base" content="<?= $baseUrl ?>">
    <?php require __DIR__ . '/../partials/pwa_head.php'; ?>
    <title><?= htmlspecialchars($title ?? 'Sign In') ?> — <?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/guest.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/auth.css">
</head>
<body class="guest-body<?= $isSplitLayout ? ' guest-body--split' : '' ?>">
    <?php $type = 'toast'; require __DIR__ . '/../partials/pwa_ui.php'; ?>

    <?php if ($isSplitLayout): ?>
        <?= $content ?? '' ?>
    <?php else: ?>
        <div class="guest-pattern" aria-hidden="true"></div>

        <div class="guest-wrapper">
            <header class="guest-header text-center mb-4">
                <a href="<?= $baseUrl ?>/login" class="guest-brand text-decoration-none">
                    <span class="guest-logo">
                        <svg width="40" height="40" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <rect width="32" height="32" rx="8" fill="#4f46e5"/>
                            <path d="M9 16.5L14 21.5L23 10.5" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="guest-brand-name"><?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?></span>
                </a>
            </header>

            <main class="guest-main">
                <?= $content ?? '' ?>
            </main>

            <footer class="guest-footer text-center">
                <p class="small text-muted mb-0">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($config['app']['name'] ?? 'OrderTrack') ?>
                    &middot; v<?= htmlspecialchars($version) ?>
                </p>
            </footer>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>/js/app.js"></script>
</body>
</html>
