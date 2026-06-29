<?php

use App\Models\Order;

/** @var string $audience customer|shopper */
$audience = $audience ?? 'customer';
$legendId = 'status-legend-' . $audience;
?>
<details class="status-legend card border-0 shadow-sm mb-4">
    <summary class="status-legend-summary card-body py-3">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        What do order statuses mean?
    </summary>
    <div class="status-legend-body card-body pt-0">
        <div class="status-legend-grid">
            <?php foreach (Order::allStatuses() as $statusKey): ?>
                <?php
                $meta = Order::statusMeta($statusKey);
                if ($meta === null) {
                    continue;
                }
                $message = $audience === 'shopper'
                    ? Order::statusShopperMessage($statusKey)
                    : Order::statusCustomerMessage($statusKey);
                ?>
                <div class="status-legend-item">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <?php $status = $statusKey; require __DIR__ . '/../customer/partials/status_badge.php'; ?>
                    </div>
                    <p class="small text-muted mb-0"><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</details>
