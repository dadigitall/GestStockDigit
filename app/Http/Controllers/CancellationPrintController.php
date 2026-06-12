<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Sale;

class CancellationPrintController extends Controller
{
    public function print(Sale $sale)
    {
        abort_if($sale->company_id !== auth()->user()->company_id, 403);
        abort_if($sale->status !== 'cancelled', 404);

        $sale->load(['items.product', 'customer', 'user', 'store']);
        $company = Company::find($sale->company_id);

        return view('sales.cancellation-print', compact('sale', 'company'));
    }
}
