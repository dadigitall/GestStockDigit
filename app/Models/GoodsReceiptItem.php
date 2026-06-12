<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'product_id',
        'quantity_ordered', 'quantity_accepted', 'quantity_rejected',
        'lot_number', 'expiry_date', 'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'decimal:2',
            'quantity_accepted' => 'decimal:2',
            'quantity_rejected' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'expiry_date' => 'date:Y-m-d',
        ];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
