<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderItem extends Model
{
    protected $fillable = [
        'customer_order_id', 'product_id', 'product_name', 'product_reference',
        'unit', 'quantity', 'quantity_prepared', 'quantity_delivered',
        'unit_price', 'discount', 'tax_rate', 'subtotal',
    ];

    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
