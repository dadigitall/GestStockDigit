<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'legal_name', 'tax_number', 'registration_number', 'logo',
        'address', 'phone', 'email', 'website', 'currency', 'date_format',
        'timezone', 'locale', 'is_active',
        'invoice_prefix', 'sale_prefix', 'purchase_prefix',
        'delivery_prefix', 'quotation_prefix', 'credit_note_prefix',
        'transfer_prefix', 'invoice_footer', 'invoice_terms',
        'ticket_footer', 'enable_multi_currency', 'secondary_currency',
        'default_tax_rate', 'discount_max_rate', 'credit_limit_default',
        'alert_threshold_global',
    ];

    public static function current(): ?self
    {
        return auth()->user()->company;
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
