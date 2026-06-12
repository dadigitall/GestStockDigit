<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_id', 'store_id', 'user_id', 'reference',
        'type', 'status', 'quotation_id', 'sale_id',
        'subtotal', 'tax_amount', 'discount', 'total',
        'paid_amount', 'amount_due',
        'issue_date', 'due_date', 'payment_terms', 'notes', 'paid_at',
        'emecf_invoice_id', 'emecf_uid', 'emecf_code', 'emecf_qr_code',
        'emecf_status', 'emecf_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'emecf_sent_at' => 'datetime',
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function emecfInvoice()
    {
        return $this->belongsTo(\Codianselme\LaraSygmef\Models\EmecfInvoice::class, 'emecf_invoice_id');
    }

    public function isEmecfSynced(): bool
    {
        return $this->emecf_status === 'confirmed';
    }

    public function getEmecfUrlAttribute(): ?string
    {
        if ($this->emecf_uid) {
            return url("/emecf/invoices/{$this->emecf_invoice_id}");
        }
        return null;
    }

    public static function generateReference(): string
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $prefix = $company->invoice_prefix ?? 'FAC';
        $year = now()->format('Y');
        $last = self::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->max('reference');

        $num = $last ? (int) substr($last, -6) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $num);
    }
}
