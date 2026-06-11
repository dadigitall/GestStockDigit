<?php

namespace App\Livewire\Pages\Stores;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Store;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingStore = null;

    public $name;
    public $code;
    public $type = 'boutique';
    public $address;
    public $phone;
    public $email;
    public $allows_stock = true;
    public $allows_sales = true;
    public $allows_cash_register = false;

    public function render()
    {
        $stores = Store::where('company_id', auth()->user()->company_id)
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.pages.stores.index', compact('stores'))
            ->layout('layouts.app', ['header' => 'Magasins & Entrepôts']);
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
        $this->address = $store->address;
        $this->phone = $store->phone;
        $this->email = $store->email;
        $this->allows_stock = $store->allows_stock;
        $this->allows_sales = $store->allows_sales;
        $this->allows_cash_register = $store->allows_cash_register;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code,' . ($this->editingStore?->id ?? ''),
            'type' => 'required|string',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'allows_stock' => $this->allows_stock,
            'allows_sales' => $this->allows_sales,
            'allows_cash_register' => $this->allows_cash_register,
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
        $store->update(['is_active' => !$store->is_active]);
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->type = 'boutique';
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->allows_stock = true;
        $this->allows_sales = true;
        $this->allows_cash_register = false;
        $this->editingStore = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
