<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        $company = Company::create([
            'name' => 'Ma Société',
            'legal_name' => 'Ma Société SARL',
            'email' => 'contact@masociete.com',
            'phone' => '+237 600 000 000',
            'currency' => 'XAF',
        ]);

        $store = Store::create([
            'company_id' => $company->id,
            'name' => 'Boutique Principale',
            'code' => 'BP-001',
            'type' => 'boutique',
            'address' => 'Douala, Cameroun',
            'phone' => '+237 600 000 001',
            'allows_stock' => true,
            'allows_sales' => true,
            'allows_cash_register' => true,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@geststock.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'store_id' => $store->id,
            'first_name' => 'Admin',
            'last_name' => 'Système',
        ]);

        $admin->assignRole('Super Admin');
    }
}
