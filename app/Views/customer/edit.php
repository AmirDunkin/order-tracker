<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/customer/orders">My Orders</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/customer/orders/<?= (int) $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<div class="mb-4">
    <h1 class="h3 mb-1">Edit Order</h1>
    <p class="text-muted mb-0"><?= htmlspecialchars($order['order_number']) ?> — only pending orders can be changed</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= $baseUrl ?>/customer/orders/<?= (int) $order['id'] ?>/update" id="order-form" novalidate>
            <?php require __DIR__ . '/partials/order_form_fields.php'; ?>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= $baseUrl ?>/customer/orders/<?= (int) $order['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
