<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_id', 'store_id', 'user_id', 'reference',
        'status', 'subtotal', 'tax_amount', 'discount', 'total',
        'validity_date', 'commercial_terms', 'notes',
        'converted_to_invoice_id', 'sent_at', 'accepted_at', 'refused_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'validity_date' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'refused_at' => 'datetime',
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
        return $this->hasMany(QuotationItem::class);
    }

    public function convertedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    public static function generateReference(): string
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $prefix = $company->quotation_prefix ?? 'DEV';
        $year = now()->format('Y');
        $last = self::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->max('reference');

        $num = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $num);
    }
}
