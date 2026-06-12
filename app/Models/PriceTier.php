<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceTier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'product_id', 'category_id', 'customer_category_id',
        'customer_id', 'store_id',
        'min_quantity', 'max_quantity', 'price', 'price_label',
        'priority', 'is_active', 'start_date', 'end_date',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'decimal:2',
            'max_quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function customerCategory()
    {
        return $this->belongsTo(CustomerCategory::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
