<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_category_id', 'name', 'type', 'phone', 'email',
        'address', 'tax_number', 'credit_limit', 'payment_terms',
        'commercial_terms', 'balance', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'particular' => 'Particulier',
            'professional' => 'Professionnel',
            'reseller' => 'Revendeur',
            'wholesaler' => 'Grossiste',
            default => $this->type,
        };
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function outstandingBalance(): float
    {
        return max(0, $this->balance - $this->totalPaid());
    }

    public function isOverdue(): bool
    {
        return $this->paymentSchedules()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->exists();
    }

    public function isCreditBlocked(): bool
    {
        if (! $this->credit_limit) {
            return false;
        }

        return $this->balance >= $this->credit_limit;
    }

    public function wouldExceedCredit(float $amount): bool
    {
        if (! $this->credit_limit) {
            return false;
        }

        return ($this->balance + $amount) > $this->credit_limit;
    }
}
