<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">My Orders</h1>
        <p class="text-muted mb-0">Track the status of your submitted orders</p>
    </div>
    <a href="<?= $baseUrl ?>/customer/orders/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
        New Order
    </a>
</div>

<?php require __DIR__ . '/../partials/status_legend.php'; ?>

<form method="GET" action="<?= $baseUrl ?>/customer/orders" class="mb-4">
    <div class="input-group">
        <input
            type="search"
            name="search"
            class="form-control"
            placeholder="Search by order number…"
            value="<?= htmlspecialchars($search ?? '') ?>"
        >
        <button class="btn btn-outline-secondary" type="submit">Search</button>
        <?php if (!empty($search)): ?>
            <a href="<?= $baseUrl ?>/customer/orders" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <?php if (!empty($search)): ?>
                <p class="text-muted mb-3">No orders matching "<?= htmlspecialchars($search) ?>".</p>
                <a href="<?= $baseUrl ?>/customer/orders" class="btn btn-outline-primary btn-sm">View all orders</a>
            <?php else: ?>
                <p class="text-muted mb-3">You have no orders yet.</p>
                <a href="<?= $baseUrl ?>/customer/orders/create" class="btn btn-primary">Place your first order</a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Title</th>
                    <th>Status <span class="text-muted fw-normal" title="Hover a badge for a quick summary">(?)</span></th>
                    <th>Priority</th>
                    <th>Placed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="fw-semibold text-nowrap"><?= htmlspecialchars($order['order_number']) ?></td>
                        <td><?= htmlspecialchars($order['title']) ?></td>
                        <td>
                            <?php $status = $order['status']; require __DIR__ . '/partials/status_badge.php'; ?>
                        </td>
                        <td>
                            <?php if ($order['priority'] === 'urgent'): ?>
                                <span class="badge bg-danger">Urgent</span>
                            <?php else: ?>
                                <span class="text-muted small">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small text-nowrap">
                            <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= $baseUrl ?>/customer/orders/<?= (int) $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
