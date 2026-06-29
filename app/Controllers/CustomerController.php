<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Core\Controller;

class CustomerController extends Controller
{
    public function index(): void
    {
        $this->requireRole('customer');

        $user = $this->user();
        $search = trim((string) ($_GET['search'] ?? ''));

        $orderModel = new Order();
        $orders = $orderModel->findByCustomerId($user['id'], $search !== '' ? $search : null);

        $this->view('customer/index', [
            'title'  => 'My Orders',
            'user'   => $user,
            'orders' => $orders,
            'search' => $search,
            'flash'  => $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole('customer');

        $this->view('customer/create', [
            'title'    => 'New Order',
            'user'     => $this->user(),
            'flash'    => $this->getFlash(),
            'old'      => $_SESSION['old_order'] ?? [],
            'scripts'  => [
                'https://code.jquery.com/jquery-3.7.1.min.js',
                rtrim($this->config['app']['url'], '/') . '/js/customer-order.js',
            ],
        ]);

        unset($_SESSION['old_order']);
    }

    public function store(): void
    {
        $this->requireRole('customer');

        $user = $this->user();
        $title            = trim((string) ($_POST['title'] ?? ''));
        $description      = trim((string) ($_POST['description'] ?? ''));
        $deliveryAddress  = trim((string) ($_POST['delivery_address'] ?? ''));
        $priority         = (string) ($_POST['priority'] ?? 'normal');
        $notes            = trim((string) ($_POST['notes'] ?? ''));
        $itemNames        = $_POST['item_name'] ?? [];
        $itemQtys         = $_POST['item_qty'] ?? [];

        $_SESSION['old_order'] = [
            'title'            => $title,
            'description'      => $description,
            'delivery_address' => $deliveryAddress,
            'priority'         => $priority,
            'notes'            => $notes,
            'items'            => $this->buildItemsFromPost($itemNames, $itemQtys),
        ];

        if ($title === '' || $deliveryAddress === '') {
            $this->setFlash('error', 'Title and delivery address are required.');
            $this->redirect('/customer/orders/create');
        }

        if (!in_array($priority, ['normal', 'urgent'], true)) {
            $this->setFlash('error', 'Invalid priority selected.');
            $this->redirect('/customer/orders/create');
        }

        $items = $this->buildItemsFromPost($itemNames, $itemQtys);

        if ($items === []) {
            $this->setFlash('error', 'Please add at least one item.');
            $this->redirect('/customer/orders/create');
        }

        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber();

        $orderId = $orderModel->create([
            'order_number'     => $orderNumber,
            'customer_id'      => $user['id'],
            'title'            => $title,
            'description'      => $description !== '' ? $description : null,
            'items'            => $items,
            'priority'         => $priority,
            'delivery_address' => $deliveryAddress,
            'notes'            => $notes !== '' ? $notes : null,
        ]);

        $logModel = new OrderStatusLog();
        $logModel->create([
            'order_id'   => $orderId,
            'changed_by' => $user['id'],
            'old_status' => null,
            'new_status' => 'pending',
            'note'       => 'Order placed by customer',
        ]);

        unset($_SESSION['old_order']);

        $this->setFlash('success', "Order {$orderNumber} submitted successfully.");
        $this->redirect('/customer/orders/' . $orderId);
    }

    public function show(string $id): void
    {
        $this->requireRole('customer');

        $user = $this->user();
        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->setFlash('error', 'Invalid order.');
            $this->redirect('/customer/orders');
        }

        $orderModel = new Order();
        $order = $orderModel->findForCustomer($orderId, $user['id']);

        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect('/customer/orders');
        }

        $logModel = new OrderStatusLog();
        $timeline = $logModel->findByOrderId($orderId);

        $this->view('customer/show', [
            'title'    => 'Order ' . $order['order_number'],
            'user'     => $user,
            'order'    => $order,
            'timeline' => $timeline,
            'flash'    => $this->getFlash(),
        ]);
    }

    /**
     * @param mixed $names
     * @param mixed $qtys
     * @return array<int, array{name: string, qty: int}>
     */
    private function buildItemsFromPost(mixed $names, mixed $qtys): array
    {
        if (!is_array($names) || !is_array($qtys)) {
            return [];
        }

        $items = [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty  = (int) ($qtys[$i] ?? 0);

            if ($name === '' || $qty < 1) {
                continue;
            }

            $items[] = ['name' => $name, 'qty' => $qty];
        }

        return $items;
    }
}
