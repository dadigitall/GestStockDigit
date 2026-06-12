<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\CustomerReturn;
use App\Models\PurchaseOrder;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];

    public array $topProducts = [];

    public array $flopProducts = [];

    public array $topCustomers = [];

    public array $salesByStore = [];

    public array $salesByUser = [];

    public array $lowStockList = [];

    public array $outOfStockList = [];

    public array $expiringSoon = [];

    public array $salesChart = [];

    public array $alerts = [];

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

        $saleIdsCompleted = Sale::where('company_id', $companyId)
            ->where('status', 'completed')->pluck('id');

        $totalCostOfGoods = SaleItem::whereIn('sale_id', $saleIdsCompleted)
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(DB::raw('COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as total_cost'))
            ->value('total_cost');

        $totalRevenue = Sale::where('company_id', $companyId)
            ->where('status', 'completed')->sum('total');

        $grossProfit = $totalRevenue - $totalCostOfGoods;

        $avgBasket = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        $stockValue = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->select(DB::raw('COALESCE(SUM(stock_quantity * purchase_price), 0) as total_value'))
            ->value('total_value');

        $totalProducts = Product::where('company_id', $companyId)->count();

        $unpaidInvoices = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('amount_due', '>', 0)
            ->sum('amount_due');

        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->where('amount_due', '>', 0)
            ->where('due_date', '<', $today)
            ->sum('amount_due');

        $supplierDebts = Supplier::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->sum('balance');

        $pendingOrders = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'approved', 'ordered', 'partially_received'])
            ->count();

        $returnRate = 0;
        if ($totalSales > 0) {
            $returnCount = CustomerReturn::where('company_id', $companyId)->count();
            $returnRate = round(($returnCount / $totalSales) * 100, 1);
        }

        $outOfStockCount = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)
            ->count();

        $lowStockCount = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->count();

        $marginRate = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        $this->stats = [
            'ca_today' => $caToday,
            'ca_month' => $caMonth,
            'total_sales' => $totalSales,
            'avg_basket' => $avgBasket,
            'stock_value' => $stockValue,
            'total_products' => $totalProducts,
            'gross_profit' => $grossProfit,
            'margin_rate' => $marginRate,
            'unpaid_invoices' => $unpaidInvoices,
            'overdue_invoices' => $overdueInvoices,
            'supplier_debts' => $supplierDebts,
            'pending_orders' => $pendingOrders,
            'return_rate' => $returnRate,
            'out_of_stock_count' => $outOfStockCount,
            'low_stock_count' => $lowStockCount,
        ];

        $this->topProducts = SaleItem::whereHas('sale', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('status', 'completed');
        })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get()
            ->toArray();

        $this->flopProducts = SaleItem::whereHas('sale', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('status', 'completed');
        })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderBy('total_revenue')
            ->take(5)
            ->get()
            ->toArray();

        $this->topCustomers = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->take(5)
            ->with('customer:id,name')
            ->get()
            ->toArray();

        $this->salesByStore = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', $monthStart)
            ->select('store_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('store_id')
            ->with('store:id,name')
            ->get()
            ->toArray();

        $this->salesByUser = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', $monthStart)
            ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('user_id')
            ->with('user:id,name,first_name,last_name')
            ->get()
            ->toArray();

        $this->lowStockList = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->take(10)
            ->get()
            ->toArray();

        $this->outOfStockList = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)
            ->orderBy('name')
            ->take(10)
            ->get()
            ->toArray();

        $this->expiringSoon = DB::table('lots')
            ->join('products', 'lots.product_id', '=', 'products.id')
            ->where('lots.company_id', $companyId)
            ->where('lots.remaining_quantity', '>', 0)
            ->where('lots.expiry_date', '>=', $today)
            ->where('lots.expiry_date', '<=', now()->addDays(30))
            ->select('lots.*', 'products.name as product_name', 'products.reference')
            ->orderBy('lots.expiry_date')
            ->take(10)
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

        $this->alerts = [];
        if ($outOfStockCount > 0) {
            $this->alerts[] = ['type' => 'danger', 'message' => "{$outOfStockCount} produit(s) en rupture de stock."];
        }
        if ($lowStockCount > 0) {
            $this->alerts[] = ['type' => 'warning', 'message' => "{$lowStockCount} produit(s) en stock bas."];
        }
        if ($overdueInvoices > 0) {
            $this->alerts[] = ['type' => 'danger', 'message' => number_format($overdueInvoices, 0, ',', ' ') . " F de factures en retard."];
        }
        if ($supplierDebts > 0) {
            $this->alerts[] = ['type' => 'info', 'message' => number_format($supplierDebts, 0, ',', ' ') . " F de dettes fournisseurs."];
        }
        if ($pendingOrders > 0) {
            $this->alerts[] = ['type' => 'info', 'message' => "{$pendingOrders} commande(s) fournisseur en attente."];
        }
        if (count($this->expiringSoon) > 0) {
            $this->alerts[] = ['type' => 'warning', 'message' => count($this->expiringSoon) . " lot(s) proche(s) d'expiration."];
        }
    }

    public function render()
    {
        return view('livewire.pages.dashboard')
            ->layout('layouts.app', ['header' => 'Tableau de bord']);
    }
}
