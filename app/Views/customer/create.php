<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/customer/orders">My Orders</a></li>
        <li class="breadcrumb-item active">New Order</li>
    </ol>
</nav>

<div class="mb-4">
    <h1 class="h3 mb-1">Submit New Order</h1>
    <p class="text-muted mb-0">Fill in the details below and add the items you need</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= $baseUrl ?>/customer/orders" id="order-form" novalidate>
            <?php require __DIR__ . '/partials/order_form_fields.php'; ?>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">Submit Order</button>
                <a href="<?= $baseUrl ?>/customer/orders" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
