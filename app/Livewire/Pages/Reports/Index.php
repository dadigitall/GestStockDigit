<?php

namespace App\Livewire\Pages\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\CustomerReturn;
use App\Models\PurchaseOrder;
use App\Models\SupplierReturn;
use App\Models\StockLoss;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\CashMovement;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    public string $tab = 'sales';

    public string $period = 'month';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?int $storeId = null;

    public ?int $userId = null;

    public ?int $categoryId = null;

    public ?int $productId = null;

    public ?int $supplierId = null;

    public array $stores = [];

    public array $users = [];

    public array $categories = [];

    public array $salesSummary = [];

    public array $salesByProduct = [];

    public array $salesByCategory = [];

    public array $salesByStore = [];

    public array $salesByUser = [];

    public array $salesByCustomer = [];

    public array $salesByType = [];

    public array $cancelledSales = [];

    public array $returns = [];

    public array $marginByProduct = [];

    public array $purchaseBySupplier = [];

    public array $purchaseByPeriod = [];

    public array $pendingOrders = [];

    public array $supplierDebts = [];

    public array $purchaseCostEvolution = [];

    public array $supplierPerformance = [];

    public array $stockState = [];

    public array $stockByLocation = [];

    public array $stockMinReached = [];

    public array $stockDormant = [];

    public array $stockOut = [];

    public array $stockMovements = [];

    public array $inventoryReport = [];

    public array $inventoryDiscrepancies = [];

    public array $expiredProducts = [];

    public array $expiringProducts = [];

    public array $stockRotation = [];

    public array $cashIn = [];

    public array $cashOut = [];

    public array $customerReceivables = [];

    public array $creditSales = [];

    public array $paymentsReceived = [];

    public array $latePayments = [];

    public array $cashSummary = [];

    public array $expenses = [];

    public array $abcAnalysis = [];

    public array $seasonality = [];

    public array $returnRate = [];

    public array $stockoutRate = [];

    public array $contributionByStore = [];

    public array $stockoutForecast = [];

    public array $previousPeriod = [];

    public array $currentPeriod = [];

    public function mount(): void
    {
        $this->stores = Store::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->pluck('name', 'id')->toArray();

        $this->users = \App\Models\User::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->select(DB::raw("id, CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,name)) as full_name"))
            ->pluck('full_name', 'id')->toArray();

        $this->categories = \App\Models\Category::where('company_id', auth()->user()->company_id)
            ->pluck('name', 'id')->toArray();

        $this->applyPeriod();

        $this->loadData();
    }

    public function applyPeriod(): void
    {
        if ($this->period === 'today') {
            $this->dateFrom = now()->startOfDay()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->period === 'yesterday') {
            $this->dateFrom = now()->subDay()->startOfDay()->format('Y-m-d');
            $this->dateTo = now()->subDay()->format('Y-m-d');
        } elseif ($this->period === 'week') {
            $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->period === 'month') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->period === 'quarter') {
            $this->dateFrom = now()->startOfQuarter()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->period === 'year') {
            $this->dateFrom = now()->startOfYear()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->period === 'custom' && !$this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
        $this->loadData();
    }

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom) {
            $this->period = 'custom';
        }
        $this->loadData();
    }

    public function updatedDateTo(): void
    {
        if ($this->dateTo) {
            $this->period = 'custom';
        }
        $this->loadData();
    }

    public function updatedStoreId(): void
    {
        $this->loadData();
    }

    public function updatedUserId(): void
    {
        $this->loadData();
    }

    public function updatedCategoryId(): void
    {
        $this->loadData();
    }

    public function updatedProductId(): void
    {
        $this->loadData();
    }

    public function updatedSupplierId(): void
    {
        $this->loadData();
    }

    public function changeTab(string $tab): void
    {
        $this->tab = $tab;
        $this->loadData();
    }

    protected function dateCondition(string $field = 'sold_at'): array
    {
        $from = $this->dateFrom ? $this->dateFrom . ' 00:00:00' : now()->startOfMonth()->format('Y-m-d 00:00:00');
        $to = $this->dateTo ? $this->dateTo . ' 23:59:59' : now()->format('Y-m-d 23:59:59');
        return [$from, $to];
    }

    public function loadData(): void
    {
        $companyId = auth()->user()->company_id;
        [$from, $to] = $this->dateCondition();

        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        switch ($this->tab) {
            case 'sales':
                $this->loadSalesData($companyId, $from, $to);
                break;
            case 'stock':
                $this->loadStockData($companyId, $from, $to);
                break;
            case 'purchases':
                $this->loadPurchaseData($companyId, $from, $to);
                break;
            case 'financial':
                $this->loadFinancialData($companyId, $from, $to);
                break;
            case 'analysis':
                $this->loadAnalysisData($companyId, $from, $to);
                break;
        }
    }

    protected function loadSalesData(int $companyId, string $from, string $to): void
    {
        $completed = ['completed'];

        $salesQuery = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);
        if ($this->storeId) $salesQuery->where('store_id', $this->storeId);
        if ($this->userId) $salesQuery->where('user_id', $this->userId);
        $sales = $salesQuery->get();
        $saleIds = $sales->pluck('id');

        $this->salesSummary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_subtotal' => $sales->sum('subtotal'),
            'total_discount' => $sales->sum('discount'),
            'total_tax' => $sales->sum('tax_amount'),
            'avg_basket' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'total_cost' => 0,
            'gross_margin' => 0,
        ];

        $costData = SaleItem::whereIn('sale_id', $saleIds)
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(DB::raw('COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as total_cost'))
            ->value('total_cost');
        $this->salesSummary['total_cost'] = (float) $costData;
        $this->salesSummary['gross_margin'] = $this->salesSummary['total_revenue'] - (float) $costData;
        $this->salesSummary['margin_rate'] = $this->salesSummary['total_revenue'] > 0
            ? round(($this->salesSummary['gross_margin'] / $this->salesSummary['total_revenue']) * 100, 1) : 0;

        $itemsQuery = SaleItem::whereIn('sale_id', $saleIds);
        $this->salesByProduct = (clone $itemsQuery)
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->take(50)
            ->get()
            ->toArray();

        $this->salesByCategory = SaleItem::whereIn('sale_id', $saleIds)
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(sale_items.quantity) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();

        $this->salesByStore = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->select('store_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('store_id')->with('store:id,name')
            ->get()->toArray();

        $this->salesByUser = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('user_id')->with('user:id,name,first_name,last_name')
            ->get()->toArray();

        $this->salesByCustomer = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('customer_id')->with('customer:id,name')
            ->orderByDesc('amount')->take(20)
            ->get()->toArray();

        $this->salesByType = Sale::where('company_id', $companyId)->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->select('type', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('type')->get()->toArray();

        $this->cancelledSales = Sale::where('company_id', $companyId)->where('status', 'cancelled')
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->select(DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->first()->toArray();

        $this->returns = CustomerReturn::where('company_id', $companyId)
            ->where('created_at', '>=', $from)->where('created_at', '<=', $to)
            ->with('customer:id,name')
            ->select('id', 'reference', 'customer_id', 'refund_amount', 'margin_impact', 'return_type', 'reason', 'created_at')
            ->orderByDesc('created_at')->take(50)
            ->get()->toArray();

        $this->marginByProduct = SaleItem::whereIn('sale_id', $saleIds)
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('sale_items.product_name',
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as cost'),
                DB::raw('SUM(sale_items.quantity * (sale_items.unit_price - products.purchase_price)) as margin'))
            ->groupBy('sale_items.product_name')
            ->havingRaw('revenue > 0')
            ->orderByDesc('margin')->take(50)
            ->get()->toArray();

        $totalSales = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)->count();

        $totalCustomers = Sale::where('company_id', $companyId)->whereIn('status', $completed)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');

        $this->salesSummary['purchase_frequency'] = $totalCustomers > 0
            ? round($totalSales / $totalCustomers, 1) : 0;
    }

    protected function loadStockData(int $companyId, string $from, string $to): void
    {
        $q = Product::where('company_id', $companyId)->where('is_stockable', true);
        if ($this->categoryId) $q->where('category_id', $this->categoryId);

        $this->stockState = $q->select('id', 'name', 'reference', 'stock_quantity', 'min_stock', 'purchase_price', 'sale_price')
            ->orderBy('name')->get()->toArray();

        $stockValue = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->select(DB::raw('SUM(stock_quantity * purchase_price) as total_value'))->value('total_value');
        $totalQty = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->sum('stock_quantity');

        $this->stockState['_summary'] = [
            'total_value' => (float) ($stockValue ?? 0),
            'total_qty' => (float) ($totalQty ?? 0),
            'total_products' => count($this->stockState),
        ];

        $storeIds = $this->storeId ? [$this->storeId] : Store::where('company_id', $companyId)->where('is_active', true)->pluck('id')->toArray();
        $this->stockByLocation = DB::table('product_store')
            ->join('stores', 'product_store.store_id', '=', 'stores.id')
            ->join('products', 'product_store.product_id', '=', 'products.id')
            ->whereIn('product_store.store_id', $storeIds)
            ->where('products.is_stockable', true)
            ->select('stores.name as store_name',
                DB::raw('COUNT(DISTINCT product_store.product_id) as products_count'),
                DB::raw('SUM(product_store.stock_quantity) as total_qty'),
                DB::raw('SUM(product_store.stock_quantity * products.purchase_price) as total_value'))
            ->groupBy('stores.name')
            ->get()->toArray();

        $this->stockMinReached = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('min_stock', '>', 0)->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'name', 'reference', 'stock_quantity', 'min_stock')
            ->orderBy('stock_quantity')->take(100)->get()->toArray();

        $this->stockOut = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)
            ->select('id', 'name', 'reference', 'stock_quantity')
            ->orderBy('name')->take(100)->get()->toArray();

        $this->stockDormant = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('stock_quantity', '>', 0)
            ->whereNotIn('id', function ($q) use ($companyId) {
                $q->select('product_id')->from('sale_items')
                    ->whereIn('sale_id', function ($q2) use ($companyId) {
                        $q2->select('id')->from('sales')
                            ->where('company_id', $companyId)
                            ->where('status', 'completed')
                            ->where('sold_at', '>=', now()->subMonths(3));
                    });
            })->select('id', 'name', 'reference', 'stock_quantity', 'purchase_price')
            ->take(100)->get()->toArray();

        $movementsQuery = StockMovement::where('company_id', $companyId)
            ->where('created_at', '>=', $from)->where('created_at', '<=', $to);
        if ($this->storeId) $movementsQuery->where('store_id', $this->storeId);
        if ($this->productId) $movementsQuery->where('product_id', $this->productId);
        $this->stockMovements = $movementsQuery->with('product:id,name', 'store:id,name', 'user:id,name,first_name,last_name')
            ->orderByDesc('created_at')->take(100)
            ->get()->toArray();

        $this->inventoryReport = Inventory::where('company_id', $companyId)
            ->where('created_at', '>=', $from)->where('created_at', '<=', $to)
            ->with('store:id,name')
            ->orderByDesc('created_at')->take(20)
            ->get()->toArray();

        $totalInvItems = InventoryItem::whereIn('inventory_id', Inventory::where('company_id', $companyId)->pluck('id'))
            ->where('discrepancy_quantity', '!=', 0)->count();
        $totalInvValue = InventoryItem::whereIn('inventory_id', Inventory::where('company_id', $companyId)->pluck('id'))
            ->sum('discrepancy_value');
        $this->inventoryDiscrepancies = [
            'total_items' => $totalInvItems,
            'total_value' => (float) ($totalInvValue ?? 0),
        ];

        $this->expiredProducts = DB::table('lots')
            ->join('products', 'lots.product_id', '=', 'products.id')
            ->where('lots.company_id', $companyId)
            ->where('lots.remaining_quantity', '>', 0)
            ->where('lots.expiry_date', '<', now())
            ->select('lots.*', 'products.name as product_name', 'products.reference')
            ->orderBy('lots.expiry_date')->take(50)
            ->get()->toArray();

        $this->expiringProducts = DB::table('lots')
            ->join('products', 'lots.product_id', '=', 'products.id')
            ->where('lots.company_id', $companyId)
            ->where('lots.remaining_quantity', '>', 0)
            ->where('lots.expiry_date', '>=', now())
            ->where('lots.expiry_date', '<=', now()->addDays(30))
            ->select('lots.*', 'products.name as product_name', 'products.reference')
            ->orderBy('lots.expiry_date')->take(50)
            ->get()->toArray();

        $this->stockRotation = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('stock_quantity', '>', 0)
            ->select('id', 'name', 'reference', 'stock_quantity', 'purchase_price')
            ->selectSub(function ($q) use ($companyId) {
                $q->select(DB::raw('COALESCE(SUM(sale_items.quantity), 0)'))
                    ->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereColumn('sale_items.product_id', 'products.id')
                    ->where('sales.company_id', $companyId)
                    ->where('sales.status', 'completed')
                    ->where('sales.sold_at', '>=', now()->subMonths(3));
            }, 'sold_qty_3m')
            ->having('stock_quantity', '>', 0)
            ->orderBy('sold_qty_3m')->take(50)
            ->get()->toArray();
    }

    protected function loadPurchaseData(int $companyId, string $from, string $to): void
    {
        $poQuery = PurchaseOrder::where('company_id', $companyId)
            ->where('created_at', '>=', $from)->where('created_at', '<=', $to);
        if ($this->storeId) $poQuery->where('store_id', $this->storeId);
        if ($this->supplierId) $poQuery->where('supplier_id', $this->supplierId);

        $orders = $poQuery->get();
        $orderIds = $orders->pluck('id');

        $this->purchaseBySupplier = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('id', $orderIds)
            ->select('supplier_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_amount'))
            ->groupBy('supplier_id')->with('supplier:id,name')
            ->get()->toArray();

        $this->purchaseByPeriod = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('id', $orderIds)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
                DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_amount'))
            ->groupBy('period')->orderBy('period')
            ->get()->toArray();

        $this->pendingOrders = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'approved', 'ordered', 'partially_received'])
            ->with('supplier:id,name', 'store:id,name')
            ->orderBy('created_at')->take(50)
            ->get()->toArray();

        $receipts = GoodsReceipt::where('company_id', $companyId)
            ->whereIn('purchase_order_id', $orderIds)
            ->get();
        $partialCount = $receipts->where('status', 'partial')->count();
        $fullCount = $receipts->where('status', 'received')->count();

        $this->pendingOrders['_summary'] = [
            'partial_receptions' => $partialCount,
            'full_receptions' => $fullCount,
            'total_receptions' => $receipts->count(),
        ];

        $this->supplierDebts = Supplier::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->select('id', 'name', 'balance')
            ->orderByDesc('balance')->take(50)
            ->get()->toArray();

        $this->purchaseCostEvolution = PurchaseOrder::where('company_id', $companyId)
            ->where('status', 'received')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
                DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(*) as total_orders'))
            ->groupBy('period')->orderBy('period')
            ->get()->toArray();

        $this->supplierPerformance = DB::table('supplier_evaluations')
            ->join('suppliers', 'supplier_evaluations.supplier_id', '=', 'suppliers.id')
            ->where('suppliers.company_id', $companyId)
            ->select('suppliers.name',
                DB::raw('AVG(respect_delays) as avg_delay_score'),
                DB::raw('AVG(product_quality) as avg_quality'),
                DB::raw('AVG(overall_rating) as avg_rating'),
                DB::raw('COUNT(*) as eval_count'))
            ->groupBy('suppliers.name')
            ->get()->toArray();
    }

    protected function loadFinancialData(int $companyId, string $from, string $to): void
    {
        $movementsQuery = CashMovement::where('company_id', $companyId)
            ->where('movement_date', '>=', $from)->where('movement_date', '<=', $to);
        if ($this->storeId) $movementsQuery->where('store_id', $this->storeId);

        $allMovements = $movementsQuery->get();

        $this->cashIn = (clone $allMovements)->where('direction', 'in')->sum('amount');
        $this->cashOut = (clone $allMovements)->where('direction', 'out')->sum('amount');
        $this->cashSummary = [
            'total_in' => (float) $this->cashIn,
            'total_out' => (float) $this->cashOut,
            'balance' => (float) $this->cashIn - (float) $this->cashOut,
            'cash_count' => (clone $allMovements)->where('payment_method', 'cash')->where('direction', 'in')->sum('amount'),
            'mobile_count' => (clone $allMovements)->where('payment_method', 'mobile_money')->where('direction', 'in')->sum('amount'),
            'card_count' => (clone $allMovements)->where('payment_method', 'card')->where('direction', 'in')->sum('amount'),
        ];

        $this->expenses = (clone $allMovements)->whereIn('type', ['internal_expense', 'supplier_payment'])
            ->values()->toArray();

        $this->customerReceivables = Customer::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->select('id', 'name', 'balance', 'credit_limit')
            ->orderByDesc('balance')->take(50)
            ->get()->toArray();

        $this->creditSales = Sale::where('company_id', $companyId)
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->whereIn('status', ['completed', 'credit'])
            ->whereRaw('paid_amount < total')
            ->with('customer:id,name')
            ->select('id', 'reference', 'customer_id', 'total', 'paid_amount', DB::raw('(total - paid_amount) as due'), 'sold_at')
            ->orderByDesc('due')->take(50)
            ->get()->toArray();

        $this->paymentsReceived = \App\Models\CustomerPayment::where('company_id', $companyId)
            ->where('payment_date', '>=', $from)->where('payment_date', '<=', $to)
            ->with('customer:id,name')
            ->select('id', 'customer_id', 'amount', 'payment_method', 'payment_date', 'reference')
            ->orderByDesc('payment_date')->take(50)
            ->get()->toArray();

        $this->latePayments = \App\Models\PaymentSchedule::where('company_id', $companyId)
            ->where('status', 'overdue')
            ->where('due_date', '<', now())
            ->with('sale:id,reference,total', 'customer:id,name')
            ->orderBy('due_date')->take(50)
            ->get()->toArray();

        $totalRevenue = Sale::where('company_id', $companyId)->whereIn('status', ['completed'])
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)->sum('total');
        $totalCost = SaleItem::whereIn('sale_id', function ($q) use ($companyId, $from, $to) {
            $q->select('id')->from('sales')
                ->where('company_id', $companyId)
                ->whereIn('status', ['completed'])
                ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);
        })->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(sale_items.quantity * products.purchase_price) as cost'))->value('cost');

        $this->cashSummary['gross_profit'] = $totalRevenue - (float) ($totalCost ?? 0);
        $this->cashSummary['gross_margin_pct'] = $totalRevenue > 0
            ? round(($this->cashSummary['gross_profit'] / $totalRevenue) * 100, 1) : 0;
        $this->cashSummary['total_revenue'] = $totalRevenue;
    }

    protected function loadAnalysisData(int $companyId, string $from, string $to): void
    {
        $currentStart = \Carbon\Carbon::parse($from);
        $currentEnd = \Carbon\Carbon::parse($to);
        $diffDays = $currentStart->diffInDays($currentEnd) + 1;
        $prevStart = (clone $currentStart)->subDays($diffDays);
        $prevEnd = (clone $currentStart)->subDay();

        $currSales = Sale::where('company_id', $companyId)->whereIn('status', ['completed'])
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);

        $prevSales = Sale::where('company_id', $companyId)->whereIn('status', ['completed'])
            ->where('sold_at', '>=', $prevStart)->where('sold_at', '<=', $prevEnd);

        if ($this->storeId) {
            $currSales->where('store_id', $this->storeId);
            $prevSales->where('store_id', $this->storeId);
        }

        $currData = $currSales->get();
        $prevData = $prevSales->get();

        $this->currentPeriod = [
            'revenue' => $currData->sum('total'),
            'count' => $currData->count(),
            'avg' => $currData->count() > 0 ? $currData->sum('total') / $currData->count() : 0,
        ];

        $this->previousPeriod = [
            'revenue' => $prevData->sum('total'),
            'count' => $prevData->count(),
            'avg' => $prevData->count() > 0 ? $prevData->sum('total') / $prevData->count() : 0,
        ];

        $topQuery = SaleItem::whereHas('sale', function ($q) use ($companyId, $from, $to) {
            $q->where('company_id', $companyId)->whereIn('status', ['completed'])
                ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);
            if ($this->storeId) $q->where('store_id', $this->storeId);
        });
        $allItems = (clone $topQuery)->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')->orderByDesc('total_revenue')->get();

        $this->abcAnalysis = [];
        $totalRev = $allItems->sum('total_revenue');
        $cumulative = 0;
        foreach ($allItems as $item) {
            $cumulative += $item->total_revenue;
            $pct = $totalRev > 0 ? ($cumulative / $totalRev) * 100 : 0;
            $class = 'C';
            if ($pct <= 70) $class = 'A';
            elseif ($pct <= 90) $class = 'B';
            $this->abcAnalysis[] = [
                'product_name' => $item->product_name,
                'total_qty' => (int) $item->total_qty,
                'total_revenue' => (float) $item->total_revenue,
                'percent' => $totalRev > 0 ? round(($item->total_revenue / $totalRev) * 100, 1) : 0,
                'cumulative' => round($pct, 1),
                'class' => $class,
            ];
        }
        $this->abcAnalysis = array_slice($this->abcAnalysis, 0, 50);

        $this->seasonality = Sale::where('company_id', $companyId)->whereIn('status', ['completed'])
            ->where('sold_at', '>=', now()->subMonths(12))
            ->select(DB::raw("DATE_FORMAT(sold_at, '%Y-%m') as period"),
                DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('period')->orderBy('period')
            ->get()->toArray();

        $storeIds = $this->storeId ? [$this->storeId] : Store::where('company_id', $companyId)->where('is_active', true)->pluck('id');
        $this->returnRate = [];
        foreach ($storeIds as $sid) {
            $sTotal = Sale::where('company_id', $companyId)->where('store_id', $sid)
                ->whereIn('status', ['completed'])->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)->count();
            $sReturns = CustomerReturn::where('company_id', $companyId)->where('store_id', $sid)
                ->where('created_at', '>=', $from)->where('created_at', '<=', $to)->count();
            $store = Store::find($sid);
            $this->returnRate[] = [
                'store_name' => $store?->name ?? 'N/A',
                'sales' => $sTotal,
                'returns' => $sReturns,
                'rate' => $sTotal > 0 ? round(($sReturns / $sTotal) * 100, 1) : 0,
            ];
        }

        $totalStockable = Product::where('company_id', $companyId)->where('is_stockable', true)->count();
        $totalOutOfStock = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)->count();
        $this->stockoutRate = [
            'total_products' => $totalStockable,
            'out_of_stock' => $totalOutOfStock,
            'rate' => $totalStockable > 0 ? round(($totalOutOfStock / $totalStockable) * 100, 1) : 0,
        ];

        $this->contributionByStore = Sale::where('company_id', $companyId)->whereIn('status', ['completed'])
            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to)
            ->select('store_id', DB::raw('SUM(total) as amount'))
            ->groupBy('store_id')->with('store:id,name')
            ->get()->toArray();

        $this->stockoutForecast = Product::where('company_id', $companyId)->where('is_stockable', true)
            ->where('stock_quantity', '>', 0)
            ->where('min_stock', '>', 0)
            ->select('id', 'name', 'reference', 'stock_quantity', 'min_stock')
            ->selectSub(function ($q) use ($companyId) {
                $q->select(DB::raw('COALESCE(SUM(sale_items.quantity), 0)'))
                    ->from('sale_items')->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereColumn('sale_items.product_id', 'products.id')
                    ->where('sales.company_id', $companyId)
                    ->where('sales.status', 'completed')
                    ->where('sales.sold_at', '>=', now()->subDays(30));
            }, 'sold_qty_30d')
            ->having('sold_qty_30d', '>', 0)
            ->get()
            ->map(function ($p) {
                $dailyRate = $p->sold_qty_30d / 30;
                $daysUntilOut = $dailyRate > 0 ? floor($p->stock_quantity / $dailyRate) : 999;
                $daysUntilMin = $dailyRate > 0 && $p->min_stock > 0
                    ? floor(($p->stock_quantity - $p->min_stock) / $dailyRate)
                    : 999;
                return [
                    'name' => $p->name,
                    'reference' => $p->reference,
                    'stock_quantity' => (float) $p->stock_quantity,
                    'min_stock' => (float) ($p->min_stock ?? 0),
                    'daily_rate' => round($dailyRate, 2),
                    'days_until_out' => max(0, $daysUntilOut),
                    'days_until_min' => max(0, $daysUntilMin),
                    'critical' => $daysUntilMin <= 7 || $daysUntilOut <= 7,
                ];
            })
            ->sortBy('days_until_min')
            ->take(20)
            ->values()
            ->toArray();

        $this->marginByFamily = SaleItem::whereIn('sale_id', function ($q) use ($companyId, $from, $to) {
            $q->select('id')->from('sales')
                ->where('company_id', $companyId)->whereIn('status', ['completed'])
                ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);
        })->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name',
                DB::raw('SUM(sale_items.subtotal) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as cost'),
                DB::raw('SUM(sale_items.quantity * (sale_items.unit_price - products.purchase_price)) as margin'))
            ->groupBy('categories.name')
            ->havingRaw('revenue > 0')
            ->orderByDesc('margin')
            ->get()->toArray();
    }

    public function exportCsv(string $reportType): StreamedResponse
    {
        $companyId = auth()->user()->company_id;
        [$from, $to] = $this->dateCondition();
        $this->loadData();

        $cashIn = $this->cashIn;
        $cashOut = $this->cashOut;
        $currentPeriod = $this->currentPeriod;
        $previousPeriod = $this->previousPeriod;
        $stockoutRate = $this->stockoutRate;

        $filename = "rapport_{$reportType}_{$from}_{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($reportType, $companyId, $from, $to, $cashIn, $cashOut, $currentPeriod, $previousPeriod, $stockoutRate) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            switch ($reportType) {
                case 'sales':
                    fputcsv($handle, ['Produit', 'Quantité', 'Revenu']);
                    $items = SaleItem::whereIn('sale_id', function ($q) use ($companyId, $from, $to) {
                        $q->select('id')->from('sales')
                            ->where('company_id', $companyId)->whereIn('status', ['completed'])
                            ->where('sold_at', '>=', $from)->where('sold_at', '<=', $to);
                    })->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as rev'))
                        ->groupBy('product_name')->orderByDesc('rev')->get();
                    foreach ($items as $i) fputcsv($handle, [$i->product_name, (float) $i->qty, (float) $i->rev]);
                    break;

                case 'stock':
                    fputcsv($handle, ['Produit', 'Référence', 'Stock', 'Min', 'Prix achat', 'Prix vente']);
                    $products = Product::where('company_id', $companyId)->where('is_stockable', true)
                        ->orderBy('name')->get(['name', 'reference', 'stock_quantity', 'min_stock', 'purchase_price', 'sale_price']);
                    foreach ($products as $p) fputcsv($handle, [$p->name, $p->reference, (float) $p->stock_quantity, (float) $p->min_stock, (float) $p->purchase_price, (float) $p->sale_price]);
                    break;

                case 'purchases':
                    fputcsv($handle, ['Fournisseur', 'Commandes', 'Montant total']);
                    $data = PurchaseOrder::where('company_id', $companyId)
                        ->where('created_at', '>=', $from)->where('created_at', '<=', $to)
                        ->select('supplier_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
                        ->groupBy('supplier_id')->with('supplier:id,name')->get();
                    foreach ($data as $d) fputcsv($handle, [$d->supplier->name ?? 'N/A', $d->total, (float) $d->amount]);
                    break;

                case 'financial':
                    fputcsv($handle, ['Type', 'Montant']);
                    fputcsv($handle, ['Encaissements', $cashIn]);
                    fputcsv($handle, ['Décaissements', $cashOut]);
                    break;

                case 'analysis':
                    fputcsv($handle, ['Analyse', 'Valeur']);
                    fputcsv($handle, ['CA période courante', $currentPeriod['revenue'] ?? 0]);
                    fputcsv($handle, ['CA période précédente', $previousPeriod['revenue'] ?? 0]);
                    fputcsv($handle, ['Taux de rupture (%)', $stockoutRate['rate'] ?? 0]);
                    break;
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(string $reportType)
    {
        $this->loadData();
        $companyName = auth()->user()->company->name ?? 'Entreprise';
        $dateLabel = $this->dateFrom && $this->dateTo
            ? "du {$this->dateFrom} au {$this->dateTo}"
            : '';

        $data = [
            'companyName' => $companyName,
            'dateLabel' => $dateLabel,
            'reportType' => $reportType,
            'tab' => $this->tab,
        ];

        foreach (['salesSummary', 'salesByProduct', 'salesByCategory', 'salesByStore', 'salesByUser',
            'salesByCustomer', 'salesByType', 'cancelledSales', 'returns', 'marginByProduct',
            'stockState', 'stockByLocation', 'stockMinReached', 'stockOut', 'stockDormant',
            'stockMovements', 'inventoryReport', 'expiredProducts', 'expiringProducts', 'stockRotation',
            'purchaseBySupplier', 'purchaseByPeriod', 'pendingOrders', 'supplierDebts', 'purchaseCostEvolution', 'supplierPerformance',
            'cashSummary', 'customerReceivables', 'creditSales', 'paymentsReceived', 'latePayments', 'expenses',
            'abcAnalysis', 'seasonality', 'returnRate', 'stockoutRate', 'stockoutForecast', 'currentPeriod', 'previousPeriod',
            'marginByFamily', 'contributionByStore'] as $key) {
            $data[$key] = $this->$key ?? [];
        }

        $pdf = Pdf::loadView('livewire.pages.reports.export-pdf', $data);
        return $pdf->download("rapport_{$reportType}_{$this->dateFrom}_{$this->dateTo}.pdf");
    }

    public function exportExcel(string $reportType): StreamedResponse
    {
        $this->loadData();
        $companyName = auth()->user()->company->name ?? 'Entreprise';
        [$from, $to] = $this->dateCondition();

        $html = '<html xmlns:o="http://www.w3.org/TR/REC-html40" xmlns:x="urn:schemas-microsoft-com:office:excel">
            <head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Rapport</x:Name></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
            <style>td,th{padding:4px 8px;border:1px solid #ccc;font-size:11px;} th{background:#f0f0f0;font-weight:bold;}</style></head>
            <body><h2>Rapport ' . ucfirst($reportType) . '</h2>
            <p>' . $companyName . ' — ' . $from . ' au ' . $to . '</p>';

        if ($reportType === 'sales' && count($this->salesByProduct) > 0) {
            $html .= '<table><tr><th>Produit</th><th>Quantité</th><th>Revenu</th></tr>';
            foreach ($this->salesByProduct as $p) {
                $html .= '<tr><td>' . e($p['product_name']) . '</td><td>' . (int) $p['total_qty'] . '</td><td>' . number_format($p['total_revenue'], 0, ',', ' ') . '</td></tr>';
            }
            $html .= '</table>';
        }

        if ($reportType === 'stock' && count($this->stockState) > 0) {
            $html .= '<table><tr><th>Produit</th><th>Stock</th><th>Min</th><th>Prix achat</th></tr>';
            foreach ($this->stockState as $p) {
                if (isset($p['_summary'])) continue;
                $html .= '<tr><td>' . e($p['name'] ?? '') . '</td><td>' . number_format($p['stock_quantity'] ?? 0, 2, ',', ' ') . '</td><td>' . number_format($p['min_stock'] ?? 0, 2, ',', ' ') . '</td><td>' . number_format($p['purchase_price'] ?? 0, 0, ',', ' ') . '</td></tr>';
            }
            $html .= '</table>';
        }

        if ($reportType === 'purchases' && count($this->purchaseBySupplier) > 0) {
            $html .= '<table><tr><th>Fournisseur</th><th>Commandes</th><th>Montant</th></tr>';
            foreach ($this->purchaseBySupplier as $s) {
                $html .= '<tr><td>' . e($s['supplier']['name'] ?? 'N/A') . '</td><td>' . $s['total_orders'] . '</td><td>' . number_format($s['total_amount'], 0, ',', ' ') . '</td></tr>';
            }
            $html .= '</table>';
        }

        if ($reportType === 'financial') {
            $html .= '<table><tr><th>Indicateur</th><th>Valeur</th></tr>';
            $html .= '<tr><td>Encaissements</td><td>' . number_format($this->cashSummary['total_in'] ?? 0, 0, ',', ' ') . '</td></tr>';
            $html .= '<tr><td>Décaissements</td><td>' . number_format($this->cashSummary['total_out'] ?? 0, 0, ',', ' ') . '</td></tr>';
            $html .= '<tr><td>Bénéfice brut</td><td>' . number_format($this->cashSummary['gross_profit'] ?? 0, 0, ',', ' ') . '</td></tr>';
            $html .= '<tr><td>Marge brute</td><td>' . ($this->cashSummary['gross_margin_pct'] ?? 0) . '%</td></tr>';
            $html .= '</table>';
        }

        if ($reportType === 'analysis' && count($this->abcAnalysis) > 0) {
            $html .= '<h3>Analyse ABC</h3><table><tr><th>Classe</th><th>Produit</th><th>%</th><th>Cumul</th></tr>';
            foreach ($this->abcAnalysis as $a) {
                $html .= '<tr><td>' . $a['class'] . '</td><td>' . e($a['product_name']) . '</td><td>' . $a['percent'] . '%</td><td>' . $a['cumulative'] . '%</td></tr>';
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';

        return new StreamedResponse(function () use ($html) {
            echo $html;
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"rapport_{$reportType}_{$from}_{$to}.xls\"",
        ]);
    }

    public function render()
    {
        return view('livewire.pages.reports.index')
            ->layout('layouts.app', ['header' => 'Rapports et analyses']);
    }
}
