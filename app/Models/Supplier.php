<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'type', 'address', 'phone', 'email',
        'contact_name', 'payment_terms', 'delivery_delay_days', 'currency',
        'is_active', 'notes', 'balance',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function evaluations()
    {
        return $this->hasMany(SupplierEvaluation::class);
    }

    public function latestEvaluation()
    {
        return $this->hasOne(SupplierEvaluation::class)->latestOfMany();
    }

    public function creditNotes()
    {
        return $this->hasMany(SupplierCreditNote::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
