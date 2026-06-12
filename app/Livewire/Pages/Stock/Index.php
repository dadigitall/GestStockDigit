<?php

namespace App\Livewire\Pages\Stock;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Lot;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'stock';

    public string $search = '';

    public string $filterStore = '';

    public string $filterStatus = '';

    public string $filterCategory = '';

    public string $filterType = '';

    public string $filterDateFrom = '';

    public string $filterDateTo = '';

    public bool $showMovementForm = false;

    public string $movementType = 'adjustment_positive';

    public $movementProductId;

    public $movementStoreId;

    public $movementSourceStoreId;

    public $movementDestinationStoreId;

    public $movementQuantity;

    public string $movementNotes = '';

    public string $valuationMethod = 'cmp';

    public array $stores = [];

    public array $categories = [];

    public string $productSearch = '';

    public array $productResults = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->stores = Store::where('company_id', $companyId)->pluck('name', 'id')->toArray();
        $this->categories = Category::where('company_id', $companyId)->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // --- Stock global avec stock par magasin ---
        $products = Product::where('company_id', $companyId)
            ->with([
                'category', 'supplier',
                'lots' => fn ($q) => $q->where('remaining_quantity', '>', 0),
                'stores' => fn ($q) => $q->where('is_active', true),
            ])
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->when($this->filterStore, fn ($q) => $q->whereHas('stores', fn ($s) => $s->where('stores.id', $this->filterStore)))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus, fn ($q) => $this->applyStockFilter($q, $this->filterStatus))
            ->orderBy('name')
            ->paginate(20, ['*'], 'stockPage');

        // --- Mouvements ---
        $movements = StockMovement::where('company_id', $companyId)
            ->with(['product', 'store', 'sourceStore', 'destinationStore', 'user'])
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$this->search}%")))
            ->when($this->filterStore, fn ($q) => $q->where(function ($q) {
                $q->where('store_id', $this->filterStore)
                    ->orWhere('source_store_id', $this->filterStore)
                    ->orWhere('destination_store_id', $this->filterStore);
            }))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'movementsPage');

        // --- Valorisation ---
        $valuation = $this->computeValuation($companyId);

        // --- Alertes ---
        $alerts = $this->computeAlerts($companyId);

        // --- Stores pour stock par magasin ---
        $allStores = Store::where('company_id', $companyId)->where('allows_stock', true)->get();

        return view('livewire.pages.stock.index', compact(
            'products', 'movements', 'valuation', 'alerts', 'allStores'
        ))->layout('layouts.app', ['header' => 'Gestion des stocks']);
    }

    // --- 8.28 Stock filters ---
    private function applyStockFilter($q, $filter)
    {
        return match ($filter) {
            'available' => $q->where('stock_quantity', '>', 0),
            'low' => $q->where('min_stock', '>', 0)->whereColumn('stock_quantity', '<=', 'min_stock'),
            'out' => $q->where(function ($sq) {
                $sq->whereNull('stock_quantity')->orWhere('stock_quantity', '<=', 0);
            }),
            'overstock' => $q->where('max_stock', '>', 0)->whereColumn('stock_quantity', '>=', 'max_stock'),
            'expired' => $q->whereHas('lots', fn ($l) => $l->where('expiry_date', '<', now())),
            'expiring_soon' => $q->whereHas('lots', fn ($l) => $l->where('expiry_date', '>=', now())->where('expiry_date', '<=', now()->addDays(30))),
            default => $q,
        };
    }

    // --- 8.29 Movement management ---
    public function openMovementForm($type = 'adjustment_positive')
    {
        $this->resetMovementForm();
        $this->movementType = $type;
        $this->showMovementForm = true;
    }

    public function resetMovementForm()
    {
        $this->movementType = 'adjustment_positive';
        $this->movementProductId = null;
        $this->movementStoreId = null;
        $this->movementSourceStoreId = null;
        $this->movementDestinationStoreId = null;
        $this->movementQuantity = null;
        $this->movementNotes = '';
        $this->showMovementForm = false;
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];

            return;
        }
        $this->productResults = Product::where('company_id', auth()->user()->company_id)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('sku', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'stock_quantity', 'purchase_price'])
            ->toArray();
    }

    public function selectProduct($id)
    {
        $this->movementProductId = $id;
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function saveMovement()
    {
        $this->validate([
            'movementProductId' => 'required|exists:products,id',
            'movementType' => 'required|string',
            'movementQuantity' => 'required|numeric|min:0.01',
        ]);

        $needsStore = in_array($this->movementType, [
            'adjustment_positive', 'adjustment_negative', 'inventory',
            'breakage', 'loss', 'expiry', 'donation', 'sample', 'internal_consumption',
        ]);
        $needsSource = in_array($this->movementType, ['transfer_out']);
        $needsDest = in_array($this->movementType, ['transfer_in', 'transfer_out']);

        if ($needsStore) {
            $this->validate(['movementStoreId' => 'required|exists:stores,id']);
        }
        if ($needsSource) {
            $this->validate(['movementSourceStoreId' => 'required|exists:stores,id']);
        }
        if ($needsDest) {
            $this->validate(['movementDestinationStoreId' => 'required|exists:stores,id']);
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($this->movementProductId);
            $oldQty = $product->stock_quantity ?? 0;
            $qty = $this->movementQuantity;

            $isNegative = in_array($this->movementType, [
                'adjustment_negative', 'breakage', 'loss', 'expiry',
                'donation', 'sample', 'internal_consumption', 'transfer_out',
            ]);
            // inventory can be positive or negative
            $invSigned = $this->movementType === 'inventory' ? ($qty >= 0 ? $qty : -$qty) : $qty;

            $newQty = $isNegative ? max(0, $oldQty - $qty) : $oldQty + $invSigned;
            $appliedQty = $isNegative ? -$qty : $invSigned;

            $product->update(['stock_quantity' => $newQty]);

            // Update per-store pivot stock
            $storeId = $this->movementStoreId ?: $this->movementSourceStoreId;
            if ($storeId) {
                $pivot = ProductStore::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $storeId],
                    ['stock_quantity' => 0]
                );
                $pivot->increment('stock_quantity', $appliedQty);
            }
            if ($this->movementType === 'transfer_out' && $this->movementDestinationStoreId) {
                $destPivot = ProductStore::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $this->movementDestinationStoreId],
                    ['stock_quantity' => 0]
                );
                $destPivot->increment('stock_quantity', $qty);
            }

            $data = [
                'company_id' => auth()->user()->company_id,
                'product_id' => $product->id,
                'store_id' => $this->movementStoreId ?: null,
                'source_store_id' => $this->movementSourceStoreId ?: null,
                'destination_store_id' => $this->movementDestinationStoreId ?: null,
                'user_id' => auth()->id(),
                'type' => $this->movementType,
                'quantity' => $appliedQty,
                'unit' => $product->unit_sale ?? 'unit',
                'stock_before' => $oldQty,
                'stock_after' => $newQty,
                'unit_cost' => $product->purchase_price ?? 0,
                'notes' => $this->movementNotes,
            ];

            StockMovement::create($data);

            if ($this->movementType === 'transfer_out' && $this->movementDestinationStoreId) {
                StockMovement::create(array_merge($data, [
                    'type' => 'transfer_in',
                    'store_id' => $this->movementDestinationStoreId,
                    'source_store_id' => null,
                    'destination_store_id' => null,
                    'quantity' => $qty,
                ]));
            }

            DB::commit();
            $this->resetMovementForm();
            session()->flash('message', 'Mouvement de stock enregistré.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    // --- 8.30 Valuation ---
    private function computeValuation($companyId)
    {
        $products = Product::where('company_id', $companyId)
            ->with(['category', 'lots' => fn ($q) => $q->where('remaining_quantity', '>', 0)->orderBy('expiry_date')])
            ->get(['id', 'name', 'category_id', 'supplier_id', 'stock_quantity', 'purchase_price', 'selling_price']);

        $totalValue = 0;
        $totalPotentialMargin = 0;
        $byCategory = [];
        $bySupplier = [];
        $fifoTotal = 0;
        $fefoTotal = 0;

        foreach ($products as $p) {
            $qty = $p->stock_quantity ?? 0;
            $cost = $p->purchase_price ?? 0;
            $price = $p->selling_price ?? 0;

            $value = $qty * $cost;
            $margin = $qty * ($price - $cost);

            $totalValue += $value;
            $totalPotentialMargin += $margin;

            $byCategory[$p->category?->name ?? 'Sans catégorie'] = ($byCategory[$p->category?->name ?? 'Sans catégorie'] ?? 0) + $value;
            $bySupplier[$p->supplier_id ?? 0] = ($bySupplier[$p->supplier_id ?? 0] ?? 0) + $value;

            // FIFO : earliest lot price
            if ($p->lots->isNotEmpty()) {
                $remaining = $qty;
                foreach ($p->lots as $lot) {
                    $lotQty = min($remaining, $lot->remaining_quantity);
                    $lotCost = $lot->unit_cost ?? $cost;
                    $fifoTotal += $lotQty * $lotCost;
                    $remaining -= $lotQty;
                    if ($remaining <= 0) {
                        break;
                    }
                }
                if ($remaining > 0) {
                    $fifoTotal += $remaining * $cost;
                }

                // FEFO : earliest-expiry lot price
                $remaining = $qty;
                $sortedByExpiry = $p->lots->sortBy('expiry_date');
                foreach ($sortedByExpiry as $lot) {
                    $lotQty = min($remaining, $lot->remaining_quantity);
                    $lotCost = $lot->unit_cost ?? $cost;
                    $fefoTotal += $lotQty * $lotCost;
                    $remaining -= $lotQty;
                    if ($remaining <= 0) {
                        break;
                    }
                }
                if ($remaining > 0) {
                    $fefoTotal += $remaining * $cost;
                }
            } else {
                $fifoTotal += $value;
                $fefoTotal += $value;
            }
        }

        // Store stock from pivot
        $storeValuations = [];
        foreach ($this->stores as $id => $name) {
            $storeProducts = ProductStore::where('store_id', $id)
                ->whereHas('product', fn ($q) => $q->where('company_id', $companyId))
                ->get();
            $val = 0;
            foreach ($storeProducts as $sp) {
                $val += ($sp->purchase_price ?? $sp->product?->purchase_price ?? 0) * ($sp->stock_quantity ?? 0);
            }
            $storeValuations[] = ['name' => $name, 'value' => $val];
        }

        // Pertes valorisées
        $lossesValue = StockMovement::where('company_id', $companyId)
            ->whereIn('type', ['breakage', 'loss', 'expiry', 'donation', 'sample', 'internal_consumption'])
            ->where('quantity', '<', 0)
            ->sum(DB::raw('ABS(quantity) * unit_cost'));

        $supplierNames = Supplier::where('company_id', $companyId)->pluck('name', 'id')->toArray();
        $bySupplierNamed = [];
        foreach ($bySupplier as $sid => $sval) {
            $name = ($sid && isset($supplierNames[$sid])) ? $supplierNames[$sid] : 'Sans fournisseur';
            $bySupplierNamed[] = ['name' => $name, 'value' => $sval];
        }

        return [
            'total_value' => $totalValue,
            'total_margin' => $totalPotentialMargin,
            'product_count' => $products->count(),
            'losses_value' => $lossesValue,
            'methods' => [
                'cmp' => $totalValue,
                'fifo' => $fifoTotal,
                'fefo' => $fefoTotal,
                'last_price' => $products->sum(fn ($p) => ($p->stock_quantity ?? 0) * ($p->purchase_price ?? 0)),
                'standard' => $products->sum(fn ($p) => ($p->stock_quantity ?? 0) * ($p->purchase_price ?? 0)),
            ],
            'by_store' => $storeValuations,
            'by_category' => collect($byCategory)->sortKeys(),
            'by_supplier' => $bySupplierNamed,
        ];
    }

    // --- 8.31 Alerts ---
    private function computeAlerts($companyId)
    {
        $alerts = [];

        $lowStock = Product::where('company_id', $companyId)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->count();
        if ($lowStock > 0) {
            $alerts[] = ['type' => 'low_stock', 'severity' => 'warning', 'count' => $lowStock, 'message' => "{$lowStock} produit(s) en dessous du seuil minimum"];
        }

        $overstock = Product::where('company_id', $companyId)
            ->where('max_stock', '>', 0)
            ->whereColumn('stock_quantity', '>=', 'max_stock')
            ->count();
        if ($overstock > 0) {
            $alerts[] = ['type' => 'overstock', 'severity' => 'info', 'count' => $overstock, 'message' => "{$overstock} produit(s) au-dessus du seuil maximum"];
        }

        $outOfStock = Product::where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereNull('stock_quantity')->orWhere('stock_quantity', '<=', 0);
            })
            ->count();
        if ($outOfStock > 0) {
            $alerts[] = ['type' => 'out_of_stock', 'severity' => 'danger', 'count' => $outOfStock, 'message' => "{$outOfStock} produit(s) en rupture de stock"];
        }

        $negative = Product::where('company_id', $companyId)
            ->where('stock_quantity', '<', 0)
            ->count();
        if ($negative > 0) {
            $alerts[] = ['type' => 'negative', 'severity' => 'danger', 'count' => $negative, 'message' => "{$negative} produit(s) avec un stock négatif"];
        }

        $expiringSoon = Lot::whereHas('product', fn ($q) => $q->where('company_id', $companyId))
            ->where('remaining_quantity', '>', 0)
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();
        if ($expiringSoon > 0) {
            $alerts[] = ['type' => 'expiring', 'severity' => 'warning', 'count' => $expiringSoon, 'message' => "{$expiringSoon} lot(s) proches de l'expiration (30 jours)"];
        }

        $expired = Lot::whereHas('product', fn ($q) => $q->where('company_id', $companyId))
            ->where('remaining_quantity', '>', 0)
            ->where('expiry_date', '<', now())
            ->count();
        if ($expired > 0) {
            $alerts[] = ['type' => 'expired', 'severity' => 'danger', 'count' => $expired, 'message' => "{$expired} lot(s) expirés encore en stock"];
        }

        $noMovement = Product::where('company_id', $companyId)
            ->where('stock_quantity', '>', 0)
            ->whereDoesntHave('stockMovements', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->count();
        if ($noMovement > 0) {
            $alerts[] = ['type' => 'no_movement', 'severity' => 'info', 'count' => $noMovement, 'message' => "{$noMovement} produit(s) sans mouvement depuis 30 jours"];
        }

        // Écart d'inventaire important (inventories with discrepancies > 5%)
        $bigDiscrepancies = Inventory::where('company_id', $companyId)
            ->whereIn('status', ['completed', 'validated'])
            ->where('total_discrepancies', '>', 0)
            ->where(function ($q) {
                $q->whereRaw('ABS(total_discrepancy_value) > total_items * 1000'); // > 1000F/item avg
            })
            ->count();
        if ($bigDiscrepancies > 0) {
            $alerts[] = ['type' => 'big_inventory_gap', 'severity' => 'warning', 'count' => $bigDiscrepancies, 'message' => "{$bigDiscrepancies} inventaire(s) avec des écarts importants à valider"];
        }

        return $alerts;
    }

    public function getAlertCountProperty()
    {
        $alerts = $this->computeAlerts(auth()->user()->company_id);

        return collect($alerts)->whereIn('severity', ['danger', 'warning'])->sum('count');
    }

    public function movementTypeLabel($type): string
    {
        $labels = [
            'purchase_entry' => 'Entrée achat',
            'sale' => 'Sortie vente',
            'transfer_out' => 'Transfert sortant',
            'transfer_in' => 'Transfert entrant',
            'customer_return' => 'Retour client',
            'supplier_return' => 'Retour fournisseur',
            'adjustment_positive' => 'Ajustement +',
            'adjustment_negative' => 'Ajustement -',
            'inventory' => 'Inventaire',
            'breakage' => 'Casse',
            'loss' => 'Perte',
            'expiry' => 'Expiration',
            'donation' => 'Don',
            'sample' => 'Échantillon',
            'internal_consumption' => 'Conso. interne',
        ];

        return $labels[$type] ?? str_replace('_', ' ', ucfirst($type));
    }
}
