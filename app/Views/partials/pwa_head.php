<?php

declare(strict_types=1);

/** @var array<string, mixed> $config */
$publicUrl = rtrim($config['app']['url'] ?? '', '/');
$pwaName   = 'OrderTrack';
$pwaShort  = 'OTrack';
$themeColor = '#1a1f36';
?>
<meta name="application-name" content="<?= $pwaShort ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="<?= $themeColor ?>">
<meta name="msapplication-TileColor" content="<?= $themeColor ?>">
<meta name="msapplication-tap-highlight" content="no">
<meta name="format-detection" content="telephone=no">

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="<?= $pwaShort ?>">

<link rel="manifest" href="<?= $publicUrl ?>/manifest.json">
<link rel="icon" type="image/png" sizes="192x192" href="<?= $publicUrl ?>/icons/icon-192.png">
<link rel="icon" type="image/png" sizes="512x512" href="<?= $publicUrl ?>/icons/icon-512.png">
<link rel="apple-touch-icon" href="<?= $publicUrl ?>/icons/icon-192.png">
