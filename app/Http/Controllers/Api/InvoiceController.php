<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()
            ->where('company_id', $request->user()->company_id)
            ->with(['customer', 'store']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('issue_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('issue_date', '<=', $request->to);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => InvoiceResource::collection($invoices),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('company_id', $request->user()->company_id)
            ->with(['customer', 'store', 'items'])
            ->findOrFail($id);

        return response()->json(new InvoiceResource($invoice));
    }
}
