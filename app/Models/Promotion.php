<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'type', 'description',
        'discount_value', 'discount_type',
        'min_purchase', 'min_quantity', 'max_quantity',
        'buy_quantity', 'get_quantity',
        'is_active', 'starts_at', 'ends_at', 'priority', 'conditions',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'buy_quantity' => 'integer',
            'get_quantity' => 'integer',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'conditions' => 'array',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'promotion_customer');
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'promotion_store');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public static function types(): array
    {
        return [
            'barred_price' => 'Prix barré',
            'period' => 'Promotion période',
            'bundle' => 'Lot de produits',
            'free_product' => 'Produit offert',
            'buy_x_get_y' => 'Achat X, obtenir Y',
            'qty_discount' => 'Réduction par quantité',
            'coupon' => 'Coupon / Code promo',
        ];
    }
}
