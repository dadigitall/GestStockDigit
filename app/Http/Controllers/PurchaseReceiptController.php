<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use Illuminate\View\View;

class PurchaseReceiptController extends Controller
{
    public function print(GoodsReceipt $goodsReceipt): View
    {
        $receipt = $goodsReceipt->load([
            'company',
            'supplier',
            'purchaseOrder',
            'store',
            'user',
            'items.product',
        ]);

        return view('purchases.receipt-print', compact('receipt'));
    }
}
