<?php

namespace App\Livewire\Pages\Promotions;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editId = null;

    public string $name = '';

    public string $promotionType = 'period';

    public string $description = '';

    public ?string $discountValue = null;

    public string $discountType = 'percentage';

    public ?string $minPurchase = null;

    public ?int $minQuantity = null;

    public ?int $maxQuantity = null;

    public ?int $buyQuantity = null;

    public ?int $getQuantity = null;

    public bool $isActive = true;

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    public int $priority = 0;

    public array $selectedProducts = [];

    public array $selectedCategories = [];

    public array $selectedCustomers = [];

    public array $selectedStores = [];

    public string $productSearch = '';

    public array $productResults = [];

    public string $filterType = '';

    public string $filterStatus = '';

    public string $search = '';

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $promotions = Promotion::where('company_id', $companyId)
            ->with(['products', 'categories', 'customers', 'stores'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->pluck('name', 'id');
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

        return view('livewire.pages.promotions.index', compact('promotions', 'categories', 'customers', 'stores'))
            ->layout('components.layouts.app', ['title' => 'Promotions']);
    }

    public function selectProduct(int $id)
    {
        if (!in_array($id, $this->selectedProducts)) {
            $this->selectedProducts[] = $id;
        }
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function removeProduct(int $id)
    {
        $this->selectedProducts = array_values(array_filter($this->selectedProducts, fn ($v) => $v !== $id));
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'promotionType' => 'required|string',
        ]);

        $companyId = auth()->user()->company_id;

        $promotion = Promotion::create([
            'company_id' => $companyId,
            'name' => $this->name,
            'type' => $this->promotionType,
            'description' => $this->description,
            'discount_value' => $this->discountValue ?: 0,
            'discount_type' => $this->discountType,
            'min_purchase' => $this->minPurchase ?: 0,
            'min_quantity' => $this->minQuantity ?: 0,
            'max_quantity' => $this->maxQuantity,
            'buy_quantity' => $this->buyQuantity ?: 0,
            'get_quantity' => $this->getQuantity ?: 0,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'priority' => $this->priority,
        ]);

        if (!empty($this->selectedProducts)) {
            $promotion->products()->sync($this->selectedProducts);
        }
        if (!empty($this->selectedCategories)) {
            $promotion->categories()->sync($this->selectedCategories);
        }
        if (!empty($this->selectedCustomers)) {
            $promotion->customers()->sync($this->selectedCustomers);
        }
        if (!empty($this->selectedStores)) {
            $promotion->stores()->sync($this->selectedStores);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Promotion créée avec succès.');
    }

    public function edit(int $id)
    {
        $promo = Promotion::with(['products', 'categories', 'customers', 'stores'])->findOrFail($id);
        $this->editId = $promo->id;
        $this->name = $promo->name;
        $this->promotionType = $promo->type;
        $this->description = $promo->description ?? '';
        $this->discountValue = (string) $promo->discount_value;
        $this->discountType = $promo->discount_type;
        $this->minPurchase = (string) $promo->min_purchase;
        $this->minQuantity = $promo->min_quantity;
        $this->maxQuantity = $promo->max_quantity;
        $this->buyQuantity = $promo->buy_quantity;
        $this->getQuantity = $promo->get_quantity;
        $this->isActive = $promo->is_active;
        $this->startsAt = $promo->starts_at?->format('Y-m-d\TH:i');
        $this->endsAt = $promo->ends_at?->format('Y-m-d\TH:i');
        $this->priority = $promo->priority;
        $this->selectedProducts = $promo->products->pluck('id')->toArray();
        $this->selectedCategories = $promo->categories->pluck('id')->toArray();
        $this->selectedCustomers = $promo->customers->pluck('id')->toArray();
        $this->selectedStores = $promo->stores->pluck('id')->toArray();
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $promotion = Promotion::findOrFail($this->editId);
        $promotion->update([
            'name' => $this->name,
            'type' => $this->promotionType,
            'description' => $this->description,
            'discount_value' => $this->discountValue ?: 0,
            'discount_type' => $this->discountType,
            'min_purchase' => $this->minPurchase ?: 0,
            'min_quantity' => $this->minQuantity ?: 0,
            'max_quantity' => $this->maxQuantity,
            'buy_quantity' => $this->buyQuantity ?: 0,
            'get_quantity' => $this->getQuantity ?: 0,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'priority' => $this->priority,
        ]);

        $promotion->products()->sync($this->selectedProducts);
        $promotion->categories()->sync($this->selectedCategories);
        $promotion->customers()->sync($this->selectedCustomers);
        $promotion->stores()->sync($this->selectedStores);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Promotion mise à jour avec succès.');
    }

    public function toggleActive(int $id)
    {
        $promo = Promotion::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);
        session()->flash('success', 'Promotion '.($promo->is_active ? 'activée' : 'désactivée').'.');
    }

    public function delete(int $id)
    {
        Promotion::findOrFail($id)->delete();
        session()->flash('success', 'Promotion supprimée.');
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->name = '';
        $this->promotionType = 'period';
        $this->description = '';
        $this->discountValue = null;
        $this->discountType = 'percentage';
        $this->minPurchase = null;
        $this->minQuantity = null;
        $this->maxQuantity = null;
        $this->buyQuantity = null;
        $this->getQuantity = null;
        $this->isActive = true;
        $this->startsAt = null;
        $this->endsAt = null;
        $this->priority = 0;
        $this->selectedProducts = [];
        $this->selectedCategories = [];
        $this->selectedCustomers = [];
        $this->selectedStores = [];
        $this->productSearch = '';
        $this->productResults = [];
    }
}
