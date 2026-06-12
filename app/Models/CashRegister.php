<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'store_id', 'user_id', 'name', 'code',
        'status', 'initial_balance', 'current_balance', 'expected_balance',
        'opened_at', 'closed_at', 'opened_by', 'closed_by',
        'closing_note', 'cashier_signature', 'counted_amount', 'difference', 'validated_by', 'validator_signature',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'counted_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function cashiers()
    {
        return $this->belongsToMany(User::class, 'cash_register_user')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

    public function open(float $initialBalance, int $userId): void
    {
        $this->update([
            'status' => 'open',
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'expected_balance' => $initialBalance,
            'opened_at' => now(),
            'opened_by' => $userId,
            'user_id' => $userId,
            'closed_at' => null,
            'closed_by' => null,
            'closing_note' => null,
            'cashier_signature' => null,
            'counted_amount' => null,
            'difference' => null,
            'validated_by' => null,
            'validator_signature' => null,
        ]);

        if ($initialBalance > 0) {
            $this->movements()->create([
                'company_id' => $this->company_id,
                'store_id' => $this->store_id,
                'user_id' => $userId,
                'type' => 'opening_balance',
                'direction' => 'in',
                'amount' => $initialBalance,
                'payment_method' => 'cash',
                'description' => 'Fond de caisse initial',
                'movement_date' => now(),
                'sourceable_type' => self::class,
                'sourceable_id' => $this->id,
            ]);
        }
    }

    public function close(float $countedAmount, ?string $note, ?string $signature, int $userId): void
    {
        $difference = $countedAmount - $this->expected_balance;

        $this->update([
            'status' => 'closed',
            'counted_amount' => $countedAmount,
            'difference' => $difference,
            'closing_note' => $note,
            'cashier_signature' => $signature,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);
    }

    public function validateClosing(?string $signature, int $userId): void
    {
        $this->update([
            'validated_by' => $userId,
            'validator_signature' => $signature,
        ]);
    }

    public function addMovement(array $data): CashMovement
    {
        if ($this->status !== 'open') {
            throw new \RuntimeException('La caisse doit être ouverte pour ajouter un mouvement.');
        }

        $movement = $this->movements()->create([
            'company_id' => $this->company_id,
            'store_id' => $this->store_id,
            'user_id' => $data['user_id'],
            'sourceable_type' => $data['sourceable_type'] ?? CashRegister::class,
            'sourceable_id' => $data['sourceable_id'] ?? $this->id,
            'type' => $data['type'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null,
            'movement_date' => $data['movement_date'] ?? now(),
        ]);

        $balanceChange = $data['direction'] === 'in' ? $data['amount'] : -$data['amount'];
        $this->increment('current_balance', $balanceChange);

        if ($data['direction'] === 'in') {
            $this->increment('expected_balance', $data['amount']);
        } else {
            $this->decrement('expected_balance', $data['amount']);
        }

        return $movement;
    }

    public function shiftMovements()
    {
        $q = $this->movements();

        if ($this->opened_at) {
            $q->where('movement_date', '>=', $this->opened_at);
        }

        if ($this->closed_at) {
            $q->where('movement_date', '<=', $this->closed_at);
        }

        return $q;
    }

    public function closingSummary(): array
    {
        $movements = $this->relationLoaded('movements')
            ? $this->movements
            : $this->shiftMovements()->get();

        if ($this->opened_at && $this->movements->isNotEmpty()) {
            $movements = $movements->filter(fn ($m) => $m->movement_date >= $this->opened_at
                && (! $this->closed_at || $m->movement_date <= $this->closed_at)
            );
        }

        $cashSales = $movements->where('type', 'cash_sale')
            ->where('payment_method', 'cash')->sum('amount');
        $cashInByType = $movements->where('direction', 'in')
            ->groupBy('type')->map(fn ($g) => $g->sum('amount'));
        $outByType = $movements->where('direction', 'out')
            ->groupBy('type')->map(fn ($g) => $g->sum('amount'));

        $mobileMoney = $movements->where('payment_method', 'mobile_money')->sum('amount');
        $card = $movements->where('payment_method', 'card')->sum('amount');
        $credits = $movements->where('payment_method', 'credit')->sum('amount');
        $refunds = $movements->whereIn('type', ['customer_refund'])->sum('amount');
        $expenses = $movements->whereIn('type', ['internal_expense', 'supplier_payment'])->sum('amount');
        $ownerWithdrawals = $movements->where('type', 'owner_withdrawal')->sum('amount');
        $bankDeposits = $movements->where('type', 'bank_deposit')->sum('amount');
        $totalIn = $movements->where('direction', 'in')->sum('amount');
        $totalOut = $movements->where('direction', 'out')->sum('amount');

        return [
            'initial_balance' => (float) $this->initial_balance,
            'cash_sales_cash' => $cashSales,
            'cash_sales_total' => $cashInByType->get('cash_sale', 0),
            'customer_payments' => $cashInByType->get('customer_payment', 0),
            'mobile_money' => $mobileMoney,
            'card' => $card,
            'credits' => $credits,
            'refunds' => $refunds,
            'expenses' => $expenses,
            'owner_withdrawals' => $ownerWithdrawals,
            'bank_deposits' => $bankDeposits,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'expected' => (float) $this->expected_balance,
            'counted' => (float) ($this->counted_amount ?? 0),
            'difference' => (float) ($this->difference ?? 0),
        ];
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
