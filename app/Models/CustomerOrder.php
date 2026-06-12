<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_id', 'store_id', 'user_id', 'reference',
        'status', 'subtotal', 'tax_amount', 'discount', 'total',
        'order_date', 'expected_delivery_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['received', 'preparing', 'ready']);
    }

    public static function generateReference(): string
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $prefix = 'BC';
        $year = now()->format('Y');
        $last = self::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->max('reference');

        $num = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $num);
    }
}
