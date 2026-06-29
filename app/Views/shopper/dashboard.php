<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$badgePartial = __DIR__ . '/../customer/partials/status_badge.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Shopper Dashboard</h1>
        <p class="text-muted mb-0">Overview of all customer orders</p>
    </div>
    <a href="<?= $baseUrl ?>/shopper/orders" class="btn btn-primary">
        <i class="bi bi-list-ul me-1"></i> View All Orders
    </a>
</div>

<?php if (!empty($insight)): ?>
    <?php
    $insightType = $insight['type'] ?? 'info';
    $insightIcons = [
        'warning'   => 'bi-exclamation-triangle',
        'info'      => 'bi-lightbulb',
        'success'   => 'bi-check-circle',
        'primary'   => 'bi-info-circle',
        'secondary' => 'bi-chat-dots',
    ];
    $insightIcon = $insightIcons[$insightType] ?? 'bi-lightbulb';
    ?>
    <div class="alert alert-<?= htmlspecialchars($insightType) ?> d-flex align-items-start gap-2 mb-4" role="status">
        <i class="bi <?= $insightIcon ?> flex-shrink-0 mt-1"></i>
        <div>
            <strong class="d-block mb-1">Dashboard Insight</strong>
            <span><?= htmlspecialchars($insight['text']) ?></span>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int) $stats['total'] ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-secondary-subtle text-secondary">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int) $stats['pending'] ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-cart3"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int) $stats['in_progress'] ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int) $stats['delivered_today'] ?></div>
                    <div class="stat-label">Delivered Today</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Recent Orders</span>
        <a href="<?= $baseUrl ?>/shopper/orders" class="btn btn-sm btn-outline-primary">See all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No orders yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent as $order): ?>
                        <tr>
                            <td class="fw-semibold text-nowrap"><?= htmlspecialchars($order['order_number']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($order['title']) ?></td>
                            <td>
                                <?php $status = $order['status']; require $badgePartial; ?>
                            </td>
                            <td class="text-muted small text-nowrap">
                                <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= $baseUrl ?>/shopper/orders/<?= (int) $order['id'] ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
