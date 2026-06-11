<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view products', 'create products', 'edit products', 'delete products',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view stock', 'create stock movements', 'adjust stock',
            'view sales', 'create sales', 'cancel sales',
            'view purchases', 'create purchases', 'receive purchases',
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view transfers', 'create transfers', 'approve transfers',
            'view inventory', 'create inventory', 'validate inventory',
            'view cash register', 'manage cash register', 'close cash register',
            'view reports', 'export reports',
            'view financial reports', 'view margins',
            'manage users', 'manage roles', 'manage settings',
            'view audit log',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            'Super Admin' => Permission::all(),
            'Admin' => Permission::all()->pluck('name')->toArray(),
            'Store Manager' => [
                'view dashboard', 'view products', 'create products', 'edit products',
                'view stock', 'create stock movements', 'adjust stock',
                'view sales', 'create sales', 'cancel sales',
                'view customers', 'create customers', 'edit customers',
                'view transfers', 'create transfers',
                'view inventory', 'create inventory',
                'view cash register', 'manage cash register', 'close cash register',
                'view reports', 'export reports',
            ],
            'Warehouse Manager' => [
                'view dashboard', 'view products',
                'view stock', 'create stock movements', 'adjust stock',
                'view purchases', 'create purchases', 'receive purchases',
                'view suppliers',
                'view transfers', 'create transfers', 'approve transfers',
                'view inventory', 'create inventory', 'validate inventory',
                'view reports',
            ],
            'Cashier' => [
                'view products',
                'view stock',
                'view sales', 'create sales',
                'view customers', 'create customers',
                'view cash register', 'manage cash register',
                'view reports',
            ],
            'Salesperson' => [
                'view products',
                'view stock',
                'view sales', 'create sales',
                'view customers', 'create customers',
                'view reports',
            ],
            'Stock Manager' => [
                'view products',
                'view stock', 'create stock movements', 'adjust stock',
                'view transfers', 'create transfers',
                'view inventory', 'create inventory',
                'view suppliers',
                'view reports', 'export reports',
            ],
            'Accountant' => [
                'view dashboard', 'view products',
                'view stock',
                'view sales', 'view purchases',
                'view invoices', 'create invoices', 'edit invoices',
                'view customers', 'view suppliers',
                'view cash register',
                'view financial reports', 'view margins',
                'view reports', 'export reports',
            ],
            'Auditor' => [
                'view dashboard', 'view products', 'view stock',
                'view sales', 'view purchases',
                'view invoices',
                'view customers', 'view suppliers',
                'view financial reports', 'view margins',
                'view audit log',
                'view reports', 'export reports',
            ],
        ];

        foreach ($roles as $name => $rolePermissions) {
            $role = Role::findOrCreate($name);
            $role->syncPermissions($rolePermissions);
        }
    }
}
