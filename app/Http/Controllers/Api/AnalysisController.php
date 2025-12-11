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
     * POST /api/analysis
     * Body: { colors: ['#...'], image_url?: string, notes?: string }
     */
    public function store(Request $request, GeminiService $gemini)
    {
        $data = $request->validate([
            'colors'    => 'required|array|min:3',
            'colors.*'  => 'string',
            'notes'     => 'nullable|string',
            'image_url' => 'nullable|string',
        ]);

        $user = $request->user();

        $history = AnalysisHistory::create([
            'user_id' => $user->id,
            'result_palette' => null,
            'input_data' => $request->all(),
            'colors' => $data['colors'],
            'notes' => $data['notes'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ]);

        // Build prompt and call LLM (synchronous)
        $prompt = $this->buildGeminiPrompt($data['colors']);
        $aiResp = $gemini->callLLM($prompt);

        $paletteName = null;
        if (is_array($aiResp)) {
            $paletteName = $aiResp['palette_name'] ?? $aiResp['palette'] ?? null;
        }

        $history->update([
            'result_palette' => $paletteName,
            'ai_result' => $aiResp,
        ]);

        // find product recommendations
        $products = collect();
        $tags = is_array($aiResp) ? ($aiResp['tags'] ?? []) : [];
        if (!empty($tags)) {
            $products = Product::where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', "%{$tag}%");
                }
            })->limit(12)->get();
        } elseif ($paletteName) {
            $products = Product::where('palette', 'like', "%{$paletteName}%")->limit(12)->get();
        }

        return response()->json([
            'message' => 'Analysis saved',
            'history' => $history,
            'ai' => $aiResp,
            'recommendations' => $products,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $histories = AnalysisHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($histories);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $history = AnalysisHistory::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $history) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $history->delete();
        return response()->json(['message' => 'Riwayat berhasil dihapus']);
    }

    public function recommend(Request $request)
    {
        $user = $request->user();
        $latest = AnalysisHistory::where('user_id', $user->id)->latest()->first();
        if (! $latest) {
            return response()->json(['message' => 'Belum ada analisa'], 404);
        }

        $products = Product::where('palette', 'like', "%{$latest->result_palette}%")->limit(12)->get();

        return response()->json([
            'palette' => $latest->result_palette,
            'suggest_products' => $products
        ]);
    }

    protected function buildGeminiPrompt(array $colors): string
    {
        $colorsList = implode('","', array_map(fn($c)=>trim($c), $colors));
        $example = json_encode([
            "palette_name" => "Autumn Warm",
            "explanation" => "Short reasons why this palette suits the person.",
            "recommendation" => [
                ["title" => "Dress Terracotta Elegant", "reason" => "Terracotta suits warm undertones."],
            ],
            "tags" => ["terracotta","warm","earth"]
        ], JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
You are an expert personal-color stylist. Input: an ordered list of HEX color codes (strings) from a user's face photo: ["{$colorsList}"].

Return **JSON ONLY** with keys:
- "palette_name": short string (e.g. "Autumn Warm")
- "explanation": a 2-3 sentence reason why this palette fits the user
- "recommendation": array of up to 3 objects {"title":"", "reason":""}
- "tags": array of keywords (lowercase) to match product DB

Example response (JSON only):
{$example}

Respond with JSON only; no additional text.
PROMPT;
    }
}
