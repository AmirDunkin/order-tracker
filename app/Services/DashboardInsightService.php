<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

class DashboardInsightService
{
    /**
     * @return array{text: string, type: string}|null
     */
    public function forShopper(): ?array
    {
        $orderModel = new Order();
        $stats = $orderModel->getDashboardStats();

        $urgentPending = $stats['urgent_pending'];
        $pending       = $stats['pending'];
        $inProgress    = $stats['in_progress'];
        $deliveredToday = $stats['delivered_today'];

        if ($urgentPending > 0) {
            $label = $urgentPending === 1 ? 'order' : 'orders';

            return [
                'text' => "{$urgentPending} urgent {$label} pending — recommend prioritizing these first.",
                'type' => 'warning',
            ];
        }

        if ($pending >= 3) {
            return [
                'text' => "{$pending} orders awaiting confirmation — review the pending queue to keep customers informed.",
                'type' => 'info',
            ];
        }

        if ($pending > 0 && $inProgress === 0) {
            $label = $pending === 1 ? 'order is' : 'orders are';

            return [
                'text' => "{$pending} pending {$label} ready to be confirmed — no orders currently in progress.",
                'type' => 'info',
            ];
        }

        if ($inProgress >= 5) {
            return [
                'text' => "{$inProgress} orders in progress — focus on completing ready orders before accepting new ones.",
                'type' => 'primary',
            ];
        }

        if ($deliveredToday > 0 && $pending === 0) {
            $label = $deliveredToday === 1 ? 'order' : 'orders';

            return [
                'text' => "Great work — {$deliveredToday} {$label} delivered today and no pending backlog.",
                'type' => 'success',
            ];
        }

        if ($stats['total'] === 0) {
            return [
                'text' => 'No orders in the system yet — new customer orders will appear here.',
                'type' => 'secondary',
            ];
        }

        return null;
    }
}
