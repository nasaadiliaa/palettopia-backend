<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function callLLM(string $prompt)
    {
        $endpoint = config('services.gemini.url');   
        $apiKey   = config('services.gemini.key');   

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
            $endpoint.`:generateContent?key=`.$apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]
        );

        if (!$response->successful()) {
            \Log::error('Gemini API failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \Exception('Gemini API error: '.$response->status());
        }

        $json = $response->json();

        return $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
}
