<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::where('company_id', $request->user()->company_id)
            ->with('stores')
            ->get(['id', 'name', 'reference', 'barcode', 'stock_quantity', 'reserved_stock', 'damaged_stock', 'blocked_stock', 'transit_stock', 'min_stock', 'max_stock']);

        return response()->json([
            'data' => $products->map(fn ($p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'reference' => $p->reference,
                'barcode' => $p->barcode,
                'total_stock' => (float) $p->stock_quantity,
                'reserved' => (float) $p->reserved_stock,
                'available' => (float) ($p->stock_quantity - $p->reserved_stock),
                'min_stock' => (float) $p->min_stock,
                'max_stock' => (float) $p->max_stock,
                'per_store' => $p->stores->map(fn ($s) => [
                    'store_id' => $s->id,
                    'store_name' => $s->name,
                    'quantity' => (float) $s->pivot->stock_quantity,
                    'reserved' => (float) $s->pivot->reserved_stock,
                ]),
            ]),
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric|min:0',
            'products.*.store_id' => 'nullable|exists:stores,id',
        ]);

        $companyId = $request->user()->company_id;
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($request->products as $item) {
                $product = Product::where('company_id', $companyId)
                    ->findOrFail($item['product_id']);

                $storeId = $item['store_id'] ?? $request->user()->store_id;

                if ($storeId && $product->stores()->where('store_id', $storeId)->exists()) {
                    $product->stores()->updateExistingPivot($storeId, [
                        'stock_quantity' => (float) $item['quantity'],
                    ]);
                }

                $product->timestamps = false;
                $product->updateQuietly(['stock_quantity' => (float) $item['quantity']]);

                $results[] = [
                    'product_id' => $product->id,
                    'reference' => $product->reference,
                    'new_quantity' => (float) $item['quantity'],
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'products' => ['Échec de la synchronisation : ' . $e->getMessage()],
            ]);
        }

        return response()->json([
            'message' => 'Stock synchronisé avec succès.',
            'synced_at' => now()->toIso8601String(),
            'products_updated' => count($results),
            'data' => $results,
        ]);
    }
}
