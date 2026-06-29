<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class AiSuggestionService
{
    /** @var array<string, string> */
    private array $config;

    /**
     * @param array<string, string> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array<int, array{name?: string, qty?: int}> $items
     * @return array{suggestion: string, source: string}
     */
    public function suggest(array $items, string $priority, string $deliveryAddress): array
    {
        $apiKey = trim($this->config['openai_api_key'] ?? '');

        if ($apiKey !== '') {
            try {
                $suggestion = $this->callOpenAi($items, $priority, $deliveryAddress, $apiKey);

                return [
                    'suggestion' => $suggestion,
                    'source'     => 'openai',
                ];
            } catch (\Throwable) {
                // Fall through to rule-based suggestion
            }
        }

        return [
            'suggestion' => $this->ruleBasedSuggestion($items, $priority, $deliveryAddress),
            'source'     => 'rules',
        ];
    }

    /**
     * @param array<int, array{name?: string, qty?: int}> $items
     */
    private function callOpenAi(array $items, string $priority, string $deliveryAddress, string $apiKey): string
    {
        $itemList = $this->formatItemList($items);

        $prompt = <<<PROMPT
You are a helpful personal shopping assistant for the OrderTrack order tracking system.
Given this order, provide ONE concise shopping tip (2-3 sentences max) covering:
- Priority handling based on urgency level
- Practical collection order or route efficiency
- Any special care for fragile, refrigerated, or pharmacy items if applicable

Order priority: {$priority}
Delivery address: {$deliveryAddress}
Items:
{$itemList}

Respond with only the tip text, no bullet points or headings.
PROMPT;

        $payload = json_encode([
            'model'       => $this->config['openai_model'] ?? 'gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => 'You give brief, actionable shopping tips for personal shoppers.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => 200,
            'temperature' => 0.7,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($this->config['openai_url']);

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize HTTP client.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('OpenAI API request failed.');
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if (!is_string($content) || trim($content) === '') {
            throw new RuntimeException('Empty response from OpenAI.');
        }

        return trim($content);
    }

    /**
     * @param array<int, array{name?: string, qty?: int}> $items
     */
    private function ruleBasedSuggestion(array $items, string $priority, string $deliveryAddress): string
    {
        $itemCount = count($items);
        $totalQty  = array_sum(array_map(static fn (array $i) => (int) ($i['qty'] ?? 1), $items));
        $tips      = [];

        if ($priority === 'urgent') {
            $tips[] = 'This is an URGENT order — prioritize it ahead of normal orders and confirm availability of all items before starting other trips.';
        } else {
            $tips[] = 'Standard priority order — complete within your normal queue unless an urgent order comes in.';
        }

        if ($itemCount >= 5 || $totalQty >= 10) {
            $tips[] = "With {$itemCount} line items ({$totalQty} units total), group items by store aisle to minimise backtracking.";
        } else {
            $tips[] = 'This is a compact order — a single-store run should be efficient.';
        }

        $perishable = $this->detectKeywords($items, ['milk', 'egg', 'bread', 'meat', 'fish', 'frozen', 'ice cream', 'yogurt']);
        if ($perishable) {
            $tips[] = 'Collect refrigerated and perishable items last so they stay fresh during transit.';
        }

        $pharmacy = $this->detectKeywords($items, ['pharmacy', 'prescription', 'vitamin', 'medicine', 'otc']);
        if ($pharmacy) {
            $tips[] = 'Pharmacy items detected — visit the pharmacy counter first and verify any prescription details with the customer name.';
        }

        $addressHint = $this->addressHint($deliveryAddress);
        if ($addressHint !== null) {
            $tips[] = $addressHint;
        }

        return implode(' ', $tips);
    }

    /**
     * @param array<int, array{name?: string, qty?: int}> $items
     * @param list<string> $keywords
     */
    private function detectKeywords(array $items, array $keywords): bool
    {
        foreach ($items as $item) {
            $name = strtolower((string) ($item['name'] ?? ''));

            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

  /**
   * @param array<int, array{name?: string, qty?: int}> $items
   */
    private function formatItemList(array $items): string
    {
        if ($items === []) {
            return '- (no items listed)';
        }

        $lines = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? 'Unknown'));
            $qty  = (int) ($item['qty'] ?? 1);
            $lines[] = "- {$name} x{$qty}";
        }

        return implode("\n", $lines);
    }

    private function addressHint(string $address): ?string
    {
        $lower = strtolower($address);

        if (str_contains($lower, 'suite') || str_contains($lower, 'office') || str_contains($lower, 'commerce')) {
            return 'Office/commercial delivery — confirm building access and reception drop-off procedures.';
        }

        if (str_contains($lower, 'apt') || str_contains($lower, 'apartment')) {
            return 'Apartment delivery — note the unit number and check if buzzer or call-on-arrival is needed.';
        }

        return null;
    }
}
