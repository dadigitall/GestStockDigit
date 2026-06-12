<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierEvaluation extends Model
{
    protected $fillable = [
        'supplier_id', 'evaluated_by',
        'respect_delays', 'product_quality', 'return_rate',
        'average_price', 'reliability', 'purchase_volume',
        'overall_rating', 'comment', 'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluated_at' => 'date',
            'respect_delays' => 'integer',
            'product_quality' => 'integer',
            'return_rate' => 'integer',
            'average_price' => 'integer',
            'reliability' => 'integer',
            'purchase_volume' => 'integer',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
