<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('company_id', $request->user()->company_id)
            ->with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('reference', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)
            ->with(['category', 'supplier', 'stores'])
            ->findOrFail($id);

        return response()->json(new ProductResource($product));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100|unique:products,reference,NULL,id,company_id,' . $request->user()->company_id,
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit_sale' => 'nullable|string|max:50',
            'unit_purchase' => 'nullable|string|max:50',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_stockable' => 'boolean',
            'brand' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
        ]);

        $data['company_id'] = $request->user()->company_id;

        if (empty($data['reference'])) {
            $data['reference'] = 'PROD-' . strtoupper(uniqid());
        }

        $product = Product::create($data);

        return response()->json(new ProductResource($product), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'reference' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('products', 'reference')->where('company_id', $request->user()->company_id)->ignore($product->id),
            ],
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit_sale' => 'nullable|string|max:50',
            'unit_purchase' => 'nullable|string|max:50',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_stockable' => 'boolean',
            'brand' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
        ]);

        $product->update($data);

        return response()->json(new ProductResource($product->load(['category', 'supplier'])));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)->findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produit supprimé.']);
    }

    public function stock(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)
            ->with('stores')
            ->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'reference' => $product->reference,
            'total_stock' => (float) $product->stock_quantity,
            'reserved' => (float) $product->reserved_stock,
            'damaged' => (float) $product->damaged_stock,
            'blocked' => (float) $product->blocked_stock,
            'transit' => (float) $product->transit_stock,
            'available' => (float) ($product->stock_quantity - $product->reserved_stock),
            'per_store' => $product->stores->map(fn ($store) => [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'stock_quantity' => (float) $store->pivot->stock_quantity,
                'reserved' => (float) $store->pivot->reserved_stock,
            ]),
        ]);
    }
}
