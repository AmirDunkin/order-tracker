<?php
require __DIR__ . '/partials/helpers.php';

use App\Models\Order;

$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$badgePartial = __DIR__ . '/../customer/partials/status_badge.php';
$page = $pagination['page'];
$totalPages = $pagination['total_pages'];
$total = $pagination['total'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">All Orders</h1>
        <p class="text-muted mb-0"><?= (int) $total ?> order<?= $total === 1 ? '' : 's' ?> found</p>
    </div>
    <a href="<?= $baseUrl ?>/shopper/dashboard" class="btn btn-outline-secondary">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
</div>

<?php $audience = 'shopper'; require __DIR__ . '/../partials/status_legend.php'; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $baseUrl ?>/shopper/orders" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="search" class="form-label small fw-semibold">Search</label>
                <input
                    type="search"
                    class="form-control"
                    id="search"
                    name="search"
                    placeholder="Order #, title, customer…"
                    value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                >
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small fw-semibold">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach (Order::allStatuses() as $s): ?>
                        <option
                            value="<?= $s ?>"
                            title="<?= htmlspecialchars(Order::statusTooltip($s)) ?>"
                            <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars(Order::statusLabel($s)) ?> — <?= htmlspecialchars(Order::statusTooltip($s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small fw-semibold">From</label>
                <input
                    type="date"
                    class="form-control"
                    id="date_from"
                    name="date_from"
                    value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                >
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small fw-semibold">To</label>
                <input
                    type="date"
                    class="form-control"
                    id="date_to"
                    name="date_to"
                    value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                >
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= $baseUrl ?>/shopper/orders" class="btn btn-outline-secondary">Reset</a>
            </div>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($filters['sort'] ?? 'created_at') ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($filters['dir'] ?? 'desc') ?>">
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="orders-table">
            <thead class="table-light">
                <tr>
                    <th><?= shopper_sort_link('order_number', 'Order #', $filters) ?></th>
                    <th><?= shopper_sort_link('customer_name', 'Customer', $filters) ?></th>
                    <th><?= shopper_sort_link('title', 'Title', $filters) ?></th>
                    <th><?= shopper_sort_link('status', 'Status', $filters) ?></th>
                    <th><?= shopper_sort_link('priority', 'Priority', $filters) ?></th>
                    <th><?= shopper_sort_link('created_at', 'Placed', $filters) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No orders match your filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="fw-semibold text-nowrap"><?= htmlspecialchars($order['order_number']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($order['title']) ?></td>
                            <td>
                                <?php $status = $order['status']; require $badgePartial; ?>
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
                                <a href="<?= $baseUrl ?>/shopper/orders/<?= (int) $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $prevDisabled = $page <= 1;
                    $nextDisabled = $page >= $totalPages;
                    ?>
                    <li class="page-item <?= $prevDisabled ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $prevDisabled ? '#' : htmlspecialchars(shopper_query($filters, ['page' => $page - 1])) ?>">Previous</a>
                    </li>
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars(shopper_query($filters, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $nextDisabled ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $nextDisabled ? '#' : htmlspecialchars(shopper_query($filters, ['page' => $page + 1])) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
