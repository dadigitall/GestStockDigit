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

        // Create or update company with XOF currency
        $company = Company::firstOrCreate(
            ['email' => 'contact@stockflow360.com'],
            [
                'name' => 'StockFlow 360° CI',
                'legal_name' => 'StockFlow 360° SARL',
                'email' => 'contact@stockflow360.com',
                'phone' => '+225 01 00 00 00',
                'address' => 'Abidjan, Côte d\'Ivoire',
                'currency' => 'XOF',
                'tax_number' => 'CI-2024-01234',
                'registration_number' => 'RCCM: CI-ABJ-2024-01234',
                'invoice_prefix' => 'FAC',
                'sale_prefix' => 'VENTE',
                'purchase_prefix' => 'ACHAT',
                'delivery_prefix' => 'BL',
                'quotation_prefix' => 'DEV',
                'credit_note_prefix' => 'AVOIR',
                'transfer_prefix' => 'TR',
                'default_tax_rate' => 18,
                'is_active' => true,
            ]
        );

        // Create or update main store
        $store = Store::firstOrCreate(
            ['code' => 'BP-001'],
            [
                'company_id' => $company->id,
                'name' => 'Boutique Principale — Yopougon',
                'code' => 'BP-001',
                'type' => 'boutique',
                'address' => 'Yopougon Niangon, Abidjan, Côte d\'Ivoire',
                'phone' => '+225 01 01 01 01',
                'allows_stock' => true,
                'allows_sales' => true,
                'allows_cash_register' => true,
            ]
        );

        $company->update(['currency' => 'XOF']);

        // Create or update admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@geststock.com'],
            [
                'name' => 'Admin',
                'first_name' => 'Admin',
                'last_name' => 'Système',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'store_id' => $store->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $admin->assignRole('Super Admin');

        $this->call(UnitSeeder::class);
        $this->call(CustomerCategorySeeder::class);
        $this->call(DemoSeeder::class);
    }
}
