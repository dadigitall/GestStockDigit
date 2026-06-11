<?php

namespace App\Livewire\Pages\Users;

use App\Models\Store;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $editingUser = null;

    public $name;

    public $first_name;

    public $last_name;

    public $username;

    public $email;

    public $phone;

    public $password;

    public $password_confirmation;

    public $store_id;

    public $status = 'active';

    public $selectedRoles = [];

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $users = User::where('company_id', $companyId)
            ->with('store', 'roles')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stores = Store::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('livewire.pages.users.index', compact('users', 'stores', 'roles'))
            ->layout('layouts.app', ['header' => 'Utilisateurs']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(User $user)
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->store_id = $user->store_id;
        $this->status = $user->status;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showForm = true;
    }

    public function save()
    {
        $companyId = auth()->user()->company_id;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUser?->id)],
            'phone' => 'nullable|string|max:50',
            'store_id' => 'nullable|exists:stores,id',
            'status' => 'required|in:active,inactive,suspended',
            'selectedRoles' => 'array',
        ];

        if ($this->editingUser) {
            if ($this->password) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }
        } else {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        $data = [
            'company_id' => $companyId,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'store_id' => $this->store_id ?: null,
            'status' => $this->status,
            'is_active' => $this->status === 'active',
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            $this->editingUser->syncRoles($this->selectedRoles);
        } else {
            $user = User::create($data);
            $user->assignRole($this->selectedRoles);
        }

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->first_name = '';
        $this->last_name = '';
        $this->username = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->store_id = null;
        $this->status = 'active';
        $this->selectedRoles = [];
        $this->editingUser = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
