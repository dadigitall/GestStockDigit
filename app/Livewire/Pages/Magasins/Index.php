<?php

namespace App\Livewire\Pages\Magasins;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $editingStore = null;

    public $name;

    public $code;

    public $type = 'boutique';

    public $parent_id;

    public $address;

    public $phone;

    public $email;

    public $manager_id;

    public $opening_hours;

    public $allows_stock = true;

    public $allows_sales = true;

    public $allows_cash_register = false;

    public $notes;

    // Sellable products management
    public $manageStore = null;

    public $productSearch = '';

    public $selectedProducts = [];

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $types = ['boutique', 'magasin', 'point_vente'];

        $stores = Store::where('company_id', $companyId)
            ->whereIn('type', $types)
            ->with('parent', 'manager', 'products')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(12);

        $parents = Store::where('company_id', $companyId)
            ->whereIn('type', $types)
            ->orderBy('name')
            ->get();

        $managers = User::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        // Sellable products
        $products = collect();
        if ($this->manageStore) {
            $products = Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('reference', 'like', "%{$this->productSearch}%");
                })
                ->orderBy('name')
                ->take(50)
                ->get();
        }

        return view('livewire.pages.magasins.index', compact('stores', 'parents', 'managers', 'products'))
            ->layout('layouts.app', ['header' => 'Magasins & Points de vente']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Store $store)
    {
        $this->editingStore = $store;
        $this->name = $store->name;
        $this->code = $store->code;
        $this->type = $store->type;
        $this->parent_id = $store->parent_id;
        $this->address = $store->address;
        $this->phone = $store->phone;
        $this->email = $store->email;
        $this->manager_id = $store->manager_id;
        $this->opening_hours = $store->opening_hours;
        $this->allows_stock = $store->allows_stock;
        $this->allows_sales = $store->allows_sales;
        $this->allows_cash_register = $store->allows_cash_register;
        $this->notes = $store->notes;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code,'.($this->editingStore?->id ?? ''),
            'type' => 'required|string|in:boutique,magasin,point_vente',
            'email' => 'nullable|email|max:255',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'parent_id' => $this->parent_id ?: null,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_id' => $this->manager_id ?: null,
            'opening_hours' => $this->opening_hours,
            'allows_stock' => $this->allows_stock,
            'allows_sales' => $this->allows_sales,
            'allows_cash_register' => $this->allows_cash_register,
            'notes' => $this->notes,
        ];

        if ($this->editingStore) {
            $this->editingStore->update($data);
        } else {
            Store::create($data);
        }

        $this->resetForm();
    }

    public function manageProducts(Store $store)
    {
        $this->manageStore = $store;
        $this->selectedProducts = $store->products()
            ->wherePivot('is_sellable', true)
            ->pluck('products.id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function saveProducts()
    {
        if (! $this->manageStore) {
            return;
        }

        $sync = [];
        foreach ($this->selectedProducts as $productId) {
            $sync[$productId] = ['is_sellable' => true, 'is_active' => true];
        }

        $this->manageStore->products()->sync($sync);
        $this->manageStore = null;
        $this->selectedProducts = [];
        $this->productSearch = '';
    }

    public function cancelManageProducts()
    {
        $this->manageStore = null;
        $this->selectedProducts = [];
        $this->productSearch = '';
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->type = 'boutique';
        $this->parent_id = null;
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->manager_id = null;
        $this->opening_hours = '';
        $this->allows_stock = true;
        $this->allows_sales = true;
        $this->allows_cash_register = false;
        $this->notes = '';
        $this->editingStore = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
