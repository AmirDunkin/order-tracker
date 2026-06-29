<?php

use App\Models\Order;

/** @var string $status */
/** @var string $audience customer|shopper */
$audience = $audience ?? 'customer';
$meta = Order::statusMeta($status);

if ($meta === null) {
    return;
}

$message = $audience === 'shopper'
    ? Order::statusShopperMessage($status)
    : Order::statusCustomerMessage($status);
?>
<div
    class="status-notice alert alert-<?= htmlspecialchars(Order::statusAlertClass($status)) ?> d-flex align-items-start gap-2 mb-4"
    id="status-notice"
    role="status"
    data-status="<?= htmlspecialchars($status) ?>"
    data-audience="<?= htmlspecialchars($audience) ?>"
>
    <i class="bi <?= htmlspecialchars(Order::statusIcon($status)) ?> flex-shrink-0 mt-1" aria-hidden="true"></i>
    <div>
        <strong class="d-block mb-1"><?= htmlspecialchars(Order::statusLabel($status)) ?></strong>
        <span class="status-notice-text mb-0"><?= htmlspecialchars($message) ?></span>
    </div>
</div>
