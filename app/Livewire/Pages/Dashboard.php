<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];

    public array $topProducts = [];

    public array $lowStockList = [];

    public array $outOfStockList = [];

    public array $salesChart = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $caToday = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', $today)
            ->sum('total');

        $caMonth = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', $monthStart)
            ->sum('total');

        $totalSales = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->count();

        $avgBasket = $totalSales > 0
            ? Sale::where('company_id', $companyId)->where('status', 'completed')->avg('total')
            : 0;

        $stockValue = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->select(DB::raw('COALESCE(SUM(stock_quantity * purchase_price), 0) as total_value'))
            ->value('total_value');

        $totalProducts = Product::where('company_id', $companyId)->count();

        $this->stats = [
            'ca_today' => $caToday,
            'ca_month' => $caMonth,
            'total_sales' => $totalSales,
            'avg_basket' => $avgBasket,
            'stock_value' => $stockValue,
            'total_products' => $totalProducts,
        ];

        $this->topProducts = SaleItem::whereHas('sale', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('status', 'completed');
        })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get()
            ->toArray();

        $this->lowStockList = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get()
            ->toArray();

        $this->outOfStockList = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)
            ->orderBy('name')
            ->take(5)
            ->get()
            ->toArray();

        $chartData = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', now()->subDays(13)->startOfDay())
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = collect();
        for ($i = 13; $i >= 0; $i--) {
            $dates->put(now()->subDays($i)->format('Y-m-d'), ['total' => 0, 'count' => 0]);
        }

        foreach ($chartData as $row) {
            $dates[$row->date] = ['total' => (float) $row->total, 'count' => (int) $row->count];
        }

        $this->salesChart = $dates->toArray();
    }

    public function render()
    {
        return view('livewire.pages.dashboard')
            ->layout('layouts.app', ['header' => 'Tableau de bord']);
    }
}
