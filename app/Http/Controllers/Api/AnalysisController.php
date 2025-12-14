<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisHistory;
use App\Models\Product;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    /**
     * =========================
     * GET /api/history
     * =========================
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // kalau belum login / session hilang
        if (!$user) {
            return response()->json([], 200);
        }

        $history = AnalysisHistory::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json($history, 200);
    }

    /**
     * =========================
     * POST /api/analysis
     * =========================
     */
    public function store(Request $request, GeminiService $gemini)
    {
        $data = $request->validate([
            'colors'    => 'required|array|min:3',
            'colors.*'  => 'string',
            'image_url' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // === 1. CALL AI ===
        try {
            $prompt = $this->buildGeminiPrompt($data['colors']);
            $raw = $gemini->callLLM($prompt);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'AI service error',
                'error' => $e->getMessage()
            ], 500);
        }

        // === 2. PARSE AI RESPONSE ===
        $ai = is_string($raw) ? json_decode($raw, true) : $raw;

        if (!is_array($ai) || empty($ai['palette_name'])) {
            return response()->json([
                'message' => 'Invalid AI response',
                'raw' => $raw
            ], 422);
        }

        $paletteName = $ai['palette_name'];
        $tags = $ai['tags'] ?? [];

        // === 3. SAVE HISTORY ===
        $history = AnalysisHistory::create([
            'user_id' => $user->id,
            'result_palette' => $paletteName,
            'ai_result' => $ai,
            'input_data' => $data,
            'colors' => $data['colors'],
            'notes' => $data['notes'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ]);

        // === 4. PRODUCT RECOMMENDATION ===
        $products = collect();

        if (!empty($tags)) {
            $products = Product::where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', "%{$tag}%");
                }
            })->limit(12)->get();
        } else {
            $products = Product::where('palette', 'like', "%{$paletteName}%")
                ->limit(12)
                ->get();
        }

        return response()->json([
            'message' => 'Analysis completed',
            'palette' => $paletteName,
            'explanation' => $ai['explanation'] ?? null,
            'recommendation' => $ai['recommendation'] ?? [],
            'history' => $history,
            'products' => $products
        ], 201);
    }

    protected function buildGeminiPrompt(array $colors): string
    {
        $list = implode(', ', $colors);

        return <<<PROMPT
You are a professional personal color analyst.

Input:
HEX skin-tone colors detected from a face image:
{$list}

Task:
Determine the MOST SUITABLE seasonal color palette.

You MUST choose ONLY ONE from:
- Autumn Warm
- Spring Warm
- Summer Cool
- Winter Cool

Rules:
- Analyze undertone (warm vs cool)
- Analyze brightness (light vs dark)
- Analyze contrast

Respond with VALID JSON ONLY in this format:

{
  "palette_name": "",
  "explanation": "",
  "tags": []
}

Do NOT add any text outside JSON.
PROMPT;
    }
}
