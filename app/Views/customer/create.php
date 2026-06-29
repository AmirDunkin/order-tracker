<?php
$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$oldItems = $old['items'] ?? [['name' => '', 'qty' => 1]];
if ($oldItems === []) {
    $oldItems = [['name' => '', 'qty' => 1]];
}
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
            <div class="row g-3">
                <div class="col-12">
                    <label for="title" class="form-label fw-semibold">Order Title <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        placeholder="e.g. Weekly Groceries"
                        value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Optional details about your order"
                    ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Items <span class="text-danger">*</span></label>
                    <div id="items-container">
                        <?php foreach ($oldItems as $index => $item): ?>
                            <div class="item-row row g-2 mb-2 align-items-end">
                                <div class="col-md-7">
                                    <?php if ($index === 0): ?>
                                        <label class="form-label small text-muted">Item name</label>
                                    <?php endif; ?>
                                    <input
                                        type="text"
                                        class="form-control item-name"
                                        name="item_name[]"
                                        placeholder="Item name"
                                        value="<?= htmlspecialchars($item['name'] ?? '') ?>"
                                    >
                                </div>
                                <div class="col-md-3">
                                    <?php if ($index === 0): ?>
                                        <label class="form-label small text-muted">Quantity</label>
                                    <?php endif; ?>
                                    <input
                                        type="number"
                                        class="form-control item-qty"
                                        name="item_qty[]"
                                        min="1"
                                        value="<?= (int) ($item['qty'] ?? 1) ?>"
                                    >
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item w-100" <?= count($oldItems) === 1 ? 'disabled' : '' ?>>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-item-btn">
                        + Add Item
                    </button>
                </div>

                <div class="col-md-8">
                    <label for="delivery_address" class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control"
                        id="delivery_address"
                        name="delivery_address"
                        rows="2"
                        placeholder="Full delivery address"
                        required
                    ><?= htmlspecialchars($old['delivery_address'] ?? '') ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="priority" class="form-label fw-semibold">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="normal" <?= ($old['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="urgent" <?= ($old['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea
                        class="form-control"
                        id="notes"
                        name="notes"
                        rows="2"
                        placeholder="Special instructions for the shopper"
                    ><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">Submit Order</button>
                <a href="<?= $baseUrl ?>/customer/orders" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<template id="item-row-template">
    <div class="item-row row g-2 mb-2 align-items-end">
        <div class="col-md-7">
            <input type="text" class="form-control item-name" name="item_name[]" placeholder="Item name">
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control item-qty" name="item_qty[]" min="1" value="1">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm remove-item w-100">Remove</button>
        </div>
    </div>
</template>
