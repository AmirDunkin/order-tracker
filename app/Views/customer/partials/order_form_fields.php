<?php
/** @var array<string, mixed> $old */
$oldItems = $old['items'] ?? [['name' => '', 'qty' => 1, 'unit' => 'pcs', 'substitute_ok' => false]];
if ($oldItems === []) {
    $oldItems = [['name' => '', 'qty' => 1, 'unit' => 'pcs', 'substitute_ok' => false]];
}

$unitOptions = ['pcs', 'bottle', 'pack', 'bag', 'kg', 'g', 'lb', 'loaf', 'box', 'carton'];
?>

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
        <p class="small text-muted mb-2">Add each item with quantity and unit. Check &ldquo;Substitute OK&rdquo; if another brand or size is acceptable.</p>
        <div id="items-container">
            <?php foreach ($oldItems as $index => $item): ?>
                <div class="item-row row g-2 mb-2 align-items-end">
                    <div class="col-md-5">
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
                    <div class="col-md-2">
                        <?php if ($index === 0): ?>
                            <label class="form-label small text-muted">Qty</label>
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
                        <?php if ($index === 0): ?>
                            <label class="form-label small text-muted">Unit</label>
                        <?php endif; ?>
                        <select class="form-select item-unit" name="item_unit[]">
                            <?php
                            $selectedUnit = $item['unit'] ?? 'pcs';
                            foreach ($unitOptions as $unit):
                            ?>
                                <option value="<?= htmlspecialchars($unit) ?>" <?= $selectedUnit === $unit ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($unit) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <?php if ($index === 0): ?>
                            <label class="form-label small text-muted">Substitute</label>
                        <?php endif; ?>
                        <div class="form-check mt-2">
                            <input type="hidden" name="item_substitute[<?= $index ?>]" value="0">
                            <input
                                type="checkbox"
                                class="form-check-input item-substitute"
                                name="item_substitute[<?= $index ?>]"
                                value="1"
                                <?= !empty($item['substitute_ok']) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label small">OK</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item w-100" <?= count($oldItems) === 1 ? 'disabled' : '' ?>>
                            &times;
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

<template id="item-row-template">
    <div class="item-row row g-2 mb-2 align-items-end">
        <div class="col-md-5">
            <input type="text" class="form-control item-name" name="item_name[]" placeholder="Item name">
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control item-qty" name="item_qty[]" min="1" value="1">
        </div>
        <div class="col-md-2">
            <select class="form-select item-unit" name="item_unit[]">
                <?php foreach ($unitOptions as $unit): ?>
                    <option value="<?= htmlspecialchars($unit) ?>"><?= htmlspecialchars($unit) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check mt-2">
                <input type="hidden" name="item_substitute[__INDEX__]" value="0">
                <input type="checkbox" class="form-check-input item-substitute" name="item_substitute[__INDEX__]" value="1">
                <label class="form-check-label small">OK</label>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm remove-item w-100">&times;</button>
        </div>
    </div>
</template>
