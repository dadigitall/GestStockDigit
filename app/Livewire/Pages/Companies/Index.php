<?php

namespace App\Livewire\Pages\Companies;

use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $showCreateForm = false;

    public $showDetails = null;

    // Form
    public $name;

    public $legal_name;

    public $email;

    public $phone;

    public $currency = 'XAF';

    // New admin
    public $admin_name;

    public $admin_email;

    public $admin_password;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'currency' => 'required|string|size:3',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ];
    }

    public function createCompany()
    {
        $this->validate();

        $company = Company::create([
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'currency' => $this->currency,
        ]);

        $store = Store::create([
            'company_id' => $company->id,
            'name' => 'Boutique Principale',
            'code' => 'BP-001',
            'type' => 'boutique',
            'allows_stock' => true,
            'allows_sales' => true,
            'allows_cash_register' => true,
        ]);

        $user = User::create([
            'name' => $this->admin_name,
            'email' => $this->admin_email,
            'password' => bcrypt($this->admin_password),
            'company_id' => $company->id,
            'store_id' => $store->id,
        ]);

        $user->assignRole('Admin');

        $this->reset(['showCreateForm', 'name', 'legal_name', 'email', 'phone', 'currency', 'admin_name', 'admin_email', 'admin_password']);

        session()->flash('success', "Entreprise « {$company->name} » créée avec succès.");
    }

    public function toggleDetails($id)
    {
        $this->showDetails = $this->showDetails === $id ? null : $id;
    }

    public function render()
    {
        $companies = Company::withCount(['stores', 'users'])->orderBy('name')->paginate(10);

        return view('livewire.pages.companies.index', compact('companies'))
            ->layout('layouts.app', ['header' => 'Entreprises']);
    }
}
