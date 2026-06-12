<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'customer_id', 'sale_id',
        'due_date', 'amount', 'paid_amount', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date:Y-m-d',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->paid_amount < $this->amount;
    }

    public function remaining(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }
}
