<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class OrderStatusLog extends Model
{
    /**
     * @return array<string, mixed>|false
     */
    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT l.*, u.name AS changed_by_name
             FROM order_status_logs l
             JOIN users u ON u.id = l.changed_by
             WHERE l.id = :id',
            ['id' => $id]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT l.*, u.name AS changed_by_name
             FROM order_status_logs l
             JOIN users u ON u.id = l.changed_by
             WHERE l.order_id = :order_id
             ORDER BY l.created_at ASC',
            ['order_id' => $orderId]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        return $this->fetchAll(
            'SELECT l.*, u.name AS changed_by_name, o.order_number
             FROM order_status_logs l
             JOIN users u ON u.id = l.changed_by
             JOIN orders o ON o.id = l.order_id
             ORDER BY l.created_at DESC
             LIMIT ' . $limit . ' OFFSET ' . $offset
        );
    }

    /**
     * @param array{
     *   order_id: int,
     *   changed_by: int,
     *   old_status?: string|null,
     *   new_status: string,
     *   note?: string|null
     * } $data
     */
    public function create(array $data): int
    {
        $id = $this->lastInsertId(
            'INSERT INTO order_status_logs (order_id, changed_by, old_status, new_status, note)
             VALUES (:order_id, :changed_by, :old_status, :new_status, :note)',
            [
                'order_id'   => $data['order_id'],
                'changed_by' => $data['changed_by'],
                'old_status' => $data['old_status'] ?? null,
                'new_status' => $data['new_status'],
                'note'       => $data['note'] ?? null,
            ]
        );

        return (int) $id;
    }

    /**
     * Log a status change and update the order in one operation.
     *
     * @return bool True on success
     */
    public function logStatusChange(int $orderId, int $changedBy, ?string $oldStatus, string $newStatus, ?string $note = null): bool
    {
        $this->db->beginTransaction();

        try {
            $orderModel = new Order();

            if (!$orderModel->updateStatus($orderId, $newStatus)) {
                $this->db->rollBack();
                return false;
            }

            $this->create([
                'order_id'   => $orderId,
                'changed_by' => $changedBy,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note'       => $note,
            ]);

            $this->db->commit();

            return true;
        } catch (\Throwable) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM order_status_logs WHERE id = :id', ['id' => $id]);
    }
}
