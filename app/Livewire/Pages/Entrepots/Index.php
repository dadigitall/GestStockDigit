<?php

namespace App\Livewire\Pages\Entrepots;

use App\Models\Location;
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

    public $type = 'entrepot';

    public $parent_id;

    public $address;

    public $phone;

    public $email;

    public $manager_id;

    public $opening_hours;

    public $allows_stock = true;

    public $allows_sales = false;

    public $allows_cash_register = false;

    public $notes;

    // Location management
    public $managingStore = null;

    public $locationName;

    public $locationCode;

    public $locationType = 'rayon';

    public $locationParentId;

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $types = ['entrepot', 'depot'];

        $stores = Store::where('company_id', $companyId)
            ->whereIn('type', $types)
            ->with('parent', 'manager', 'locations')
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

        $locationParents = collect();
        if ($this->managingStore) {
            $locationParents = Location::where('store_id', $this->managingStore->id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.pages.entrepots.index', compact('stores', 'parents', 'managers', 'locationParents'))
            ->layout('layouts.app', ['header' => 'Entrepôts & Dépôts']);
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
            'type' => 'required|string|in:entrepot,depot',
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

    public function manageLocations(Store $store)
    {
        $this->managingStore = $store;
        $this->resetLocationForm();
    }

    public function addLocation()
    {
        $this->validate([
            'locationName' => 'required|string|max:255',
            'locationCode' => 'required|string|max:50',
            'locationType' => 'required|string',
        ]);

        if (! $this->managingStore) {
            return;
        }

        Location::create([
            'store_id' => $this->managingStore->id,
            'parent_id' => $this->locationParentId ?: null,
            'name' => $this->locationName,
            'code' => $this->locationCode,
            'type' => $this->locationType,
        ]);

        $this->resetLocationForm();
    }

    public function deleteLocation(Location $location)
    {
        $location->delete();
    }

    public function resetLocationForm()
    {
        $this->locationName = '';
        $this->locationCode = '';
        $this->locationType = 'rayon';
        $this->locationParentId = null;
    }

    public function closeLocations()
    {
        $this->managingStore = null;
        $this->resetLocationForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->type = 'entrepot';
        $this->parent_id = null;
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->manager_id = null;
        $this->opening_hours = '';
        $this->allows_stock = true;
        $this->allows_sales = false;
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
