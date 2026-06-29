<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AiSuggestionService;
use Core\Controller;

class ApiController extends Controller
{
    public function aiSuggest(): void
    {
        $this->requireRole('shopper');

        $input = $this->jsonInput();

        if ($input === [] && $_POST !== []) {
            $input = $_POST;

            if (isset($input['items']) && is_string($input['items'])) {
                $decoded = json_decode($input['items'], true);
                $input['items'] = is_array($decoded) ? $decoded : [];
            }
        }

        $items            = $input['items'] ?? [];
        $priority         = trim((string) ($input['priority'] ?? 'normal'));
        $deliveryAddress  = trim((string) ($input['delivery_address'] ?? ''));

        if (!is_array($items) || $items === []) {
            $this->json(['success' => false, 'message' => 'Order items are required.'], 422);
        }

        if (!in_array($priority, ['normal', 'urgent'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid priority value.'], 422);
        }

        if ($deliveryAddress === '') {
            $this->json(['success' => false, 'message' => 'Delivery address is required.'], 422);
        }

        $normalizedItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalizedItems[] = [
                'name' => $name,
                'qty'  => max(1, (int) ($item['qty'] ?? 1)),
            ];
        }

        if ($normalizedItems === []) {
            $this->json(['success' => false, 'message' => 'At least one valid item is required.'], 422);
        }

        $aiConfig = require $this->config['paths']['root'] . '/config/ai.php';
        $service  = new AiSuggestionService($aiConfig);
        $result   = $service->suggest($normalizedItems, $priority, $deliveryAddress);

        $this->json([
            'success'    => true,
            'suggestion' => $result['suggestion'],
            'source'     => $result['source'],
        ]);
    }
}
