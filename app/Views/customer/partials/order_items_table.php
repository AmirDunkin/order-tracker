<?php
use App\Models\Order;

/** @var array<int, array<string, mixed>> $items */
/** @var bool $showShopperNotes */
$showShopperNotes = $showShopperNotes ?? false;
?>

<div class="table-responsive">
    <table class="table table-sm mb-0">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th>Unit</th>
                <?php if ($showShopperNotes): ?>
                    <th>Status</th>
                    <th>Shopper note</th>
                <?php else: ?>
                    <th>Substitute</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                $itemStatus = (string) ($item['item_status'] ?? 'pending');
                $statusBadge = Order::itemStatusBadgeClass($itemStatus);
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name'] ?? '') ?></td>
                    <td class="text-end"><?= (int) ($item['qty'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($item['unit'] ?? 'pcs') ?></td>
                    <?php if ($showShopperNotes): ?>
                        <td>
                            <span class="badge bg-<?= htmlspecialchars($statusBadge) ?>">
                                <?= htmlspecialchars(Order::itemStatusLabel($itemStatus)) ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?= !empty($item['shopper_note'])
                                ? htmlspecialchars($item['shopper_note'])
                                : '—' ?>
                        </td>
                    <?php else: ?>
                        <td>
                            <?php if (!empty($item['substitute_ok'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Allowed</span>
                            <?php else: ?>
                                <span class="text-muted small">No</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
