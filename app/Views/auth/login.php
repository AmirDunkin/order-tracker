<?php
$authBrandTitle = 'Track orders with confidence';
$authBrandDescription = 'Secure access for customers and personal shoppers.';
?>
<div class="auth-wrapper">
    <?php require __DIR__ . '/../partials/auth_brand_panel.php'; ?>

    <main class="auth-form-panel">
        <div class="auth-card auth-card--compact">
            <div class="auth-card-header">
                <div class="auth-card-brand-mobile" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="#4f46e5"/>
                        <path d="M9 16.5L14 21.5L23 10.5" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2>Sign in</h2>
                <p>Enter your credentials to access your account</p>
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-auth alert-auth--compact mb-3" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= rtrim($config['app']['url'], '/') ?>/login" class="auth-form" novalidate>
                <div class="mb-2">
                    <label for="email" class="form-label">Email address</label>
                    <div class="input-group-auth">
                        <span class="input-group-auth-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="you@company.com"
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group-auth">
                        <span class="input-group-auth-icon" aria-hidden="true"><i class="bi bi-lock"></i></span>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Sign in as</label>
                    <div class="role-selector role-selector--compact">
                        <div class="role-option">
                            <input
                                type="radio"
                                name="role"
                                id="role_customer"
                                value="customer"
                                <?= ($old['role'] ?? 'customer') === 'customer' ? 'checked' : '' ?>
                                required
                            >
                            <label for="role_customer">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                Customer
                            </label>
                        </div>
                        <div class="role-option">
                            <input
                                type="radio"
                                name="role"
                                id="role_shopper"
                                value="shopper"
                                <?= ($old['role'] ?? '') === 'shopper' ? 'checked' : '' ?>
                            >
                            <label for="role_shopper">
                                <i class="bi bi-bag-check" aria-hidden="true"></i>
                                Personal Shopper
                            </label>
                        </div>
                    </div>
                </div>

                <div class="auth-form-actions mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-yp-primary">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                </button>
            </form>

            <p class="auth-card-footer text-center mb-0">
                Don't have an account?
                <a href="<?= rtrim($config['app']['url'], '/') ?>/register" class="auth-link">Create one</a>
            </p>
        </div>
    </main>
</div>
