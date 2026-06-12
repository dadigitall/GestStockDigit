<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;

class InvoicePrintController extends Controller
{
    public function print(Invoice $invoice)
    {
        abort_if($invoice->company_id !== auth()->user()->company_id, 403);

        $invoice->load(['items', 'customer', 'user']);
        $company = Company::find($invoice->company_id);

        return view('invoices.print', compact('invoice', 'company'));
    }
}
