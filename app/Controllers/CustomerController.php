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
        $input = $this->orderInputFromPost();
        $_SESSION['old_order'] = $input;

        if (!$this->validateOrderInput($input)) {
            $this->redirect('/customer/orders/create');
        }

        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber();

        $orderId = $orderModel->create([
            'order_number'     => $orderNumber,
            'customer_id'      => $user['id'],
            'title'            => $input['title'],
            'description'      => $input['description'],
            'items'            => $input['items'],
            'priority'         => $input['priority'],
            'delivery_address' => $input['delivery_address'],
            'notes'            => $input['notes'],
        ]);

        $logModel = new OrderStatusLog();
        $logModel->logStatusChange(
            $orderId,
            $user['id'],
            null,
            'pending',
            'Order placed by customer'
        );

        unset($_SESSION['old_order']);

        $this->setFlash('success', "Order {$orderNumber} submitted successfully.");
        $this->redirect('/customer/orders/' . $orderId);
    }

    public function show(string $id): void
    {
        $this->requireRole('customer');

        $order = $this->findCustomerOrder($id);
        if ($order === null) {
            return;
        }

        $logModel = new OrderStatusLog();
        $timeline = $logModel->findByOrderId((int) $order['id']);

        $this->view('customer/show', [
            'title'    => 'Order ' . $order['order_number'],
            'user'     => $this->user(),
            'order'    => $order,
            'timeline' => $timeline,
            'flash'    => $this->getFlash(),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole('customer');

        $order = $this->findCustomerOrder($id);
        if ($order === null) {
            return;
        }

        if ($order['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending orders can be edited.');
            $this->redirect('/customer/orders/' . $order['id']);
        }

        $old = $_SESSION['old_order'] ?? [
            'title'            => $order['title'],
            'description'      => $order['description'] ?? '',
            'delivery_address' => $order['delivery_address'],
            'priority'         => $order['priority'],
            'notes'            => $order['notes'] ?? '',
            'items'            => $order['items'],
        ];

        $this->view('customer/edit', [
            'title'    => 'Edit Order',
            'user'     => $this->user(),
            'order'    => $order,
            'flash'    => $this->getFlash(),
            'old'      => $old,
            'scripts'  => [
                'https://code.jquery.com/jquery-3.7.1.min.js',
                rtrim($this->config['app']['url'], '/') . '/js/customer-order.js',
            ],
        ]);

        unset($_SESSION['old_order']);
    }

    public function update(string $id): void
    {
        $this->requireRole('customer');

        $order = $this->findCustomerOrder($id);
        if ($order === null) {
            return;
        }

        if ($order['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending orders can be edited.');
            $this->redirect('/customer/orders/' . $order['id']);
        }

        $input = $this->orderInputFromPost();
        $_SESSION['old_order'] = $input;

        if (!$this->validateOrderInput($input)) {
            $this->redirect('/customer/orders/' . $order['id'] . '/edit');
        }

        $orderModel = new Order();
        $mergedItems = $this->preserveShopperItemFields($order['items'], $input['items']);

        $orderModel->update((int) $order['id'], [
            'title'            => $input['title'],
            'description'      => $input['description'],
            'items'            => $mergedItems,
            'priority'         => $input['priority'],
            'delivery_address' => $input['delivery_address'],
            'notes'            => $input['notes'],
        ]);

        unset($_SESSION['old_order']);

        $this->setFlash('success', 'Order updated successfully.');
        $this->redirect('/customer/orders/' . $order['id']);
    }

    public function cancel(string $id): void
    {
        $this->requireRole('customer');

        $order = $this->findCustomerOrder($id);
        if ($order === null) {
            return;
        }

        if ($order['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending orders can be cancelled.');
            $this->redirect('/customer/orders/' . $order['id']);
        }

        $user = $this->user();
        $logModel = new OrderStatusLog();
        $note = trim((string) ($_POST['note'] ?? ''));

        if (!$logModel->logStatusChange(
            (int) $order['id'],
            $user['id'],
            'pending',
            'cancelled',
            $note !== '' ? $note : 'Cancelled by customer'
        )) {
            $this->setFlash('error', 'Could not cancel the order. Please try again.');
            $this->redirect('/customer/orders/' . $order['id']);
        }

        $this->setFlash('success', 'Order cancelled successfully.');
        $this->redirect('/customer/orders/' . $order['id']);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findCustomerOrder(string $id): ?array
    {
        $user = $this->user();
        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->setFlash('error', 'Invalid order.');
            $this->redirect('/customer/orders');
            return null;
        }

        $orderModel = new Order();
        $order = $orderModel->findForCustomer($orderId, $user['id']);

        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect('/customer/orders');
            return null;
        }

        return $order;
    }

    /**
     * @return array{
     *   title: string,
     *   description: string|null,
     *   delivery_address: string,
     *   priority: string,
     *   notes: string|null,
     *   items: array<int, array<string, mixed>>
     * }
     */
    private function orderInputFromPost(): array
    {
        $title           = trim((string) ($_POST['title'] ?? ''));
        $description     = trim((string) ($_POST['description'] ?? ''));
        $deliveryAddress = trim((string) ($_POST['delivery_address'] ?? ''));
        $priority        = (string) ($_POST['priority'] ?? 'normal');
        $notes           = trim((string) ($_POST['notes'] ?? ''));

        return [
            'title'            => $title,
            'description'      => $description !== '' ? $description : null,
            'delivery_address' => $deliveryAddress,
            'priority'         => $priority,
            'notes'            => $notes !== '' ? $notes : null,
            'items'            => Order::normalizeItems(
                $this->buildItemsFromPost(
                    $_POST['item_name'] ?? [],
                    $_POST['item_qty'] ?? [],
                    $_POST['item_unit'] ?? [],
                    $_POST['item_substitute'] ?? []
                )
            ),
        ];
    }

    /**
     * @param array{
     *   title: string,
     *   description: string|null,
     *   delivery_address: string,
     *   priority: string,
     *   notes: string|null,
     *   items: array<int, array<string, mixed>>
     * } $input
     */
    private function validateOrderInput(array $input): bool
    {
        if ($input['title'] === '' || $input['delivery_address'] === '') {
            $this->setFlash('error', 'Title and delivery address are required.');
            return false;
        }

        if (!in_array($input['priority'], ['normal', 'urgent'], true)) {
            $this->setFlash('error', 'Invalid priority selected.');
            return false;
        }

        if ($input['items'] === []) {
            $this->setFlash('error', 'Please add at least one item.');
            return false;
        }

        return true;
    }

    /**
     * @param mixed $names
     * @param mixed $qtys
     * @param mixed $units
     * @param mixed $substitutes
     * @return array<int, array<string, mixed>>
     */
    private function buildItemsFromPost(mixed $names, mixed $qtys, mixed $units, mixed $substitutes): array
    {
        if (!is_array($names) || !is_array($qtys)) {
            return [];
        }

        $items = [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty  = (int) ($qtys[$i] ?? 0);
            $unit = trim((string) (is_array($units) ? ($units[$i] ?? 'pcs') : 'pcs'));

            if ($name === '' || $qty < 1) {
                continue;
            }

            $items[] = [
                'name'          => $name,
                'qty'           => $qty,
                'unit'          => $unit !== '' ? $unit : 'pcs',
                'substitute_ok' => is_array($substitutes) && (($substitutes[$i] ?? '0') === '1' || ($substitutes[$i] ?? false) === true),
            ];
        }

        return $items;
    }

    /**
     * Keep shopper progress when a customer edits a pending order.
     *
     * @param array<int, array<string, mixed>> $existing
     * @param array<int, array<string, mixed>> $updated
     * @return array<int, array<string, mixed>>
     */
    private function preserveShopperItemFields(array $existing, array $updated): array
    {
        foreach ($updated as $index => $item) {
            if (!isset($existing[$index])) {
                continue;
            }

            $updated[$index]['item_status']  = $existing[$index]['item_status'] ?? 'pending';
            $updated[$index]['shopper_note'] = $existing[$index]['shopper_note'] ?? null;
        }

        return $updated;
    }
}
