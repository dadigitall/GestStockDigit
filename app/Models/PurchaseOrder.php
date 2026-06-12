<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'supplier_id', 'store_id', 'user_id', 'reference',
        'source', 'purchase_requisition_id', 'status',
        'subtotal', 'tax_amount', 'discount', 'shipping_cost', 'total',
        'payment_terms', 'delivery_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_date' => 'date:Y-m-d',
        ];
    }

    public static function generateReference(): string
    {
        $prefix = 'PO-'.date('Ymd');
        $last = static::where('reference', 'like', "{$prefix}-%")
            ->orderBy('reference', 'desc')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return "{$prefix}-".str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(function ($item) {
            $received = GoodsReceiptItem::where('purchase_order_item_id', $item->id)->sum('quantity_accepted');

            return $received >= $item->quantity;
        });
    }
}
