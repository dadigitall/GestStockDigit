<?php

namespace App\Livewire\Pages\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showForm = false;
    public $editingCustomer = null;
    public $name;
    public $phone;
    public $email;
    public $address;
    public $type = 'particular';
    public $credit_limit;
    public $payment_terms;

    public function render()
    {
        return view('livewire.pages.customers.index', [
            'customers' => Customer::where('company_id', auth()->user()->company_id)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->orderBy('name')
                ->paginate(15),
        ])->layout('layouts.app', ['header' => 'Clients']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Customer $customer)
    {
        $this->editingCustomer = $customer;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->type = $customer->type;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate(['name' => 'required|string|max:255']);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name, 'phone' => $this->phone,
            'email' => $this->email, 'address' => $this->address,
            'type' => $this->type, 'credit_limit' => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
        ];

        $this->editingCustomer ? $this->editingCustomer->update($data) : Customer::create($data);
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->fill(['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'type' => 'particular', 'credit_limit' => null, 'payment_terms' => '']);
        $this->editingCustomer = null;
        $this->showForm = false;
    }

    public function cancel() { $this->resetForm(); }
}
