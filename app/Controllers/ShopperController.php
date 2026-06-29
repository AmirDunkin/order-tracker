<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\DashboardInsightService;
use Core\Controller;

class ShopperController extends Controller
{
    public function dashboard(): void
    {
        $this->requireRole('shopper');

        $orderModel = new Order();
        $insightService = new DashboardInsightService();

        $this->view('shopper/dashboard', [
            'title'   => 'Shopper Dashboard',
            'user'    => $this->user(),
            'stats'   => $orderModel->getDashboardStats(),
            'recent'  => $orderModel->getRecent(10),
            'insight' => $insightService->forShopper(),
            'flash'   => $this->getFlash(),
        ]);
    }

    public function orders(): void
    {
        $this->requireRole('shopper');

        $filters = [
            'status'    => trim((string) ($_GET['status'] ?? '')),
            'search'    => trim((string) ($_GET['search'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to'   => trim((string) ($_GET['date_to'] ?? '')),
            'sort'      => trim((string) ($_GET['sort'] ?? 'created_at')),
            'dir'       => trim((string) ($_GET['dir'] ?? 'desc')),
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(5, min((int) ($_GET['per_page'] ?? 15), 50));

        $orderModel = new Order();
        $result = $orderModel->paginate($filters, $page, $perPage);

        $this->view('shopper/orders', [
            'title'       => 'All Orders',
            'user'        => $this->user(),
            'orders'      => $result['data'],
            'pagination'  => $result,
            'filters'     => $filters,
            'flash'       => $this->getFlash(),
        ]);
    }

    public function show(string $id): void
    {
        $this->requireRole('shopper');

        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->setFlash('error', 'Invalid order.');
            $this->redirect('/shopper/orders');
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect('/shopper/orders');
        }

        $logModel = new OrderStatusLog();
        $timeline = $logModel->findByOrderId($orderId);
        $allowedNext = Order::allowedTransitions((string) $order['status']);

        $this->view('shopper/show', [
            'title'        => 'Order ' . $order['order_number'],
            'user'         => $this->user(),
            'order'        => $order,
            'timeline'     => $timeline,
            'allowedNext'  => $allowedNext,
            'flash'        => $this->getFlash(),
            'scripts'      => [
                rtrim($this->config['app']['url'], '/') . '/js/shopper-status.js',
                rtrim($this->config['app']['url'], '/') . '/js/shopper-ai.js',
            ],
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->requireRole('shopper');

        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid order.'], 400);
        }

        $input = $_POST !== [] ? $_POST : $this->jsonInput();
        $newStatus = trim((string) ($input['status'] ?? ''));
        $note      = trim((string) ($input['note'] ?? ''));

        if ($newStatus === '') {
            $this->json(['success' => false, 'message' => 'Status is required.'], 422);
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $currentStatus = (string) $order['status'];

        if (!Order::canTransition($currentStatus, $newStatus)) {
            $this->json([
                'success' => false,
                'message' => 'Cannot change status from ' . ucfirst($currentStatus) . ' to ' . ucfirst($newStatus) . '.',
            ], 422);
        }

        $user = $this->user();
        $logModel = new OrderStatusLog();

        $success = $logModel->logStatusChange(
            $orderId,
            $user['id'],
            $currentStatus,
            $newStatus,
            $note !== '' ? $note : null
        );

        if (!$success) {
            $this->json(['success' => false, 'message' => 'Failed to update order status.'], 500);
        }

        $updated = $orderModel->findById($orderId);
        $logs = $logModel->findByOrderId($orderId);
        $latestLog = $logs !== [] ? $logs[array_key_last($logs)] : null;

        $this->json([
            'success' => true,
            'message' => 'Status updated to ' . ucfirst($newStatus) . '.',
            'order'   => [
                'id'          => (int) $updated['id'],
                'status'      => (string) $updated['status'],
                'badge_class' => Order::statusBadgeClass((string) $updated['status']),
                'updated_at'  => (string) $updated['updated_at'],
            ],
            'timeline_entry' => $latestLog ? [
                'id'              => (int) $latestLog['id'],
                'old_status'      => $latestLog['old_status'],
                'new_status'      => (string) $latestLog['new_status'],
                'note'            => $latestLog['note'],
                'changed_by_name' => (string) $latestLog['changed_by_name'],
                'created_at'      => (string) $latestLog['created_at'],
                'badge_class'     => Order::statusBadgeClass((string) $latestLog['new_status']),
            ] : null,
            'allowed_next' => Order::allowedTransitions($newStatus),
        ]);
    }
}
