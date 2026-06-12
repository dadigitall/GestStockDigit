<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bundle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'description',
        'bundle_price', 'savings',
        'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'bundle_price' => 'decimal:2',
            'savings' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(BundleItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_items')
            ->withPivot('quantity');
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
}
