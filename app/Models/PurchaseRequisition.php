<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'store_id', 'requested_by', 'reference',
        'priority', 'justification', 'desired_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'desired_date' => 'date:Y-m-d',
        ];
    }

    public static function generateReference(): string
    {
        $prefix = 'PR-'.date('Ymd');
        $last = static::where('reference', 'like', "{$prefix}-%")
            ->orderBy('reference', 'desc')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return "{$prefix}-".str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
