<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'type', 'phone', 'email', 'address',
        'tax_number', 'credit_limit', 'payment_terms',
        'balance', 'is_active', 'notes',
    ];
}
