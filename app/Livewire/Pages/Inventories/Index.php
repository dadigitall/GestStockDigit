<?php

namespace App\Livewire\Pages\Inventories;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\Lot;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'list';

    public string $search = '';

    public string $filterStatus = '';

    public string $filterType = '';

    // Etape 1-2 : Création + périmètre
    public bool $showForm = false;

    public ?int $editId = null;

    public string $title = '';

    public string $type = 'global';

    public ?string $storeId = null;

    public ?string $categoryId = null;

    public ?string $locationId = null;

    public bool $freezeStock = false;

    public string $notes = '';

    // Etape 4-5 : Comptage
    public bool $showCounting = false;

    public string $importCsv = '';

    public ?int $countingInventoryId = null;

    public array $countedItems = [];

    // Etape 6-7 : Comparaison + approbation par item
    public bool $showComparison = false;

    public ?int $comparisonInventoryId = null;

    public array $comparisonItems = [];

    // Detail
    public ?int $detailId = null;

    public array $stores = [];

    public array $categories = [];

    public array $locations = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->stores = Store::where('company_id', $companyId)->pluck('name', 'id')->toArray();
        $this->categories = Category::where('company_id', $companyId)->pluck('name', 'id')->toArray();
        $this->loadLocations();
    }

    private function loadLocations()
    {
        $companyId = auth()->user()->company_id;
        $all = Location::whereHas('store', fn ($q) => $q->where('company_id', $companyId))
            ->where('is_active', true)
            ->with('store')
            ->get();
        $grouped = [];
        foreach ($all as $loc) {
            $storeName = $loc->store->name ?? 'Sans magasin';
            $grouped[$storeName][$loc->id] = "{$loc->name} ({$loc->code})";
        }
        $this->locations = $grouped;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $inventories = Inventory::forCompany($companyId)
            ->with(['creator', 'validator', 'store', 'category', 'location'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->detailId) {
            $detail = Inventory::forCompany($companyId)
                ->with(['items.product', 'items.store', 'items.lot', 'items.counter', 'items.decider', 'creator', 'validator', 'store', 'category', 'location'])
                ->find($this->detailId);
        }

        return view('livewire.pages.inventories.index', compact('inventories', 'detail'))
            ->layout('layouts.app', ['header' => 'Inventaires']);
    }

    // ─── Etape 1-2 : Création + périmètre ───

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->title = '';
        $this->type = 'global';
        $this->storeId = null;
        $this->categoryId = null;
        $this->locationId = null;
        $this->freezeStock = false;
        $this->notes = '';
        $this->showForm = false;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:global,partial,by_store,by_category,by_location,tournant,by_lot',
            'storeId' => 'nullable|exists:stores,id',
            'categoryId' => 'nullable|exists:categories,id',
            'locationId' => 'nullable|exists:locations,id',
        ]);

        $companyId = auth()->user()->company_id;

        $effectiveStoreId = $this->storeId;
        if ($this->type === 'by_location' && $this->locationId) {
            $location = Location::find($this->locationId);
            $effectiveStoreId = $location ? $location->store_id : $this->storeId;
        }

        DB::beginTransaction();
        try {
            $inventory = Inventory::create([
                'company_id' => $companyId,
                'title' => $this->title,
                'type' => $this->type,
                'status' => 'draft',
                'store_id' => $effectiveStoreId ?: null,
                'category_id' => $this->categoryId ?: null,
                'location_id' => $this->locationId ?: null,
                'freeze_stock' => $this->freezeStock,
                'created_by' => auth()->id(),
                'notes' => $this->notes,
            ]);

            $this->generateItems($inventory);

            DB::commit();
            $this->resetForm();
            $count = $inventory->items()->count();
            session()->flash('message', "Inventaire {$inventory->reference} créé avec {$count} article(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    private function generateItems(Inventory $inventory): void
    {
        $companyId = auth()->user()->company_id;
        $query = Product::where('company_id', $companyId)->where('is_stockable', true);

        $effectiveStoreId = $this->storeId;
        if ($this->type === 'by_location' && $this->locationId) {
            $location = Location::with('store')->find($this->locationId);
            $effectiveStoreId = $location?->store_id;
        }

        if ($effectiveStoreId) {
            $query->whereHas('stores', fn ($q) => $q->where('stores.id', $effectiveStoreId));
        }
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        if ($this->type === 'partial') {
            $query->where('stock_quantity', '>', 0);
        }

        $products = $query->get(['id', 'name', 'stock_quantity', 'purchase_price']);
        $storeIds = $effectiveStoreId ? [$effectiveStoreId] : Store::where('company_id', $companyId)->pluck('id')->toArray();

        $items = [];
        foreach ($products as $product) {
            foreach ($storeIds as $sid) {
                $pivot = ProductStore::where('product_id', $product->id)->where('store_id', $sid)->first();
                $storeQty = $pivot ? ($pivot->stock_quantity ?? $product->stock_quantity) : 0;
                $cost = $product->purchase_price ?? 0;

                if ($this->type === 'by_lot') {
                    $lots = Lot::where('product_id', $product->id)
                        ->where('remaining_quantity', '>', 0)
                        ->get();
                    foreach ($lots as $lot) {
                        $items[] = [
                            'inventory_id' => $inventory->id,
                            'product_id' => $product->id,
                            'store_id' => $sid,
                            'lot_id' => $lot->id,
                            'theoretical_quantity' => $lot->remaining_quantity,
                            'unit_cost' => $lot->unit_cost ?? $cost,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                } else {
                    $items[] = [
                        'inventory_id' => $inventory->id,
                        'product_id' => $product->id,
                        'store_id' => $sid,
                        'lot_id' => null,
                        'theoretical_quantity' => $storeQty,
                        'unit_cost' => $cost,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($items)) {
            InventoryItem::insert($items);
        }

        $inventory->update([
            'total_items' => count($items),
            'started_at' => now(),
        ]);
    }

    // ─── Etape 3-4-5 : Gel + comptage physique ───

    public function startCounting($id)
    {
        $inventory = Inventory::findOrFail($id);
        if (! $inventory->canBeStarted()) {
            session()->flash('error', 'Cet inventaire ne peut pas être démarré.');

            return;
        }

        $data = ['status' => 'in_progress', 'frozen_at' => now()];
        $inventory->update($data);

        $this->countingInventoryId = $id;
        $this->showCounting = true;
        $this->loadCountedItems();
    }

    public function loadCountedItems()
    {
        $items = InventoryItem::where('inventory_id', $this->countingInventoryId)
            ->with(['product', 'store', 'lot'])
            ->get();

        $this->countedItems = $items->mapWithKeys(function ($item) {
            return [$item->id => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? '#'.$item->product_id,
                'store_name' => $item->store?->name ?? '',
                'lot_number' => $item->lot?->lot_number,
                'theoretical' => $item->theoretical_quantity,
                'physical' => $item->physical_quantity,
                'unit_cost' => $item->unit_cost,
            ]];
        })->toArray();
    }

    public function saveCounting()
    {
        DB::beginTransaction();
        try {
            foreach ($this->countedItems as $itemId => $data) {
                $physical = $data['physical'] ?? null;
                if ($physical === '' || $physical === null) {
                    continue;
                }

                $item = InventoryItem::findOrFail($itemId);
                $theoretical = $item->theoretical_quantity;
                $discrepancy = (float) $physical - (float) $theoretical;
                $discrepancyValue = $discrepancy * $item->unit_cost;

                $item->update([
                    'physical_quantity' => $physical,
                    'discrepancy_quantity' => $discrepancy,
                    'discrepancy_value' => $discrepancyValue,
                    'status' => 'counted',
                    'counted_by' => auth()->id(),
                    'counted_at' => now(),
                ]);
            }

            $inventory = Inventory::findOrFail($this->countingInventoryId);
            $discrepantCount = $inventory->discrepantItems()->count();
            $totalDiscrepancyValue = $inventory->items()->sum('discrepancy_value');
            $inventory->update([
                'total_discrepancies' => $discrepantCount,
                'total_discrepancy_value' => $totalDiscrepancyValue,
            ]);

            DB::commit();
            session()->flash('message', 'Comptage enregistré.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function importCount()
    {
        if (empty(trim($this->importCsv))) {
            session()->flash('error', 'Collez d\'abord les données CSV (référence;quantité).');

            return;
        }

        $lines = explode("\n", trim($this->importCsv));
        $imported = 0;
        $errors = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = str_getcsv($line, ';');
            if (count($parts) < 2) {
                $errors[] = 'Ligne '.($i + 1).' : format invalide (attendu: référence;quantité)';

                continue;
            }

            $reference = trim($parts[0]);
            $qty = (float) str_replace([' ', ','], ['', '.'], trim($parts[1]));

            $item = InventoryItem::where('inventory_id', $this->countingInventoryId)
                ->whereHas('product', fn ($q) => $q->where('reference', $reference))
                ->first();

            if (! $item) {
                $errors[] = 'Ligne '.($i + 1)." : produit '{$reference}' introuvable dans l'inventaire";

                continue;
            }

            $physical = $qty;
            $theoretical = $item->theoretical_quantity;
            $discrepancy = $physical - $theoretical;
            $discrepancyValue = $discrepancy * $item->unit_cost;

            $item->update([
                'physical_quantity' => $physical,
                'discrepancy_quantity' => $discrepancy,
                'discrepancy_value' => $discrepancyValue,
                'status' => 'counted',
                'counted_by' => auth()->id(),
                'counted_at' => now(),
            ]);
            $imported++;

            // Sync back to the Livewire countedItems array
            if (isset($this->countedItems[$item->id])) {
                $this->countedItems[$item->id]['physical'] = $physical;
            }
        }

        $msg = "{$imported} article(s) importés depuis le CSV.";
        if (! empty($errors)) {
            $msg .= ' Erreurs: '.implode('; ', array_slice($errors, 0, 5));
        }
        $this->importCsv = '';
        session()->flash('message', $msg);
    }

    // ─── Etape 6-7 : Comparaison + analyse des écarts ───

    public function openComparison($id)
    {
        $inventory = Inventory::findOrFail($id);
        if ($inventory->status !== 'in_progress') {
            session()->flash('error', 'Le comptage doit être en cours.');

            return;
        }

        $this->comparisonInventoryId = $id;
        $this->showComparison = true;
        $this->loadComparisonItems();
    }

    public function loadComparisonItems()
    {
        $items = InventoryItem::where('inventory_id', $this->comparisonInventoryId)
            ->with(['product', 'store', 'lot'])
            ->get();

        $this->comparisonItems = $items->mapWithKeys(function ($item) {
            return [$item->id => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? '#'.$item->product_id,
                'store_name' => $item->store?->name ?? '',
                'lot_number' => $item->lot?->lot_number,
                'theoretical' => $item->theoretical_quantity,
                'physical' => $item->physical_quantity,
                'discrepancy' => $item->discrepancy_quantity,
                'discrepancy_value' => $item->discrepancy_value,
                'unit_cost' => $item->unit_cost,
                'status' => $item->status,
                'decision' => $item->decision,
                'justification' => $item->justification,
            ]];
        })->toArray();
    }

    public function approveItem($itemId)
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->update([
            'decision' => 'approved',
            'justification' => $this->comparisonItems[$itemId]['justification'] ?? null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);
        $this->comparisonItems[$itemId]['decision'] = 'approved';
    }

    public function rejectItem($itemId)
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->update([
            'decision' => 'rejected',
            'justification' => $this->comparisonItems[$itemId]['justification'] ?? null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);
        $this->comparisonItems[$itemId]['decision'] = 'rejected';
        // Rejected = keep theoretical quantity, reset physical
        $item->update([
            'physical_quantity' => $item->theoretical_quantity,
            'discrepancy_quantity' => 0,
            'discrepancy_value' => 0,
        ]);
        $this->loadComparisonItems();
    }

    public function closeComparison()
    {
        $this->showComparison = false;
        $this->comparisonInventoryId = null;
        $this->comparisonItems = [];
    }

    // ─── Etape 8-9 : Validation + ajustement ───

    public function completeInventory($id)
    {
        $inventory = Inventory::findOrFail($id);
        $uncounted = $inventory->items()->whereNull('physical_quantity')->count();

        if ($uncounted > 0) {
            session()->flash('error', "{$uncounted} article(s) non comptés. Complétez le comptage d'abord.");

            return;
        }

        $inventory->update(['status' => 'completed', 'completed_at' => now()]);
        session()->flash('message', 'Inventaire terminé. En attente de validation.');
    }

    public function validateInventory($id)
    {
        $inventory = Inventory::findOrFail($id);
        if (! $inventory->canBeValidated()) {
            session()->flash('error', 'Cet inventaire ne peut pas être validé.');

            return;
        }

        DB::beginTransaction();
        try {
            foreach ($inventory->discrepantItems as $item) {
                $product = $item->product;
                $diff = $item->discrepancy_quantity;

                if (abs($diff) < 0.01) {
                    continue;
                }

                $oldQty = $product->stock_quantity ?? 0;
                $newQty = $oldQty + $diff;
                $product->update(['stock_quantity' => max(0, $newQty)]);

                // Also update per-store pivot
                $pivot = ProductStore::firstOrCreate(
                    ['product_id' => $item->product_id, 'store_id' => $item->store_id],
                    ['stock_quantity' => 0]
                );
                $pivot->increment('stock_quantity', $diff);

                StockMovement::create([
                    'company_id' => $inventory->company_id,
                    'product_id' => $item->product_id,
                    'store_id' => $item->store_id,
                    'user_id' => auth()->id(),
                    'type' => $diff > 0 ? 'adjustment_positive' : 'adjustment_negative',
                    'quantity' => $diff,
                    'unit' => $product->unit_sale ?? 'unit',
                    'stock_before' => $oldQty,
                    'stock_after' => max(0, $newQty),
                    'unit_cost' => $item->unit_cost,
                    'notes' => "Ajustement inventaire {$inventory->reference}: {$inventory->title}",
                ]);
            }

            $inventory->update([
                'status' => 'validated',
                'validated_at' => now(),
                'validated_by' => auth()->id(),
            ]);

            DB::commit();
            $this->detailId = $inventory->id;
            session()->flash('message', 'Inventaire validé. Stock ajusté automatiquement.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function cancelInventory($id)
    {
        Inventory::findOrFail($id)->update(['status' => 'cancelled']);
        session()->flash('message', 'Inventaire annulé.');
    }

    // ─── Etape 10 : Détail / rapport ───

    public function viewDetail($id)
    {
        $this->detailId = $id;
    }

    public function closeDetail()
    {
        $this->detailId = null;
    }

    // ─── Helpers ───

    public function statusBadge($status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
            'in_progress' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
            'frozen' => 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300',
            'completed' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'validated' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'cancelled' => 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        };
    }

    public function typeLabel($type): string
    {
        return match ($type) {
            'global' => 'Global',
            'partial' => 'Partiel',
            'by_store' => 'Par magasin',
            'by_category' => 'Par catégorie',
            'by_location' => 'Par emplacement',
            'tournant' => 'Tournant',
            'by_lot' => 'Par lot',
            default => $type,
        };
    }

    public function getAlertCountProperty()
    {
        return Inventory::where('company_id', auth()->user()->company_id)
            ->where('status', 'completed')
            ->count();
    }
}
