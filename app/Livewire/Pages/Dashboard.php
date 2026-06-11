<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Store;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalProducts = 0;

    public $totalStores = 0;

    public $lowStockProducts = 0;

    public $outOfStockProducts = 0;

    public function mount()
    {
        $companyId = auth()->user()->company_id;

        $this->totalProducts = Product::where('company_id', $companyId)->count();
        $this->totalStores = Store::where('company_id', $companyId)->count();
        $this->lowStockProducts = Product::where('company_id', $companyId)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();
        $this->outOfStockProducts = Product::where('company_id', $companyId)
            ->whereColumn('stock_quantity', '<=', 0)
            ->count();
    }

    public function render()
    {
        return view('livewire.pages.dashboard')
            ->layout('layouts.app', ['header' => 'Tableau de bord']);
    }
}
