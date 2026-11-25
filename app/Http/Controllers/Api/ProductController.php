<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all active products catalog.
     */
    public function index(): JsonResponse
    {
        $products = Product::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Fetch products below a configurable low-stock threshold.
     */
    public function lowStock(Request $request): JsonResponse
    {
        // Default threshold is configurable via config('inventory.low_stock_threshold', 10)
        // or query parameter ?threshold=X
        $defaultThreshold = config('inventory.low_stock_threshold', 10);
        $threshold = (int)$request->input('threshold', $defaultThreshold);

        if ($threshold < 0) {
            $threshold = 0;
        }

        $products = Product::where('stock_on_hand', '<=', $threshold)
            ->orderBy('stock_on_hand', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'threshold' => $threshold,
            'count' => $products->count(),
            'data' => $products,
        ]);
    }
}
