<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomerCategory;
use Illuminate\Database\Seeder;

class CustomerCategorySeeder extends Seeder
{
    public function run(): void
    {
        $companyId = Company::value('id');

        if (! $companyId) {
            return;
        }

        $categories = [
            ['name' => 'Client détail', 'slug' => 'detail', 'color' => '#6366f1'],
            ['name' => 'Client gros', 'slug' => 'gros', 'color' => '#059669'],
            ['name' => 'Client VIP', 'slug' => 'vip', 'color' => '#d97706'],
            ['name' => 'Revendeur', 'slug' => 'revendeur', 'color' => '#dc2626'],
            ['name' => 'Entreprise', 'slug' => 'entreprise', 'color' => '#2563eb'],
            ['name' => 'Administration', 'slug' => 'administration', 'color' => '#7c3aed'],
            ['name' => 'Client à crédit', 'slug' => 'credit', 'color' => '#0891b2'],
            ['name' => 'Client bloqué', 'slug' => 'bloque', 'color' => '#78716c'],
        ];

        foreach ($categories as $cat) {
            CustomerCategory::create(array_merge($cat, ['company_id' => $companyId]));
        }
    }
}
