<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'store_id', 'user_id', 'customer_id', 'reference',
        'type', 'status', 'subtotal', 'tax_amount', 'discount', 'total',
        'paid_amount', 'change_amount', 'payment_method',
        'payment_method_secondary', 'payment_secondary_amount',
        'notes', 'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public static function generateReference(): string
    {
        $prefix = 'VENTE';
        $year = now()->format('Y');
        $last = self::whereYear('created_at', now()->year)
            ->max('reference');

        $num = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $num);
    }
}
