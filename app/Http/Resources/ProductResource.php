<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),
            'unit_sale' => $this->unit_sale,
            'unit_purchase' => $this->unit_purchase,
            'purchase_price' => (float) $this->purchase_price,
            'sale_price' => (float) $this->sale_price,
            'wholesale_price' => (float) $this->wholesale_price,
            'reseller_price' => (float) $this->reseller_price,
            'promo_price' => (float) $this->promo_price,
            'tax_rate' => (float) $this->tax_rate,
            'stock_quantity' => (float) $this->stock_quantity,
            'reserved_stock' => (float) $this->reserved_stock,
            'damaged_stock' => (float) $this->damaged_stock,
            'blocked_stock' => (float) $this->blocked_stock,
            'transit_stock' => (float) $this->transit_stock,
            'min_stock' => (float) $this->min_stock,
            'max_stock' => (float) $this->max_stock,
            'alert_threshold' => (float) $this->alert_threshold,
            'is_active' => $this->is_active,
            'is_sellable' => $this->is_sellable,
            'is_stockable' => $this->is_stockable,
            'track_lot' => $this->track_lot,
            'track_serial' => $this->track_serial,
            'track_expiry' => $this->track_expiry,
            'brand' => $this->brand,
            'family' => $this->family,
            'weight' => (float) $this->weight,
            'volume' => (float) $this->volume,
            'dimensions' => $this->dimensions,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
