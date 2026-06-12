<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DeliveryNote;

class DeliveryNotePrintController extends Controller
{
    public function print(DeliveryNote $deliveryNote)
    {
        abort_if($deliveryNote->company_id !== auth()->user()->company_id, 403);

        $deliveryNote->load(['items', 'customer', 'user']);
        $company = Company::find($deliveryNote->company_id);

        return view('delivery-notes.print', compact('deliveryNote', 'company'));
    }
}
