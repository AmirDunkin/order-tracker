<?php
/**
 * Shared branding panel for login / register.
 *
 * @var array<string, mixed> $config
 * @var string $authBrandTitle
 * @var string $authBrandDescription
 */
$appName = htmlspecialchars($config['app']['name'] ?? 'OrderTrack');
?>
<aside class="auth-brand" aria-label="Application branding">
    <div class="auth-brand-content">
        <div class="auth-brand-logo">
            <svg width="36" height="36" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <rect width="32" height="32" rx="8" fill="rgba(255,255,255,0.12)"/>
                <path d="M9 16.5L14 21.5L23 10.5" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span><?= $appName ?></span>
        </div>

        <h1><?= htmlspecialchars($authBrandTitle ?? 'Track orders with confidence') ?></h1>
        <p><?= htmlspecialchars($authBrandDescription ?? 'Secure access for customers and personal shoppers.') ?></p>

        <ul class="auth-features">
            <li>
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                Role-based secure access
            </li>
            <li>
                <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                Real-time order visibility
            </li>
            <li>
                <i class="bi bi-building" aria-hidden="true"></i>
                Built for professional operations
            </li>
        </ul>
    </div>

    <p class="auth-brand-footer">
        &copy; <?= date('Y') ?> <?= $appName ?>
    </p>
</aside>
