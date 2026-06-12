<?php

namespace App\Livewire\Pages\PriceTiers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editId = null;

    public ?string $productSearch = '';

    public ?int $productId = null;

    public string $productName = '';

    public ?int $categoryId = null;

    public ?int $customerCategoryId = null;

    public ?int $customerId = null;

    public ?int $storeId = null;

    public ?string $minQuantity = null;

    public ?string $maxQuantity = null;

    public ?string $price = null;

    public string $priceLabel = '';

    public int $priority = 0;

    public bool $isActive = true;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $productResults = [];

    public array $customers = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->customers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $tiers = PriceTier::where('company_id', $companyId)
            ->with(['product', 'category', 'customerCategory', 'customer', 'store'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = Category::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id');
        $customerCategories = CustomerCategory::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id');
        $stores = Store::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id');

        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('reference', 'like', "%{$this->productSearch}%");
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->productResults = [];
        }

        return view('livewire.pages.price-tiers.index', compact('tiers', 'categories', 'customerCategories', 'stores'))
            ->layout('components.layouts.app', ['title' => 'Grilles de prix']);
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function selectProduct(int $id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->productName = $product->name;
        $this->productSearch = $product->name;
        $this->productResults = [];
    }

    public function removeProduct()
    {
        $this->productId = null;
        $this->productName = '';
        $this->productSearch = '';
    }

    public function save()
    {
        $this->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $companyId = auth()->user()->company_id;

        PriceTier::create([
            'company_id' => $companyId,
            'product_id' => $this->productId,
            'category_id' => $this->categoryId ?: null,
            'customer_category_id' => $this->customerCategoryId ?: null,
            'customer_id' => $this->customerId ?: null,
            'store_id' => $this->storeId ?: null,
            'min_quantity' => $this->minQuantity ?: 0,
            'max_quantity' => $this->maxQuantity ?: null,
            'price' => $this->price,
            'price_label' => $this->priceLabel,
            'priority' => $this->priority,
            'is_active' => $this->isActive,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Palier de prix créé avec succès.');
    }

    public function edit(int $id)
    {
        $tier = PriceTier::findOrFail($id);
        $this->editId = $tier->id;
        $this->productId = $tier->product_id;
        $this->productName = $tier->product?->name ?? '';
        $this->productSearch = $tier->product?->name ?? '';
        $this->categoryId = $tier->category_id;
        $this->customerCategoryId = $tier->customer_category_id;
        $this->customerId = $tier->customer_id;
        $this->storeId = $tier->store_id;
        $this->minQuantity = (string) $tier->min_quantity;
        $this->maxQuantity = $tier->max_quantity ? (string) $tier->max_quantity : null;
        $this->price = (string) $tier->price;
        $this->priceLabel = $tier->price_label;
        $this->priority = $tier->priority;
        $this->isActive = $tier->is_active;
        $this->startDate = $tier->start_date?->format('Y-m-d');
        $this->endDate = $tier->end_date?->format('Y-m-d');
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate([
            'price' => 'required|numeric|min:0',
        ]);

        PriceTier::findOrFail($this->editId)->update([
            'product_id' => $this->productId,
            'category_id' => $this->categoryId ?: null,
            'customer_category_id' => $this->customerCategoryId ?: null,
            'customer_id' => $this->customerId ?: null,
            'store_id' => $this->storeId ?: null,
            'min_quantity' => $this->minQuantity ?: 0,
            'max_quantity' => $this->maxQuantity ?: null,
            'price' => $this->price,
            'price_label' => $this->priceLabel,
            'priority' => $this->priority,
            'is_active' => $this->isActive,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Palier de prix mis à jour avec succès.');
    }

    public function toggleActive(int $id)
    {
        $tier = PriceTier::findOrFail($id);
        $tier->update(['is_active' => !$tier->is_active]);
        session()->flash('success', 'Palier '.($tier->is_active ? 'activé' : 'désactivé').'.');
    }

    public function delete(int $id)
    {
        PriceTier::findOrFail($id)->delete();
        session()->flash('success', 'Palier de prix supprimé.');
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->productSearch = '';
        $this->productId = null;
        $this->productName = '';
        $this->categoryId = null;
        $this->customerCategoryId = null;
        $this->customerId = null;
        $this->storeId = null;
        $this->minQuantity = null;
        $this->maxQuantity = null;
        $this->price = null;
        $this->priceLabel = '';
        $this->priority = 0;
        $this->isActive = true;
        $this->startDate = null;
        $this->endDate = null;
        $this->productResults = [];
    }
}
