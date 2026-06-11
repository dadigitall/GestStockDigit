<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SensitivePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'modify prices',
            'apply exceptional discount',
            'open cash register',
        ];

        foreach ($newPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findByName('Admin');
        $admin->givePermissionTo($newPermissions);

        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->givePermissionTo($newPermissions);

        $storeManager = Role::findByName('Store Manager');
        $storeManager->givePermissionTo(['modify prices', 'apply exceptional discount', 'open cash register']);
    }
}
