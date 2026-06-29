<?php
/** @var string $type banner|toast */
$type = $type ?? 'banner';
$publicUrl = rtrim($config['app']['url'] ?? '', '/');
?>

<div id="offline-banner" class="alert alert-warning text-center rounded-0 mb-0 d-none" role="alert">
    <i class="bi bi-wifi-off me-1"></i>
    You're offline, showing cached data.
</div>

<?php if ($type === 'banner'): ?>
<div id="install-banner" class="alert alert-primary alert-dismissible rounded-0 mb-0 d-none" role="alert">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span>
            <i class="bi bi-phone me-1"></i>
            Install <strong>OrderTrack</strong> for quick access from your home screen.
        </span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light" id="install-btn">Install</button>
            <button type="button" class="btn btn-sm btn-outline-light" id="install-dismiss">Not now</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div id="pwa-update-toast" class="position-fixed bottom-0 end-0 p-3 d-none" style="z-index:1080">
    <div class="toast show align-items-center text-bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">A new version is available.</div>
            <button type="button" class="btn btn-sm btn-light me-2 m-auto" id="pwa-reload-btn">Reload</button>
        </div>
    </div>
</div>
