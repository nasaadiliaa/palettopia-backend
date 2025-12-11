<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * GET /api/recommendations?palette=Autumn&limit=8
     */
    public function byPalette(Request $request)
    {
        // validasi palette wajib diisi
        $data = $request->validate([
            'palette' => 'required|string|max:100',
            'limit'   => 'nullable|integer|min:1|max:50',
        ]);

        $limit   = $data['limit'] ?? 8; // default 8 rekomendasi
        $palette = $data['palette'];

        // query produk berdasarkan palette_category
        $query = Product::query()
            ->where('palette_category', $palette)
            ->orderByDesc('created_at');

        // kalau mau, bisa tambahin filter brand/price nanti di sini

        $products = $query->take($limit)->get();

        return response()->json([
            'palette'      => $palette,
            'total'        => $products->count(),
            'recommendations' => $products,
        ]);
    }
}
