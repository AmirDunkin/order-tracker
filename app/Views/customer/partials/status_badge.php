<?php

use App\Models\Order;

/** @var string $status */
$badgeClass = Order::statusBadgeClass($status);
$label = Order::statusLabel($status);
$tooltip = Order::statusTooltip($status);
?>
<span
    class="badge bg-<?= htmlspecialchars($badgeClass) ?> status-badge"
    title="<?= htmlspecialchars($tooltip) ?>"
><?= htmlspecialchars($label) ?></span>
