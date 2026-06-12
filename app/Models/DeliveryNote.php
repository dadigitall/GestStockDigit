<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_id', 'store_id', 'user_id', 'reference',
        'status', 'source_type', 'source_id',
        'delivery_date', 'received_date',
        'receiver_name', 'receiver_signature', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'received_date' => 'date',
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

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public static function generateReference(): string
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $prefix = $company->delivery_prefix ?? 'BL';
        $year = now()->format('Y');
        $last = self::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->max('reference');

        $num = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $num);
    }
}
