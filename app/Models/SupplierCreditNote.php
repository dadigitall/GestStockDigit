<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'supplier_id', 'supplier_return_id',
        'reference', 'amount', 'reason', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public static function generateReference(): string
    {
        $prefix = 'CN-'.date('Ymd');
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class);
    }
}
