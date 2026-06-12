<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerialNumber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'product_id', 'customer_id', 'serial_number',
        'status', 'entry_date', 'sold_at', 'warranty_expiry',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'sold_at' => 'date',
            'warranty_expiry' => 'date',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
