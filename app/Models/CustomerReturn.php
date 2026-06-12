<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CustomerReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'store_id', 'user_id', 'customer_id', 'sale_id',
        'reference', 'return_type', 'reason', 'reason_description',
        'restock', 'refund_method', 'refund_amount', 'margin_impact',
        'exchange_amount', 'exchange_products', 'status', 'notes',
        'approved_by', 'approved_at', 'credit_note_id',
    ];

    protected function casts(): array
    {
        return [
            'restock' => 'boolean',
            'refund_amount' => 'decimal:2',
            'margin_impact' => 'decimal:2',
            'exchange_amount' => 'decimal:2',
            'exchange_products' => 'array',
            'approved_at' => 'datetime',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creditNote()
    {
        return $this->belongsTo(Invoice::class, 'credit_note_id');
    }

    public function getMarginImpactAttribute()
    {
        return $this->items->sum(function ($item) {
            $purchasePrice = $item->product?->purchase_price ?? 0;
            return ($item->unit_price - $purchasePrice) * $item->quantity;
        });
    }

    public function items()
    {
        return $this->hasMany(CustomerReturnItem::class);
    }

    public static function generateReference(): string
    {
        $prefix = 'RCL-'.date('Ymd');
        $last = static::where('reference', 'like', "{$prefix}-%")
            ->orderBy('reference', 'desc')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return "{$prefix}-".str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function processReturn(): void
    {
        DB::transaction(function () {
            foreach ($this->items as $item) {
                if ($item->restock) {
                    $pivot = \DB::table('product_store')
                        ->where('product_id', $item->product_id)
                        ->where('store_id', $this->store_id)
                        ->first();

                    if ($pivot) {
                        \DB::table('product_store')
                            ->where('id', $pivot->id)
                            ->increment('stock_quantity', $item->quantity);
                    }

                    StockMovement::create([
                        'company_id' => $this->company_id,
                        'store_id' => $this->store_id,
                        'product_id' => $item->product_id,
                        'user_id' => $this->user_id,
                        'type' => 'return_in',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'reference' => $this->reference,
                        'notes' => 'Retour client: '.$this->reason,
                        'movement_date' => now(),
                    ]);
                }
            }

            $this->update(['status' => 'completed']);
        });
    }
}
