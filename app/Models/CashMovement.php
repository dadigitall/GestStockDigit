<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'cash_register_id', 'store_id', 'user_id',
        'sourceable_type', 'sourceable_id',
        'type', 'direction', 'amount', 'payment_method',
        'reference', 'description', 'movement_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'movement_date' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceable()
    {
        return $this->morphTo();
    }

    public function scopeByRegister($query, int $registerId)
    {
        return $query->where('cash_register_id', $registerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('movement_date', today());
    }
}
