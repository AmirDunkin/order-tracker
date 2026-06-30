<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

class NotificationService
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Notify the customer when their order status changes.
     */
    public function orderStatusChanged(int $orderId, string $newStatus, ?string $note = null): void
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order || empty($order['customer_email'])) {
            return;
        }

        $orderNumber = (string) $order['order_number'];
        $title       = (string) $order['title'];
        $statusLabel = Order::statusLabel($newStatus);
        $customerMsg = Order::statusCustomerMessage($newStatus);
        $appName     = (string) ($this->config['app']['name'] ?? 'OrderTrack');
        $orderUrl    = rtrim((string) ($this->config['app']['url'] ?? ''), '/') . '/customer/orders/' . $orderId;

        $subject = "{$appName}: Order {$orderNumber} is now {$statusLabel}";

        $body = "Hello {$order['customer_name']},\n\n";
        $body .= "Your order \"{$title}\" ({$orderNumber}) has been updated.\n\n";
        $body .= "New status: {$statusLabel}\n";
        $body .= "{$customerMsg}\n";

        if ($note !== null && trim($note) !== '') {
            $body .= "\nNote from your shopper:\n" . trim($note) . "\n";
        }

        $body .= "\nView your order: {$orderUrl}\n\n";
        $body .= "— {$appName}\n";

        $this->send((string) $order['customer_email'], $subject, $body);
    }

    private function send(string $to, string $subject, string $body): void
    {
        $mail = $this->config['mail'] ?? [];
        $enabled = filter_var($mail['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if (!$enabled) {
            return;
        }

        $fromEmail = (string) ($mail['from_address'] ?? 'noreply@ordertrack.local');
        $fromName  = (string) ($mail['from_name'] ?? 'OrderTrack');
        $logOnly   = filter_var($mail['log_only'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($logOnly) {
            $this->logMail($to, $subject, $body);
            return;
        }

        $headers = [
            'From: ' . $this->formatAddress($fromEmail, $fromName),
            'Reply-To: ' . $fromEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: OrderTrack',
        ];

        @mail($to, $this->encodeSubject($subject), $body, implode("\r\n", $headers));
    }

    private function logMail(string $to, string $subject, string $body): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            str_repeat('-', 40),
            $body
        );

        @file_put_contents($logDir . '/mail.log', $entry, FILE_APPEND | LOCK_EX);
    }

    private function formatAddress(string $email, string $name): string
    {
        $safeName = str_replace(['"', "\r", "\n"], '', $name);

        return $safeName !== '' ? "\"{$safeName}\" <{$email}>" : $email;
    }

    private function encodeSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
}
