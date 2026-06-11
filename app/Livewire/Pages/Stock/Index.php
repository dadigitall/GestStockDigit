<?php

namespace App\Livewire\Pages\Stock;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $filterStore = '';

    public $filterStatus = '';

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $products = Product::where('company_id', $companyId)
            ->with(['category', 'supplier'])
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatus, function ($q) {
                match ($this->filterStatus) {
                    'low' => $q->where('min_stock', '>', 0)->whereColumn('stock_quantity', '<=', 'min_stock'),
                    'out' => $q->where(function ($sq) {
                        $sq->whereNull('stock_quantity')->orWhere('stock_quantity', '<=', 0);
                    }),
                    'available' => $q->where('stock_quantity', '>', 0),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.pages.stock.index', compact('products'))
            ->layout('layouts.app', ['header' => 'Gestion des stocks']);
    }
}
