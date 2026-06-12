<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'inventory_id', 'product_id', 'store_id', 'lot_id',
        'theoretical_quantity', 'physical_quantity',
        'discrepancy_quantity', 'discrepancy_value', 'unit_cost',
        'status', 'decision', 'justification', 'counted_by',
        'decided_by', 'decided_at', 'counted_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'counted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function hasDiscrepancy(): bool
    {
        return abs($this->discrepancy_quantity ?? 0) > 0;
    }
}
