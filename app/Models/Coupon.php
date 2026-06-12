<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'promotion_id', 'code',
        'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'usage_per_customer', 'times_used',
        'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_per_customer' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('usage_limit', 0)->orWhere('times_used', '<', \DB::raw('usage_limit'));
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit > 0 && $this->times_used >= $this->usage_limit) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        return true;
    }

    public static function generateCode(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
