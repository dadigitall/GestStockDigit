<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bundle_id', 'product_id', 'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
