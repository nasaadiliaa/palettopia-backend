<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function callLLM(string $prompt)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . config('services.gemini.key'),
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]
        );

        if (!$response->successful()) {
            throw new \Exception('Gemini API error');
        }

        $json = $response->json();

        // ✅ AMBIL TEXT YANG BENAR
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
}
