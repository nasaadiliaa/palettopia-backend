<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        $this->url = config('services.gemini.url') ?: env('GEMINI_API_URL', '');
        $this->key = config('services.gemini.key') ?: env('GEMINI_API_KEY', '');
    }

    /**
     * Call the LLM provider with prompt and try parse JSON response.
     * IMPORTANT: adapt request structure to your LLM provider's API.
     */
    public function callLLM(string $prompt): ?array
    {
        try {
            $resp = Http::withToken($this->key)
                ->post($this->url, [
                    // Adapt keys according to provider
                    'model' => 'gemini-pro',
                    'input' => ['text' => $prompt],
                    'max_output_tokens' => 800,
                ]);

            if (! $resp->ok()) {
                Log::error('LLM request failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                return null;
            }

            $body = $resp->json();

            // Attempt to find textual content in common fields - adapt for provider
            $text = $body['output'][0]['content'] ?? $body['choices'][0]['text'] ?? ($body['text'] ?? null);

            if (! $text) {
                Log::warning('LLM returned unexpected shape', ['body' => $body]);
                return null;
            }

            // Try JSON decode the text
            $parsed = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }

            // fallback: extract JSON substring
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $maybe = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $maybe;
                }
            }

            return ['raw_text' => $text];
        } catch (\Throwable $e) {
            Log::error('LLM call error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }
}
