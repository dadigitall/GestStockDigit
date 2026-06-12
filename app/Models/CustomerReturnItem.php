<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_return_id', 'product_id', 'sale_item_id',
        'quantity', 'unit_price', 'total',
        'product_condition', 'restock', 'refund_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'restock' => 'boolean',
        ];
    }

    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
