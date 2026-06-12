<?php

namespace App\Services;

use App\Models\Company;

class SettingsService
{
    protected array $definitions = [
        'multi_store' => [
            'label' => 'Gestion multi-magasins',
            'description' => 'Activer la gestion de plusieurs points de vente.',
            'default' => true,
            'category' => 'inventory',
        ],
        'multi_warehouse' => [
            'label' => 'Gestion multi-entrepôts',
            'description' => 'Activer la gestion de plusieurs entrepôts de stockage.',
            'default' => true,
            'category' => 'inventory',
        ],
        'credit_sale' => [
            'label' => 'Vente à crédit',
            'description' => 'Permettre les ventes à crédit avec suivi des échéances.',
            'default' => true,
            'category' => 'sales',
        ],
        'lot_management' => [
            'label' => 'Gestion des lots',
            'description' => 'Activer le suivi des numéros de lot sur les produits.',
            'default' => false,
            'category' => 'inventory',
        ],
        'serial_management' => [
            'label' => 'Gestion des séries',
            'description' => 'Activer le suivi des numéros de série sur les produits.',
            'default' => false,
            'category' => 'inventory',
        ],
        'expiry_management' => [
            'label' => 'Gestion de péremption',
            'description' => 'Activer le suivi des dates de péremption avec alertes.',
            'default' => false,
            'category' => 'inventory',
        ],
        'tax_management' => [
            'label' => 'Gestion des taxes',
            'description' => 'Activer le calcul et le suivi des taxes sur les ventes et achats.',
            'default' => true,
            'category' => 'finance',
        ],
        'multi_currency' => [
            'label' => 'Multi-devise',
            'description' => 'Activer la gestion de plusieurs devises.',
            'default' => false,
            'category' => 'finance',
        ],
        'wholesale_price' => [
            'label' => 'Prix de gros',
            'description' => 'Activer les prix de gros et grilles tarifaires.',
            'default' => true,
            'category' => 'sales',
        ],
        'promotions' => [
            'label' => 'Promotions',
            'description' => 'Activer la gestion des promotions et coupons.',
            'default' => true,
            'category' => 'sales',
        ],
        'cash_register' => [
            'label' => 'Caisse',
            'description' => 'Activer la gestion des caisses enregistreuses.',
            'default' => true,
            'category' => 'finance',
        ],
        'inventory_validation' => [
            'label' => 'Inventaire avec validation',
            'description' => 'Activer le workflow de validation pour les inventaires.',
            'default' => true,
            'category' => 'inventory',
        ],
        'negative_stock' => [
            'label' => 'Stock négatif autorisé',
            'description' => 'Autoriser les stocks négatifs (déconseillé).',
            'default' => false,
            'category' => 'inventory',
        ],
        'sale_without_customer' => [
            'label' => 'Vente sans client',
            'description' => 'Permettre les ventes sans sélectionner un client.',
            'default' => true,
            'category' => 'sales',
        ],
        'require_full_payment' => [
            'label' => 'Obligation de paiement complet',
            'description' => 'Exiger le paiement intégral pour finaliser une vente.',
            'default' => false,
            'category' => 'sales',
        ],
        'require_daily_closure' => [
            'label' => 'Clôture quotidienne de caisse obligatoire',
            'description' => 'Obliger la clôture de caisse en fin de journée.',
            'default' => false,
            'category' => 'finance',
        ],
    ];

    public function all(?Company $company = null): array
    {
        $company ??= Company::current();
        $saved = $company?->features ?? [];
        $result = [];

        foreach ($this->definitions as $key => $def) {
            $result[$key] = [
                'key' => $key,
                'enabled' => $saved[$key] ?? $def['default'],
                'label' => $def['label'],
                'description' => $def['description'],
                'default' => $def['default'],
                'category' => $def['category'],
            ];
        }

        return $result;
    }

    public function isEnabled(string $key, ?Company $company = null): bool
    {
        $company ??= Company::current();
        if (!$company) return $this->definitions[$key]['default'] ?? false;

        $saved = $company->features ?? [];

        return $saved[$key] ?? $this->definitions[$key]['default'] ?? false;
    }

    public function getDefinition(string $key): ?array
    {
        return $this->definitions[$key] ?? null;
    }

    public function categories(): array
    {
        return [
            'inventory' => 'Stock & Entrepôts',
            'sales' => 'Ventes',
            'finance' => 'Finance & Caisse',
        ];
    }

    public function defaults(): array
    {
        $result = [];
        foreach ($this->definitions as $key => $def) {
            $result[$key] = $def['default'];
        }
        return $result;
    }

    public function save(array $features, Company $company): void
    {
        $allowed = array_keys($this->definitions);

        $filtered = [];
        foreach ($features as $key => $value) {
            if (in_array($key, $allowed)) {
                $filtered[$key] = (bool) $value;
            }
        }

        $company->update(['features' => $filtered]);

        if (isset($filtered['multi_currency'])) {
            $company->update(['enable_multi_currency' => $filtered['multi_currency']]);
        }
    }
}
