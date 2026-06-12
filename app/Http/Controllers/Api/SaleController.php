<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query()
            ->where('company_id', $request->user()->company_id)
            ->with(['customer', 'store', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $sales = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => SaleResource::collection($sales),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sale = Sale::where('company_id', $request->user()->company_id)
            ->with(['customer', 'store', 'user', 'items'])
            ->findOrFail($id);

        return response()->json(new SaleResource($sale));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'customer_id' => 'nullable|exists:customers,id',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $companyId = $request->user()->company_id;

        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))
            ->where('company_id', $companyId)
            ->get()
            ->keyBy('id');

        $itemsData = [];
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        DB::beginTransaction();
        try {
            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.*.product_id" => "Le produit #{$item['product_id']} n'existe pas dans cette compagnie.",
                    ]);
                }

                $unitPrice = (float) $item['unit_price'];
                $qty = (float) $item['quantity'];
                $discount = (float) ($item['discount'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? $product->tax_rate ?? 0);
                $lineSubtotal = $unitPrice * $qty - $discount;
                $lineTax = $lineSubtotal * ($taxRate / 100);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_reference' => $product->reference,
                    'unit' => $product->unit_sale ?? 'pc',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'subtotal' => $lineSubtotal + $lineTax,
                ];

                $subtotal += $unitPrice * $qty;
                $totalDiscount += $discount;
                $totalTax += $lineTax;
            }

            $total = $subtotal - $totalDiscount + $totalTax;

            $sale = Sale::create([
                'company_id' => $companyId,
                'store_id' => $data['store_id'],
                'user_id' => $request->user()->id,
                'customer_id' => $data['customer_id'] ?? null,
                'reference' => Sale::generateReference(),
                'type' => $data['type'] ?? 'standard',
                'status' => $data['status'] ?? 'completed',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount' => $totalDiscount,
                'total' => $total,
                'paid_amount' => $total,
                'change_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'sold_at' => now(),
            ]);

            $sale->items()->createMany($itemsData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $sale->load(['customer', 'store', 'user', 'items']);

        return response()->json(new SaleResource($sale), 201);
    }
}
