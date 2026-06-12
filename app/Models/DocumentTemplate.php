<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'type', 'logo', 'colors',
        'header_html', 'footer_html', 'legal_mentions', 'terms',
        'paper_format', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeDefaultFor($query, string $type)
    {
        return $query->where('type', $type)->where('is_default', true)->first()
            ?? $query->where('type', $type)->first();
    }
}
