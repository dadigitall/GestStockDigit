<?php

namespace App\Http\Controllers;

use App\Models\Quotation;

class QuotationPrintController extends Controller
{
    public function print(Quotation $quotation)
    {
        $quotation->load(['items', 'customer', 'company', 'user']);

        return view('quotations.print', compact('quotation'));
    }
}
