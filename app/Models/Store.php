<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'parent_id', 'address',
        'phone', 'email', 'manager_id', 'opening_hours', 'is_active',
        'allows_stock', 'allows_sales', 'allows_cash_register', 'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_store')
            ->withPivot('stock_quantity', 'reserved_stock', 'damaged_stock', 'blocked_stock', 'min_stock', 'max_stock', 'is_sellable', 'is_active')
            ->withTimestamps();
    }

    public function sellableProducts()
    {
        return $this->belongsToMany(Product::class, 'product_store')
            ->wherePivot('is_sellable', true)
            ->wherePivot('is_active', true)
            ->withPivot('stock_quantity', 'reserved_stock', 'damaged_stock', 'blocked_stock', 'min_stock', 'max_stock')
            ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function rootLocations()
    {
        return $this->hasMany(Location::class)->whereNull('parent_id');
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    public static function fullTree(int $companyId): Collection
    {
        $all = self::where('company_id', $companyId)
            ->with('parent', 'manager')
            ->orderBy('name')
            ->get();

        $grouped = $all->groupBy('parent_id');

        foreach ($all as $store) {
            $store->setRelation('children', $grouped->get($store->id, collect()));
        }

        return $grouped->get(null, collect());
    }
}
