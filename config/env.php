<?php

declare(strict_types=1);

/**
 * Detect application environment.
 * Override with APP_ENV=local|production (SetEnv, .user.ini, or server config).
 */
function app_environment(): string
{
    $env = getenv('APP_ENV');

    if (is_string($env) && $env !== '') {
        return $env;
    }

    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    if (
        $host === 'localhost'
        || str_starts_with($host, 'localhost:')
        || $host === '127.0.0.1'
        || str_starts_with($host, '127.0.0.1:')
        || str_ends_with($host, '.local')
        || str_ends_with($host, '.test')
    ) {
        return 'local';
    }

    return 'production';
}

/**
 * Resolve public base URL (no trailing slash).
 * Override with APP_URL=https://order-tracker.yourdomain.com
 */
function app_base_url(): string
{
    $configured = getenv('APP_URL');

    if (is_string($configured) && $configured !== '') {
        return rtrim($configured, '/');
    }

    if (app_environment() === 'local') {
        // Auto-detect when running under IIS or Apache (avoids wrong hardcoded path)
        if (PHP_SAPI !== 'cli' && isset($_SERVER['SCRIPT_NAME'])) {
            $detected = app_detect_base_url();

            if ($detected !== '') {
                return $detected;
            }
        }

        return 'http://localhost:8080/order-tracker/public';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on');

    $scheme = $https ? 'https' : 'http';
    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $base   = rtrim(dirname($script), '/');

    if ($base === '/' || $base === '.') {
        $base = '';
    }

    return $scheme . '://' . $host . $base;
}

/**
 * Build base URL from current request (local IIS/Apache).
 */
function app_detect_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $scheme = $https ? 'https' : 'http';
    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($script === '') {
        return '';
    }

    $base = rtrim(dirname($script), '/');

    if ($base === '/' || $base === '.') {
        $base = '';
    }

    return $scheme . '://' . $host . $base;
}
