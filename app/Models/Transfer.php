<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'reference', 'title',
        'source_store_id', 'destination_store_id',
        'status',
        'requested_by', 'approved_by', 'prepared_by', 'shipped_by', 'received_by',
        'requested_at', 'approved_at', 'prepared_at', 'shipped_at', 'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'prepared_at' => 'datetime',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($transfer) {
            if (! $transfer->reference) {
                $prefix = optional($transfer->company)->transfer_prefix ?? 'TR';
                $count = static::where('company_id', $transfer->company_id)
                    ->whereYear('created_at', now()->year)
                    ->count() + 1;
                $transfer->reference = strtoupper($prefix).'-'.now()->year.'-'.str_pad($count, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    public function destinationStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'destination_store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'requested', 'approved', 'prepared']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['fully_received', 'rejected', 'cancelled']);
    }
}
