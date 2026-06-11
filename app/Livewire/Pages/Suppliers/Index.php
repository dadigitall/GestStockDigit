<?php

namespace App\Livewire\Pages\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $editingSupplier = null;

    public $name;

    public $type;

    public $phone;

    public $email;

    public $address;

    public $contact_name;

    public $payment_terms;

    public function render()
    {
        $suppliers = Supplier::where('company_id', auth()->user()->company_id)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.pages.suppliers.index', compact('suppliers'))
            ->layout('layouts.app', ['header' => 'Fournisseurs']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Supplier $supplier)
    {
        $this->editingSupplier = $supplier;
        $this->name = $supplier->name;
        $this->type = $supplier->type;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->contact_name = $supplier->contact_name;
        $this->payment_terms = $supplier->payment_terms;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'contact_name' => $this->contact_name,
            'payment_terms' => $this->payment_terms,
        ];

        if ($this->editingSupplier) {
            $this->editingSupplier->update($data);
        } else {
            Supplier::create($data);
        }

        $this->resetForm();
    }

    public function toggleActive(Supplier $supplier)
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);
    }

    public function resetForm()
    {
        $this->name = '';
        $this->type = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->contact_name = '';
        $this->payment_terms = '';
        $this->editingSupplier = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
