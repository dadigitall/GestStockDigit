<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Pièce', 'slug' => 'piece', 'base_unit' => true],
            ['name' => 'Carton', 'slug' => 'carton', 'base_unit' => false],
            ['name' => 'Paquet', 'slug' => 'paquet', 'base_unit' => false],
            ['name' => 'Boîte', 'slug' => 'boite', 'base_unit' => false],
            ['name' => 'Kilogramme', 'slug' => 'kg', 'base_unit' => true],
            ['name' => 'Gramme', 'slug' => 'g', 'base_unit' => false],
            ['name' => 'Litre', 'slug' => 'litre', 'base_unit' => true],
            ['name' => 'Mètre', 'slug' => 'm', 'base_unit' => true],
            ['name' => 'Sac', 'slug' => 'sac', 'base_unit' => false],
            ['name' => 'Palette', 'slug' => 'palette', 'base_unit' => false],
            ['name' => 'Caisse', 'slug' => 'caisse', 'base_unit' => false],
            ['name' => 'Bouteille', 'slug' => 'bouteille', 'base_unit' => false],
            ['name' => 'Rouleau', 'slug' => 'rouleau', 'base_unit' => false],
            ['name' => 'Unité', 'slug' => 'unite', 'base_unit' => false],
        ];

        $companyId = Company::value('id');

        if ($companyId) {
            foreach ($units as $unit) {
                Unit::create(array_merge($unit, ['company_id' => $companyId, 'type' => 'standard']));
            }
        }
    }
}
