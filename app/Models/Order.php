<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Order extends Model
{
    /** @var list<string> */
    private const STATUSES = ['pending', 'confirmed', 'shopping', 'ready', 'delivered', 'cancelled'];

    /** @var array<string, string> */
    private const SORTABLE_COLUMNS = [
        'order_number'  => 'o.order_number',
        'title'         => 'o.title',
        'customer_name' => 'u.name',
        'status'        => 'o.status',
        'priority'      => 'o.priority',
        'created_at'    => 'o.created_at',
    ];

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['shopping', 'cancelled'],
        'shopping'  => ['ready', 'cancelled'],
        'ready'     => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /** @var array<string, array<string, string>> */
    private const STATUS_META = [
        'pending' => [
            'label'           => 'Pending',
            'tooltip'         => 'Waiting for a personal shopper to review',
            'customer'        => 'Your order was received and is waiting for a personal shopper to review it.',
            'shopper'         => 'New order — review the details, then confirm or cancel.',
            'shopper_action'  => 'Confirm Order',
            'transition_hint' => 'Accept this order and queue it for shopping.',
            'alert'           => 'secondary',
            'icon'            => 'bi-hourglass-split',
        ],
        'confirmed' => [
            'label'           => 'Confirmed',
            'tooltip'         => 'Accepted and queued for shopping',
            'customer'        => 'A personal shopper has accepted your order and will start collecting items soon.',
            'shopper'         => 'Order accepted — start shopping when you are ready.',
            'shopper_action'  => 'Start Shopping',
            'transition_hint' => 'You are now collecting or purchasing the items.',
            'alert'           => 'info',
            'icon'            => 'bi-check-circle',
        ],
        'shopping' => [
            'label'           => 'Shopping',
            'tooltip'         => 'Items are being collected now',
            'customer'        => 'Your personal shopper is actively collecting the items for this order.',
            'shopper'         => 'Shopping in progress — mark ready once all items are collected and packed.',
            'shopper_action'  => 'Mark Ready',
            'transition_hint' => 'All items are collected and packed for delivery or pickup.',
            'alert'           => 'warning',
            'icon'            => 'bi-cart-check',
        ],
        'ready' => [
            'label'           => 'Ready',
            'tooltip'         => 'Packed and waiting for delivery',
            'customer'        => 'Your order is packed and ready. Delivery or pickup will happen soon.',
            'shopper'         => 'Order is packed — mark delivered once the customer receives it.',
            'shopper_action'  => 'Mark Delivered',
            'transition_hint' => 'The customer has received the order.',
            'alert'           => 'primary',
            'icon'            => 'bi-box-seam',
        ],
        'delivered' => [
            'label'           => 'Delivered',
            'tooltip'         => 'Order completed successfully',
            'customer'        => 'This order has been completed and delivered to you.',
            'shopper'         => 'This order is complete — no further action is required.',
            'shopper_action'  => '',
            'transition_hint' => '',
            'alert'           => 'success',
            'icon'            => 'bi-check2-all',
        ],
        'cancelled' => [
            'label'           => 'Cancelled',
            'tooltip'         => 'Order will not be fulfilled',
            'customer'        => 'This order was cancelled and will not be fulfilled.',
            'shopper'         => 'This order was cancelled — no further action is required.',
            'shopper_action'  => 'Cancel Order',
            'transition_hint' => 'The order will not be fulfilled. Add a note if helpful.',
            'alert'           => 'danger',
            'icon'            => 'bi-x-circle',
        ],
    ];

    /**
     * @return array<string, mixed>|false
     */
    public function findById(int $id): array|false
    {
        $order = $this->fetchOne(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE o.id = :id',
            ['id' => $id]
        );

        return $order ? $this->decodeItems($order) : false;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function findByOrderNumber(string $orderNumber): array|false
    {
        $order = $this->fetchOne(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE o.order_number = :order_number',
            ['order_number' => $orderNumber]
        );

        return $order ? $this->decodeItems($order) : false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByCustomerId(int $customerId, ?string $search = null): array
    {
        $sql = 'SELECT * FROM orders WHERE customer_id = :customer_id';
        $params = ['customer_id' => $customerId];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND order_number LIKE :search';
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= ' ORDER BY created_at DESC';

        $orders = $this->fetchAll($sql, $params);

        return array_map(fn (array $order) => $this->decodeItems($order), $orders);
    }

    /**
     * @return array<string, mixed>|false
     */
    public function findForCustomer(int $id, int $customerId): array|false
    {
        $order = $this->fetchOne(
            'SELECT * FROM orders WHERE id = :id AND customer_id = :customer_id',
            ['id' => $id, 'customer_id' => $customerId]
        );

        return $order ? $this->decodeItems($order) : false;
    }

    /**
     * @param array{status?: string, priority?: string, customer_id?: int} $filters
     * @return array<int, array<string, mixed>>
     */
    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT o.*, u.name AS customer_name
                FROM orders o
                JOIN users u ON u.id = o.customer_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND o.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= ' AND o.priority = :priority';
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= ' AND o.customer_id = :customer_id';
            $params['customer_id'] = $filters['customer_id'];
        }

        $sql .= ' ORDER BY o.created_at DESC';

        $orders = $this->fetchAll($sql, $params);

        return array_map(fn (array $order) => $this->decodeItems($order), $orders);
    }

    /**
     * @return array{total: int, pending: int, in_progress: int, delivered_today: int, urgent_pending: int}
     */
    public function getDashboardStats(): array
    {
        $total = $this->fetchOne('SELECT COUNT(*) AS cnt FROM orders');
        $pending = $this->fetchOne("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
        $inProgress = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM orders WHERE status IN ('confirmed', 'shopping', 'ready')"
        );
        $deliveredToday = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'delivered' AND DATE(updated_at) = CURDATE()"
        );
        $urgentPending = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending' AND priority = 'urgent'"
        );

        return [
            'total'           => (int) ($total['cnt'] ?? 0),
            'pending'         => (int) ($pending['cnt'] ?? 0),
            'in_progress'     => (int) ($inProgress['cnt'] ?? 0),
            'delivered_today' => (int) ($deliveredToday['cnt'] ?? 0),
            'urgent_pending'  => (int) ($urgentPending['cnt'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecent(int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));

        $orders = $this->fetchAll(
            'SELECT o.*, u.name AS customer_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             ORDER BY o.created_at DESC
             LIMIT ' . $limit
        );

        return array_map(fn (array $order) => $this->decodeItems($order), $orders);
    }

    /**
     * @param array{
     *   status?: string,
     *   search?: string,
     *   date_from?: string,
     *   date_to?: string,
     *   sort?: string,
     *   dir?: string
     * } $filters
     * @return array{
     *   data: array<int, array<string, mixed>>,
     *   total: int,
     *   page: int,
     *   per_page: int,
     *   total_pages: int
     * }
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(5, min($perPage, 50));
        $offset = ($page - 1) * $perPage;

        [$where, $params] = $this->buildShopperFilterWhere($filters);

        $sortKey = $filters['sort'] ?? 'created_at';
        $sortCol = self::SORTABLE_COLUMNS[$sortKey] ?? self::SORTABLE_COLUMNS['created_at'];
        $sortDir = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $countRow = $this->fetchOne(
            'SELECT COUNT(*) AS cnt
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE 1=1' . $where,
            $params
        );
        $total = (int) ($countRow['cnt'] ?? 0);

        $orders = $this->fetchAll(
            'SELECT o.*, u.name AS customer_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE 1=1' . $where . "
             ORDER BY {$sortCol} {$sortDir}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = (int) max(1, (int) ceil($total / $perPage));

        return [
            'data'        => array_map(fn (array $order) => $this->decodeItems($order), $orders),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedTransitions(string $currentStatus): array
    {
        return self::TRANSITIONS[$currentStatus] ?? [];
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::allowedTransitions($from), true);
    }

    /**
     * @return list<string>
     */
    public static function allStatuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @return array<string, string>|null
     */
    public static function statusMeta(string $status): ?array
    {
        return self::STATUS_META[$status] ?? null;
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUS_META[$status]['label'] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function statusTooltip(string $status): string
    {
        return self::STATUS_META[$status]['tooltip'] ?? self::statusLabel($status);
    }

    public static function statusCustomerMessage(string $status): string
    {
        return self::STATUS_META[$status]['customer'] ?? '';
    }

    public static function statusShopperMessage(string $status): string
    {
        return self::STATUS_META[$status]['shopper'] ?? '';
    }

    public static function statusShopperActionLabel(string $status): string
    {
        $label = self::STATUS_META[$status]['shopper_action'] ?? '';

        return $label !== '' ? $label : ucfirst($status);
    }

    public static function statusTransitionHint(string $status): string
    {
        return self::STATUS_META[$status]['transition_hint'] ?? '';
    }

    public static function statusAlertClass(string $status): string
    {
        return self::STATUS_META[$status]['alert'] ?? 'secondary';
    }

    public static function statusIcon(string $status): string
    {
        return self::STATUS_META[$status]['icon'] ?? 'bi-info-circle';
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function statusMetaForJs(): array
    {
        $payload = [];

        foreach (self::STATUSES as $status) {
            $meta = self::STATUS_META[$status] ?? [];

            $payload[$status] = [
                'label'           => $meta['label'] ?? self::statusLabel($status),
                'tooltip'         => $meta['tooltip'] ?? '',
                'customer'        => $meta['customer'] ?? '',
                'shopper'         => $meta['shopper'] ?? '',
                'shopper_action'  => $meta['shopper_action'] ?? '',
                'transition_hint' => $meta['transition_hint'] ?? '',
                'alert'           => $meta['alert'] ?? 'secondary',
                'icon'            => $meta['icon'] ?? 'bi-info-circle',
                'badge_class'     => self::statusBadgeClass($status),
            ];
        }

        return $payload;
    }

    /**
     * @param array{status?: string, search?: string, date_from?: string, date_to?: string} $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildShopperFilterWhere(array $filters): array
    {
        $where = '';
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $where .= ' AND o.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where .= ' AND (o.order_number LIKE :search OR o.title LIKE :search OR u.name LIKE :search)';
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where .= ' AND DATE(o.created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= ' AND DATE(o.created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return [$where, $params];
    }

    /**
     * @param array{
     *   order_number: string,
     *   customer_id: int,
     *   title: string,
     *   description?: string|null,
     *   items: array<int, array<string, mixed>>,
     *   status?: string,
     *   priority?: string,
     *   delivery_address: string,
     *   notes?: string|null
     * } $data
     */
    public function create(array $data): int
    {
        $id = $this->lastInsertId(
            'INSERT INTO orders (order_number, customer_id, title, description, items, status, priority, delivery_address, notes)
             VALUES (:order_number, :customer_id, :title, :description, :items, :status, :priority, :delivery_address, :notes)',
            [
                'order_number'     => $data['order_number'],
                'customer_id'      => $data['customer_id'],
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'items'            => json_encode($data['items'], JSON_THROW_ON_ERROR),
                'status'           => $data['status'] ?? 'pending',
                'priority'         => $data['priority'] ?? 'normal',
                'delivery_address' => $data['delivery_address'],
                'notes'            => $data['notes'] ?? null,
            ]
        );

        return (int) $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = [
            'order_number', 'customer_id', 'title', 'description',
            'status', 'priority', 'delivery_address', 'notes',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (isset($data['items']) && is_array($data['items'])) {
            $fields[] = 'items = :items';
            $params['items'] = json_encode($data['items'], JSON_THROW_ON_ERROR);
        }

        if ($fields === []) {
            return false;
        }

        $sql = 'UPDATE orders SET ' . implode(', ', $fields) . ' WHERE id = :id';

        return $this->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM orders WHERE id = :id', ['id' => $id]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        return $this->execute(
            'UPDATE orders SET status = :status WHERE id = :id',
            ['id' => $id, 'status' => $status]
        );
    }

    public function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $prefix = "ORD-{$date}-";

        $last = $this->fetchOne(
            'SELECT order_number FROM orders WHERE order_number LIKE :prefix ORDER BY id DESC LIMIT 1',
            ['prefix' => $prefix . '%']
        );

        $next = 1;

        if ($last && isset($last['order_number'])) {
            $next = (int) substr((string) $last['order_number'], -4) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, string>
     */
    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending'   => 'secondary',
            'confirmed' => 'info',
            'shopping'  => 'warning',
            'ready'     => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    /**
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    private function decodeItems(array $order): array
    {
        if (isset($order['items']) && is_string($order['items'])) {
            $order['items'] = json_decode($order['items'], true, 512, JSON_THROW_ON_ERROR);
        }

        return $order;
    }
}
