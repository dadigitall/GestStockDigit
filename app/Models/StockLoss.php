<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLoss extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'store_id', 'user_id', 'product_id',
        'reference', 'loss_type', 'quantity', 'unit_price', 'total_value',
        'reason', 'justification', 'status',
        'approved_by', 'approved_at', 'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateReference(): string
    {
        $prefix = 'PERTE-'.date('Ymd');
        $last = static::where('reference', 'like', "{$prefix}-%")
            ->orderBy('reference', 'desc')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return "{$prefix}-".str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function lossTypes(): array
    {
        return [
            'unknown_loss' => 'Perte inconnue',
            'theft' => 'Vol',
            'breakage' => 'Casse',
            'expired' => 'Expiration',
            'damaged' => 'Produit endommagé',
            'internal_consumption' => 'Consommation interne',
            'sample' => 'Échantillon',
            'donation' => 'Don',
        ];
    }
}
