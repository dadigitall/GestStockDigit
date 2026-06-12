<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'name', 'reference', 'barcode',
        'supplier_id', 'description', 'image', 'unit_sale', 'unit_purchase',
        'purchase_price', 'sale_price', 'wholesale_price', 'reseller_price', 'promo_price',
        'tax_rate', 'min_stock', 'max_stock', 'alert_threshold', 'stock_quantity',
        'reserved_stock', 'damaged_stock', 'blocked_stock', 'transit_stock',
        'is_active', 'is_sellable', 'is_stockable', 'track_lot',
        'track_serial', 'track_expiry', 'weight', 'volume', 'dimensions',
        'brand', 'family', 'packaging',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_stockable' => 'boolean',
            'track_lot' => 'boolean',
            'track_serial' => 'boolean',
            'track_expiry' => 'boolean',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'reseller_price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:2',
            'volume' => 'decimal:2',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'product_store')
            ->withPivot('stock_quantity', 'reserved_stock', 'damaged_stock', 'blocked_stock', 'min_stock', 'max_stock', 'is_sellable', 'is_active')
            ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(PriceTier::class);
    }

    public function getPriceForCustomer(?Customer $customer, float $quantity, ?int $storeId = null): float
    {
        $companyId = $this->company_id;

        $query = PriceTier::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('product_id', $this->id)
                    ->orWhereNull('product_id');
            })
            ->where(function ($q) use ($quantity) {
                $q->where('min_quantity', '<=', $quantity)
                    ->where(function ($q2) use ($quantity) {
                        $q2->where('max_quantity', '>=', $quantity)
                            ->orWhereNull('max_quantity');
                    });
            })
            ->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            })
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now()->format('Y-m-d'));
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->format('Y-m-d'));
            });

        if ($customer) {
            // category_id matching
            $query->where(function ($q) use ($customer) {
                $q->where(function ($q2) use ($customer) {
                    $q2->whereNotNull('category_id')
                        ->whereIn('category_id', function ($sub) use ($customer) {
                            $sub->select('category_id')
                                ->from('product_category')
                                ->where('product_id', $this->id);
                        });
                })->orWhereNull('category_id');
            });

            // customer_category_id matching
            if ($customer->customer_category_id) {
                $query->where(function ($q) use ($customer) {
                    $q->where('customer_category_id', $customer->customer_category_id)
                        ->orWhereNull('customer_category_id');
                });
            } else {
                $query->whereNull('customer_category_id');
            }

            // customer_id matching
            $query->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                    ->orWhereNull('customer_id');
            });
        } else {
            $query->whereNull('category_id')
                ->whereNull('customer_category_id')
                ->whereNull('customer_id');
        }

        $tier = $query->orderByRaw('
                priority DESC,
                CASE WHEN product_id IS NOT NULL THEN 1 ELSE 0 END DESC,
                CASE WHEN category_id IS NOT NULL THEN 1 ELSE 0 END DESC,
                CASE WHEN customer_category_id IS NOT NULL THEN 1 ELSE 0 END DESC,
                CASE WHEN customer_id IS NOT NULL THEN 1 ELSE 0 END DESC,
                CASE WHEN store_id IS NOT NULL THEN 1 ELSE 0 END DESC,
                min_quantity DESC
            ')->first();

        if ($tier) {
            return (float) $tier->price;
        }

        if ($customer) {
            return match ($customer->type) {
                'wholesaler' => (float) ($this->wholesale_price ?? $this->sale_price),
                'reseller' => (float) ($this->reseller_price ?? $this->wholesale_price ?? $this->sale_price),
                'professional' => (float) ($this->wholesale_price ?? $this->sale_price),
                default => (float) $this->sale_price,
            };
        }

        return (float) $this->sale_price;
    }

    public function unitModelSale()
    {
        return Unit::where('company_id', $this->company_id)
            ->where('slug', $this->unit_sale)
            ->first();
    }

    public function unitModelPurchase()
    {
        return Unit::where('company_id', $this->company_id)
            ->where('slug', $this->unit_purchase)
            ->first();
    }

    public function convertToPurchaseQuantity(float $saleQty): float
    {
        if ($this->unit_sale === $this->unit_purchase) {
            return $saleQty;
        }

        $from = $this->unitModelSale();
        $to = $this->unitModelPurchase();

        if (! $from || ! $to) {
            return $saleQty;
        }

        return $from->convert($saleQty, $to);
    }

    public function convertToSaleQuantity(float $purchaseQty): float
    {
        if ($this->unit_sale === $this->unit_purchase) {
            return $purchaseQty;
        }

        $from = $this->unitModelPurchase();
        $to = $this->unitModelSale();

        if (! $from || ! $to) {
            return $purchaseQty;
        }

        return $from->convert($purchaseQty, $to);
    }
}
