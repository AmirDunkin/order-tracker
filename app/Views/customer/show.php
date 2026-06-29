<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/customer/orders">My Orders</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($order['order_number']) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars($order['title']) ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($order['order_number']) ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <?php $status = $order['status']; require __DIR__ . '/partials/status_badge.php'; ?>
        <?php if ($order['priority'] === 'urgent'): ?>
            <span class="badge bg-danger">Urgent</span>
        <?php endif; ?>
    </div>
</div>

<?php $status = $order['status']; $audience = 'customer'; require __DIR__ . '/../partials/status_notice.php'; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Order Details</div>
            <div class="card-body">
                <?php if (!empty($order['description'])): ?>
                    <p class="mb-3"><?= nl2br(htmlspecialchars($order['description'])) ?></p>
                <?php endif; ?>

                <h6 class="fw-semibold mb-2">Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-3">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name'] ?? '') ?></td>
                                    <td class="text-end"><?= (int) ($item['qty'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-semibold mb-1">Delivery Address</h6>
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>

                <?php if (!empty($order['notes'])): ?>
                    <h6 class="fw-semibold mb-1">Notes</h6>
                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Status Timeline</div>
            <div class="card-body">
                <p class="small text-muted mb-3">Each update shows how your order is progressing from placement to delivery.</p>
                <?php if (empty($timeline)): ?>
                    <p class="text-muted small mb-0">No status updates yet.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($timeline as $i => $log): ?>
                            <?php
                            $isLast = $i === count($timeline) - 1;
                            $logStatus = $log['new_status'];
                            $badgeClass = \App\Models\Order::statusBadgeClass($logStatus);
                            ?>
                            <div class="timeline-item <?= $isLast ? 'timeline-item-last' : '' ?>">
                                <div class="timeline-marker bg-<?= htmlspecialchars($badgeClass) ?>"></div>
                                <div class="timeline-content pb-4">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <?php $status = $logStatus; require __DIR__ . '/partials/status_badge.php'; ?>
                                        <span class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($log['old_status'])): ?>
                                        <p class="small text-muted mb-1">
                                            <?= htmlspecialchars(ucfirst($log['old_status'])) ?>
                                            &rarr;
                                            <?= htmlspecialchars(ucfirst($log['new_status'])) ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($log['note'])): ?>
                                        <p class="small mb-1"><?= htmlspecialchars($log['note']) ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted mb-0">by <?= htmlspecialchars($log['changed_by_name']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body small text-muted">
                <div class="d-flex justify-content-between mb-1">
                    <span>Created</span>
                    <span><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Last updated</span>
                    <span><?= date('d M Y, H:i', strtotime($order['updated_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= $baseUrl ?>/customer/orders" class="btn btn-outline-secondary">&larr; Back to orders</a>
</div>
