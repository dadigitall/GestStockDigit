<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'name', 'reference', 'barcode',
        'supplier_id', 'description', 'image', 'unit_sale', 'unit_purchase',
        'purchase_price', 'sale_price', 'wholesale_price', 'promo_price',
        'tax_rate', 'min_stock', 'max_stock', 'alert_threshold',
        'is_active', 'is_sellable', 'is_stockable', 'track_lot',
        'track_serial', 'track_expiry', 'weight', 'volume', 'dimensions', 'brand',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'product_store')
            ->withPivot('min_stock', 'max_stock', 'is_sellable', 'is_active')
            ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
