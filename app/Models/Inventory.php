<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Inventory extends Model
{
    protected $fillable = [
        'company_id', 'reference', 'title', 'type', 'status',
        'store_id', 'category_id', 'location_id', 'frozen_at', 'freeze_stock',
        'started_at', 'completed_at', 'validated_at', 'validated_by', 'created_by',
        'notes', 'total_items', 'total_discrepancies', 'total_discrepancy_value',
    ];

    protected function casts(): array
    {
        return [
            'frozen_at' => 'datetime',
            'freeze_stock' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($inventory) {
            if (! $inventory->reference) {
                $inventory->reference = 'INV-'.strtoupper(Str::random(8));
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function countedItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class)->whereNotNull('physical_quantity');
    }

    public function discrepantItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class)->where('discrepancy_quantity', '!=', 0);
    }

    public function approvedItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class)->where('decision', 'approved');
    }

    public function rejectedItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class)->where('decision', 'rejected');
    }

    public function scopeForCompany($q, $companyId)
    {
        return $q->where('company_id', $companyId);
    }

    public function scopeActive($q)
    {
        return $q->whereIn('status', ['draft', 'in_progress', 'frozen', 'completed']);
    }

    public function isFrozen(): bool
    {
        return $this->freeze_stock && $this->frozen_at !== null;
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeValidated(): bool
    {
        return $this->status === 'completed';
    }
}
