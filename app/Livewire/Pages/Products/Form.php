<?php

namespace App\Livewire\Pages\Products;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class Form extends Component
{
    public ?Product $product = null;
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
    public $tax_rate = 0;

    public $categories = [];
    public $suppliers = [];

    public function mount(?Product $product = null)
    {
        $this->product = $product;
        $companyId = auth()->user()->company_id;

        $this->categories = Category::where('company_id', $companyId)->where('is_active', true)->get();
        $this->suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        if ($product) {
            $this->name = $product->name;
            $this->reference = $product->reference;
            $this->barcode = $product->barcode;
            $this->category_id = $product->category_id;
            $this->supplier_id = $product->supplier_id;
            $this->description = $product->description;
            $this->purchase_price = $product->purchase_price;
            $this->sale_price = $product->sale_price;
            $this->wholesale_price = $product->wholesale_price;
            $this->unit_sale = $product->unit_sale;
            $this->unit_purchase = $product->unit_purchase;
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
            $this->tax_rate = $product->tax_rate;
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
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'reference' => $this->reference,
            'barcode' => $this->barcode,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'description' => $this->description,
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'wholesale_price' => $this->wholesale_price,
            'unit_sale' => $this->unit_sale,
            'unit_purchase' => $this->unit_purchase,
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

    public function render()
    {
        return view('livewire.pages.products.form', [
            'categories' => $this->categories,
            'suppliers' => $this->suppliers,
        ])->layout('layouts.app', ['header' => $this->product ? 'Modifier le produit' : 'Nouveau produit']);
    }
}
