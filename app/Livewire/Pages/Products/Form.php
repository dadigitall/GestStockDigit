<?php

namespace App\Livewire\Pages\Products;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $image;

    public $existingImage;

    public $name;

    public $reference;

    public $barcode;

    public $category_id;

    public $supplier_id;

    public $description;

    public $purchase_price = 0;

    public $sale_price = 0;

    public $wholesale_price;

    public $unit_sale = 'piece';

    public $unit_purchase = 'piece';

    public $min_stock = 0;

    public $max_stock;

    public $alert_threshold;

    public $is_active = true;

    public $is_sellable = true;

    public $is_stockable = true;

    public $track_lot = false;

    public $track_serial = false;

    public $track_expiry = false;

    public $brand;

    public $family;

    public $packaging;

    public $reseller_price;

    public $promo_price;

    public $weight;

    public $volume;

    public $dimensions;

    public $tax_rate = 0;

    public $categories = [];

    public $suppliers = [];

    public $variants = [];

    public $variantName = '';

    public $variantSku = '';

    public $variantBarcode = '';

    public $variantPrice = null;

    public $variantWholesalePrice = null;

    public $variantPurchasePrice = null;

    public $variantStock = 0;

    public $variantIsActive = true;

    public $lots = [];

    public $lotNumber = '';

    public $lotManufacturingDate = '';

    public $lotExpiry = '';

    public $lotQuantity = 0;

    public $lotSupplierId = '';

    public $serials = [];

    public $serialInput = '';

    public $serialEntryDate = '';

    public $serialWarrantyExpiry = '';

    public $serialCustomerId = '';

    public $attributeGroups = [];

    public $customers = [];

    public $units = [];

    public function mount(?Product $product = null)
    {
        $this->product = $product;
        $companyId = auth()->user()->company_id;

        $this->categories = Category::where('company_id', $companyId)->where('is_active', true)->get();
        $this->suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $this->customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $this->units = Unit::where('company_id', $companyId)->orderBy('name')->get();

        if ($product) {
            $this->existingImage = $product->image;
            $this->name = $product->name;
            $this->reference = $product->reference;
            $this->barcode = $product->barcode;
            $this->category_id = $product->category_id;
            $this->supplier_id = $product->supplier_id;
            $this->description = $product->description;
            $this->purchase_price = $product->purchase_price;
            $this->sale_price = $product->sale_price;
            $this->wholesale_price = $product->wholesale_price;
            $this->reseller_price = $product->reseller_price;
            $this->unit_sale = $product->unit_sale;
            $this->unit_purchase = $product->unit_purchase;
            $this->packaging = $product->packaging;
            $this->min_stock = $product->min_stock;
            $this->max_stock = $product->max_stock;
            $this->alert_threshold = $product->alert_threshold;
            $this->is_active = $product->is_active;
            $this->is_sellable = $product->is_sellable;
            $this->is_stockable = $product->is_stockable;
            $this->track_lot = $product->track_lot;
            $this->track_serial = $product->track_serial;
            $this->track_expiry = $product->track_expiry;
            $this->brand = $product->brand;
            $this->family = $product->family;
            $this->promo_price = $product->promo_price;
            $this->weight = $product->weight;
            $this->volume = $product->volume;
            $this->dimensions = $product->dimensions;
            $this->tax_rate = $product->tax_rate;
            $this->variants = $product->variants()->orderBy('name')->get();
            $this->lots = $product->lots()->orderBy('expiry_date')->get();
            $this->serials = $product->serialNumbers()->orderBy('serial_number')->get();
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $this->existingImage = $this->image->store('products', 'public');
        }

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'reference' => $this->reference,
            'barcode' => $this->barcode,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'description' => $this->description,
            'image' => $this->existingImage,
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'wholesale_price' => $this->wholesale_price,
            'reseller_price' => $this->reseller_price,
            'promo_price' => $this->promo_price,
            'unit_sale' => $this->unit_sale,
            'unit_purchase' => $this->unit_purchase,
            'packaging' => $this->packaging,
            'min_stock' => $this->min_stock,
            'max_stock' => $this->max_stock,
            'alert_threshold' => $this->alert_threshold,
            'is_active' => $this->is_active,
            'is_sellable' => $this->is_sellable,
            'is_stockable' => $this->is_stockable,
            'track_lot' => $this->track_lot,
            'track_serial' => $this->track_serial,
            'track_expiry' => $this->track_expiry,
            'brand' => $this->brand,
            'family' => $this->family,
            'weight' => $this->weight,
            'volume' => $this->volume,
            'dimensions' => $this->dimensions,
            'tax_rate' => $this->tax_rate,
        ];

        if ($this->product) {
            $this->product->update($data);
            session()->flash('message', 'Produit mis à jour.');
        } else {
            Product::create($data);
            session()->flash('message', 'Produit créé.');
        }

        $this->redirectRoute('products.index', navigate: true);
    }

    public function addVariant()
    {
        if (! $this->product) {
            return;
        }
        $this->validate(['variantName' => 'required|string|max:255']);

        $this->product->variants()->create([
            'name' => $this->variantName,
            'sku' => $this->variantSku,
            'barcode' => $this->variantBarcode,
            'price' => $this->variantPrice,
            'wholesale_price' => $this->variantWholesalePrice,
            'purchase_price' => $this->variantPurchasePrice,
            'stock_quantity' => $this->variantStock,
            'is_active' => $this->variantIsActive,
        ]);

        $this->variantName = '';
        $this->variantSku = '';
        $this->variantBarcode = '';
        $this->variantPrice = null;
        $this->variantWholesalePrice = null;
        $this->variantPurchasePrice = null;
        $this->variantStock = 0;
        $this->variantIsActive = true;
        $this->variants = $this->product->variants()->orderBy('name')->get();
    }

    public function deleteVariant($id)
    {
        if ($this->product) {
            $this->product->variants()->where('id', $id)->delete();
            $this->variants = $this->product->variants()->orderBy('name')->get();
        }
    }

    public function addLot()
    {
        if (! $this->product) {
            return;
        }
        $this->validate([
            'lotNumber' => 'required|string|max:255',
            'lotQuantity' => 'required|numeric|min:0',
        ]);

        $this->product->lots()->create([
            'company_id' => auth()->user()->company_id,
            'lot_number' => $this->lotNumber,
            'manufacturing_date' => $this->lotManufacturingDate ?: null,
            'expiry_date' => $this->lotExpiry ?: null,
            'initial_quantity' => $this->lotQuantity,
            'remaining_quantity' => $this->lotQuantity,
            'supplier_id' => $this->lotSupplierId ?: null,
        ]);

        $this->lotNumber = '';
        $this->lotManufacturingDate = '';
        $this->lotExpiry = '';
        $this->lotQuantity = 0;
        $this->lotSupplierId = '';
        $this->lots = $this->product->lots()->orderBy('expiry_date')->get();
    }

    public function deleteLot($id)
    {
        if ($this->product) {
            $this->product->lots()->where('id', $id)->delete();
            $this->lots = $this->product->lots()->orderBy('expiry_date')->get();
        }
    }

    public function addSerial()
    {
        if (! $this->product || ! $this->serialInput) {
            return;
        }

        $numbers = explode("\n", trim($this->serialInput));
        foreach ($numbers as $sn) {
            $sn = trim($sn);
            if (! $sn) {
                continue;
            }
            $this->product->serialNumbers()->firstOrCreate([
                'company_id' => auth()->user()->company_id,
                'serial_number' => $sn,
                'entry_date' => $this->serialEntryDate ?: null,
                'warranty_expiry' => $this->serialWarrantyExpiry ?: null,
                'customer_id' => $this->serialCustomerId ?: null,
            ]);
        }

        $this->serialInput = '';
        $this->serials = $this->product->serialNumbers()->orderBy('serial_number')->get();
    }

    public function deleteSerial($id)
    {
        if ($this->product) {
            $this->product->serialNumbers()->where('id', $id)->delete();
            $this->serials = $this->product->serialNumbers()->orderBy('serial_number')->get();
        }
    }

    public function addAttributeGroup()
    {
        $this->attributeGroups[] = ['name' => '', 'values' => ''];
    }

    public function removeAttributeGroup($index)
    {
        unset($this->attributeGroups[$index]);
        $this->attributeGroups = array_values($this->attributeGroups);
    }

    public function generateCombinations()
    {
        if (! $this->product) {
            return;
        }

        $groups = array_filter($this->attributeGroups, fn ($g) => ! empty($g['name']) && ! empty($g['values']));
        if (empty($groups)) {
            return;
        }

        $dimensions = [];
        foreach ($groups as $group) {
            $values = array_map('trim', explode(',', $group['values']));
            $values = array_filter($values);
            if (! empty($values)) {
                $dimensions[$group['name']] = $values;
            }
        }

        if (empty($dimensions)) {
            return;
        }

        $combinations = $this->cartesianProduct($dimensions);

        foreach ($combinations as $combo) {
            $name = implode(' / ', array_values($combo));
            $attrs = json_encode($combo);
            $existing = $this->product->variants()->where('name', $name)->first();
            if (! $existing) {
                $this->product->variants()->create([
                    'name' => $name,
                    'attributes' => $attrs,
                    'stock_quantity' => 0,
                    'is_active' => true,
                ]);
            }
        }

        $this->attributeGroups = [];
        $this->variants = $this->product->variants()->orderBy('name')->get();
    }

    private function cartesianProduct(array $dimensions): array
    {
        $result = [[]];
        foreach ($dimensions as $dimName => $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $value) {
                    $product[$dimName] = $value;
                    $append[] = $product;
                }
            }
            $result = $append;
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.pages.products.form', [
            'categories' => $this->categories,
            'suppliers' => $this->suppliers,
            'customers' => $this->customers,
            'units' => $this->units,
        ])->layout('layouts.app', ['header' => $this->product ? 'Modifier le produit' : 'Nouveau produit']);
    }
}
