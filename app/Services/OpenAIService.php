<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiKey;
    protected string $model;
    protected string $modelHaiku;

    public function __construct()
    {
        // Using Claude API Key
        $this->apiKey = env('CLAUDE_API_KEY', env('OPENAI_API_KEY')); 
        $this->model = 'claude-sonnet-5';
        $this->modelHaiku = 'claude-sonnet-5';
    }

    private function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ];
    }

    private function cleanJsonResponse(?string $text): ?array
    {
        if (empty($text)) {
            return null;
        }
        $text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', trim($text));
        return json_decode($text, true);
    }

    /**
     * Analyze a receipt image and extract transaction data.
     */
    public function parseReceipt(string $base64Image, array $availableCategories): ?array
    {
        try {
            $systemPrompt = "You are an AI assistant that extracts transaction details from receipt images. 
Available categories: " . implode(', ', $availableCategories) . ". 
Return ONLY a JSON object with the following keys:
- 'amount': (integer) total amount, remove decimals or currency symbols.
- 'date': (string) date in YYYY-MM-DD format.
- 'description': (string) short description or merchant name (max 100 chars).
- 'category': (string) choose the closest category from the available list, or leave empty if unknown.
Do not wrap the JSON in markdown code blocks, just return raw JSON.";

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $this->model,
                    'max_tokens' => 1024,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => 'image/jpeg',
                                        'data' => $base64Image,
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'Extract the receipt information.'
                                ]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $text = $response->json('content.0.text');
                if (is_string($text)) {
                    return $this->cleanJsonResponse($text);
                }
            }

            Log::error('Claude Parse Receipt Failed', ['response' => $response->body()]);
            $errorMsg = $response->json('error.message') ?? 'Terjadi kesalahan pada layanan AI Claude.';
            throw new \Exception("API Error: " . $errorMsg);
        } catch (\Exception $e) {
            Log::error('Claude Parse Receipt Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate financial advice based on family limits and current expenses.
     */
    public function generateAdvisor(array $data): array
    {
        try {
            $systemPrompt = "You are a smart financial advisor for a family. Analyze the following data:
- Income: Rp " . number_format($data['income'], 0, ',', '.') . "
- Expense: Rp " . number_format($data['expense'], 0, ',', '.') . "
- Balance: Rp " . number_format($data['balance'], 0, ',', '.') . "
- Family Limit: Rp " . number_format($data['family_limit'], 0, ',', '.') . " (Used: {$data['family_pct']}%)
- Top Expenses: " . json_encode($data['top_expenses']) . "

Provide two responses in a JSON object:
1. 'owner_advice': Specific advice including the actual amounts and specific warnings. (max 3 sentences in Indonesian)
2. 'member_advice': General advice without revealing exact total income or exact balance, encouraging good habits. (max 2 sentences in Indonesian)

Return ONLY raw JSON without markdown formatting.";

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(20)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $this->modelHaiku,
                    'max_tokens' => 500,
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Please generate the advice JSON.']
                    ]
                ]);

            if ($response->successful()) {
                $text = $response->json('content.0.text');
                if (is_string($text)) {
                    $decoded = $this->cleanJsonResponse($text);
                    
                    return $decoded ?? [
                        'owner_advice' => 'Tidak dapat menganalisis data keuangan saat ini.',
                        'member_advice' => 'Mari berhemat dan kelola pengeluaran dengan bijak.'
                    ];
                } else {
                    Log::error('Claude API returned 200 OK but text is null', ['response' => $response->body()]);
                }
            }

            return [
                'owner_advice' => 'Gagal memuat saran AI.',
                'member_advice' => 'Gagal memuat saran AI.'
            ];
        } catch (\Exception $e) {
            Log::error('Claude Advisor Exception: ' . $e->getMessage());
            return [
                'owner_advice' => 'Terjadi kesalahan sistem AI.',
                'member_advice' => 'Terjadi kesalahan sistem AI.'
            ];
        }
    }
}
