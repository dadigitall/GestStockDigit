<?php

namespace App\Livewire\Pages\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Index extends Component
{
    public $showForm = false;

    public $editingRole = null;

    public $name;

    public $rolePermissions = [];

    public function render()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $grouped = $permissions->groupBy(function ($p) {
            $parts = explode(' ', $p->name);

            return $parts[0] ?? 'general';
        });

        return view('livewire.pages.roles.index', compact('roles', 'permissions', 'grouped'))
            ->layout('layouts.app', ['header' => 'Rôles & Permissions']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Role $role)
    {
        $this->editingRole = $role;
        $this->name = $role->name;
        $this->rolePermissions = $role->permissions->pluck('name')->toArray();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.($this->editingRole?->id ?? ''),
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if ($this->editingRole) {
            $this->editingRole->update(['name' => $this->name]);
            $this->editingRole->syncPermissions($this->rolePermissions);
        } else {
            $role = Role::findOrCreate($this->name);
            $role->syncPermissions($this->rolePermissions);
        }

        $this->resetForm();
    }

    public function delete(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return;
        }
        $role->delete();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->rolePermissions = [];
        $this->editingRole = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
