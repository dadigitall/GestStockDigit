<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'slug', 'type', 'base_unit',
    ];

    protected function casts(): array
    {
        return [
            'base_unit' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function conversionsFrom()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    public function convert(float $quantity, Unit $to): float
    {
        if ($this->id === $to->id) {
            return $quantity;
        }

        $conversion = $this->conversionsFrom()
            ->where('to_unit_id', $to->id)
            ->first();

        if ($conversion) {
            return $quantity * $conversion->factor;
        }

        $reverse = $to->conversionsFrom()
            ->where('to_unit_id', $this->id)
            ->first();

        if ($reverse) {
            return $quantity / $reverse->factor;
        }

        return $quantity;
    }
}
