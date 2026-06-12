<?php

namespace App\Livewire\Pages\Exports;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Export de données')]
class Index extends Component
{
    public array $exportTypes = [];

    public function mount()
    {
        $this->exportTypes = [
            'products'  => ['label' => 'Produits',       'icon' => '📦', 'description' => 'Tous les produits avec prix et stocks'],
            'customers' => ['label' => 'Clients',         'icon' => '👥', 'description' => 'Liste complète des clients'],
            'suppliers' => ['label' => 'Fournisseurs',    'icon' => '🏭', 'description' => 'Tous les fournisseurs enregistrés'],
            'stock'     => ['label' => 'Stocks',          'icon' => '📊', 'description' => 'État des stocks avec valeurs'],
            'sales'     => ['label' => 'Ventes',          'icon' => '💰', 'description' => 'Factures de vente'],
            'purchases' => ['label' => 'Achats',          'icon' => '📥', 'description' => 'Factures d\'achat'],
            'invoices'  => ['label' => 'Factures',        'icon' => '🧾', 'description' => 'Toutes les factures (ventes + achats)'],
            'reports'   => ['label' => 'Rapports',        'icon' => '📈', 'description' => 'Exportez depuis la page Rapports →'],
            'audit'     => ['label' => 'Journal d\'audit', 'icon' => '📋', 'description' => 'Traçabilité des actions'],
        ];
    }

    public function render()
    {
        return view('livewire.pages.exports.index')
            ->layout('components.layouts.app');
    }
}
