<?php

namespace App\Http\Controllers;

use App\Models\Inventory;

class InventoryPrintController extends Controller
{
    public function print(Inventory $inventory)
    {
        abort_if($inventory->company_id !== auth()->user()->company_id, 403);

        $inventory->load(['items.product', 'items.store', 'items.lot', 'items.counter', 'items.decider', 'creator', 'validator', 'store', 'category']);

        return view('inventories.print', compact('inventory'));
    }
}
