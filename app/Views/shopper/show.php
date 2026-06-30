<?php
use App\Models\Order;

$baseUrl = rtrim($config['app']['url'] ?? '', '/');
$badgePartial = __DIR__ . '/../customer/partials/status_badge.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/shopper/orders">All Orders</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($order['order_number']) ?></li>
    </ol>
</nav>

<div id="status-alert" class="alert d-none" role="alert"></div>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= htmlspecialchars($order['title']) ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($order['order_number']) ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center" id="order-status-badge">
        <?php $status = $order['status']; require $badgePartial; ?>
        <?php if ($order['priority'] === 'urgent'): ?>
            <span class="badge bg-danger">Urgent</span>
        <?php endif; ?>
    </div>
</div>

<?php $status = $order['status']; $audience = 'shopper'; require __DIR__ . '/../partials/status_notice.php'; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Order Details</div>
            <div class="card-body">
                <div class="row g-3 mb-3 small">
                    <div class="col-sm-6">
                        <span class="text-muted">Customer</span>
                        <div class="fw-semibold"><?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
                        <div class="text-muted"><?= htmlspecialchars($order['customer_email'] ?? '') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Placed</span>
                        <div><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                </div>

                <?php if (!empty($order['description'])): ?>
                    <p class="mb-3"><?= nl2br(htmlspecialchars($order['description'])) ?></p>
                <?php endif; ?>

                <h6 class="fw-semibold mb-2">Items</h6>
                <?php if (!empty($itemsEditable)): ?>
                    <form id="shopper-items-form"
                          data-url="<?= $baseUrl ?>/shopper/orders/<?= (int) $order['id'] ?>/items"
                          class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Qty</th>
                                        <th>Unit</th>
                                        <th>Substitute</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $index => $item): ?>
                                        <?php $itemStatus = (string) ($item['item_status'] ?? 'pending'); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name'] ?? '') ?></td>
                                            <td class="text-end"><?= (int) ($item['qty'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars($item['unit'] ?? 'pcs') ?></td>
                                            <td>
                                                <?php if (!empty($item['substitute_ok'])): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">OK</span>
                                                <?php else: ?>
                                                    <span class="text-muted small">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" name="item_status[<?= (int) $index ?>]">
                                                    <?php foreach (Order::allItemStatuses() as $statusOption): ?>
                                                        <option value="<?= htmlspecialchars($statusOption) ?>" <?= $itemStatus === $statusOption ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars(Order::itemStatusLabel($statusOption)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    name="shopper_note[<?= (int) $index ?>]"
                                                    placeholder="e.g. Anchor brand used"
                                                    value="<?= htmlspecialchars($item['shopper_note'] ?? '') ?>"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm" id="shopper-items-submit">
                            <span class="submit-text">Save Item Updates</span>
                            <span class="spinner-border spinner-border-sm d-none ms-1" id="shopper-items-spinner" role="status"></span>
                        </button>
                    </form>
                <?php else: ?>
                    <?php
                    $items = $order['items'];
                    $showShopperNotes = in_array($order['status'], ['delivered', 'cancelled'], true);
                    require __DIR__ . '/../customer/partials/order_items_table.php';
                    ?>
                <?php endif; ?>

                <h6 class="fw-semibold mb-1">Delivery Address</h6>
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>

                <?php if (!empty($order['notes'])): ?>
                    <h6 class="fw-semibold mb-1">Customer Notes</h6>
                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Smart Order Summary (AI) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-stars text-primary"></i> Smart Order Summary
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Get an AI-powered shopping tip based on items, priority, and delivery location.</p>
                <button
                    type="button"
                    class="btn btn-outline-primary w-100"
                    id="ai-suggest-btn"
                    data-url="<?= $baseUrl ?>/api/ai-suggest"
                    data-items="<?= htmlspecialchars(json_encode($order['items'], JSON_THROW_ON_ERROR), ENT_QUOTES) ?>"
                    data-priority="<?= htmlspecialchars($order['priority']) ?>"
                    data-address="<?= htmlspecialchars($order['delivery_address']) ?>"
                >
                    <i class="bi bi-magic me-1"></i>
                    <span class="btn-text">Get AI Suggestion</span>
                    <span class="spinner-border spinner-border-sm d-none ms-1" id="ai-suggest-spinner" role="status"></span>
                </button>
                <div id="ai-suggestion-alert" class="alert alert-info alert-dismissible fade mt-3 mb-0 d-none" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-lightbulb flex-shrink-0 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">Shopping Tip</strong>
                            <span id="ai-suggestion-text"></span>
                            <span id="ai-suggestion-source" class="badge bg-secondary ms-1 d-none"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <?php if (!empty($allowedNext)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Update Status</div>
                <div class="card-body">
                    <form id="status-update-form"
                          data-url="<?= $baseUrl ?>/shopper/orders/<?= (int) $order['id'] ?>/status"
                          data-order-id="<?= (int) $order['id'] ?>"
                          data-status-meta="<?= htmlspecialchars(json_encode(Order::statusMetaForJs(), JSON_THROW_ON_ERROR), ENT_QUOTES) ?>">
                        <div class="mb-3">
                            <label for="new_status" class="form-label fw-semibold">New Status</label>
                            <select class="form-select" id="new_status" name="status" required>
                                <option value="">Select next status…</option>
                                <?php foreach ($allowedNext as $next): ?>
                                    <option
                                        value="<?= htmlspecialchars($next) ?>"
                                        data-hint="<?= htmlspecialchars(Order::statusTransitionHint($next)) ?>"
                                    >
                                        <?= htmlspecialchars(Order::statusShopperActionLabel($next)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="status-action-hint" class="status-action-hint small text-muted mt-2 d-none" role="note"></div>
                        </div>
                        <div class="mb-3">
                            <label for="status_note" class="form-label fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea class="form-control" id="status_note" name="note" rows="3" placeholder="Add a note about this status change"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="status-submit-btn">
                            <span class="submit-text">Update Status</span>
                            <span class="spinner-border spinner-border-sm d-none" id="status-spinner" role="status"></span>
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                    <?= htmlspecialchars(Order::statusShopperMessage((string) $order['status'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Status Timeline</div>
            <div class="card-body" id="status-timeline">
                <p class="small text-muted mb-3">Status changes are logged here so customers can follow progress.</p>
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
                            <div class="timeline-item <?= $isLast ? 'timeline-item-last' : '' ?>" data-log-id="<?= (int) $log['id'] ?>">
                                <div class="timeline-marker bg-<?= htmlspecialchars($badgeClass) ?>"></div>
                                <div class="timeline-content pb-4">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <?php $status = $logStatus; require $badgePartial; ?>
                                        <span class="text-muted small timeline-date">
                                            <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($log['note'])): ?>
                                        <p class="small mb-1 timeline-note"><?= htmlspecialchars($log['note']) ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted mb-0">by <?= htmlspecialchars($log['changed_by_name']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= $baseUrl ?>/shopper/orders" class="btn btn-outline-secondary">&larr; Back to orders</a>
</div>
