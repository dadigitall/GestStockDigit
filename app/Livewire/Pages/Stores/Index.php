<?php

namespace App\Livewire\Pages\Stores;

use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $viewMode = 'list';

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

    protected $types = [
        'filiale' => 'Filiale',
        'agence' => 'Agence',
        'boutique' => 'Boutique',
        'point_vente' => 'Point de vente',
        'magasin' => 'Magasin',
        'depot' => 'Dépôt',
        'entrepot' => 'Entrepôt',
        'rayon' => 'Rayon',
        'zone_stockage' => 'Zone de stockage',
        'emplacement' => 'Emplacement',
    ];

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $stores = Store::where('company_id', $companyId)
            ->with('parent', 'manager')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        $parents = Store::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->orWhere(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->whereNotNull('parent_id');
            })
            ->orderBy('name')
            ->get();

        $managers = User::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $tree = Store::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('livewire.pages.stores.index', compact('stores', 'parents', 'managers', 'tree'))
            ->layout('layouts.app', ['header' => 'Entités']);
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
            'type' => 'required|string',
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

    public function toggleActive(Store $store)
    {
        $store->update(['is_active' => ! $store->is_active]);
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

    public function toggleView($mode)
    {
        $this->viewMode = $mode;
    }

    public function getTypeLabel($type)
    {
        return $this->types[$type] ?? $type;
    }

    public function getTypeIcon($type)
    {
        return match ($type) {
            'filiale', 'agence' => 'building2',
            'boutique', 'point_vente', 'magasin' => 'store',
            'depot', 'entrepot' => 'warehouse',
            'rayon', 'zone_stockage', 'emplacement' => 'package',
            default => 'store',
        };
    }

    public function getTypeColor($type)
    {
        return match ($type) {
            'filiale', 'agence' => 'from-violet-500 to-purple-600',
            'boutique', 'point_vente', 'magasin' => 'from-emerald-500 to-teal-600',
            'depot', 'entrepot' => 'from-amber-500 to-orange-600',
            'rayon', 'zone_stockage', 'emplacement' => 'from-sky-500 to-blue-600',
            default => 'from-slate-500 to-slate-600',
        };
    }
}
