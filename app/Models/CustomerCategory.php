<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'slug', 'color', 'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
