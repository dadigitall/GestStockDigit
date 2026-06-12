<?php

namespace App\Livewire\Pages\Transfers;

use App\Models\Product;
use App\Models\ProductStore;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\TransferItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'list';

    public string $search = '';

    public string $filterStatus = '';

    public bool $showForm = false;

    public ?int $editId = null;

    public ?string $sourceStoreId = null;

    public ?string $destinationStoreId = null;

    public string $transferTitle = '';

    public string $transferNotes = '';

    public array $transferItems = [];

    public string $productSearch = '';

    public array $productResults = [];

    public bool $showDetail = false;

    public ?int $detailId = null;

    // Ship
    public bool $showShip = false;

    public ?int $shipTransferId = null;

    public array $shipItems = [];

    // Receive
    public bool $showReceive = false;

    public ?int $receiveTransferId = null;

    public array $receiveItems = [];

    public array $stores = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->stores = Store::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('type', ['entrepot', 'depot', 'magasin', 'boutique', 'filiale', 'agence'])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $transfers = Transfer::forCompany($companyId)
            ->with(['sourceStore', 'destinationStore', 'requestedBy', 'approvedBy', 'preparedBy', 'shippedBy', 'receivedBy'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->detailId) {
            $detail = Transfer::forCompany($companyId)
                ->with([
                    'items.product', 'sourceStore', 'destinationStore',
                    'requestedBy', 'approvedBy', 'preparedBy', 'shippedBy', 'receivedBy',
                ])
                ->find($this->detailId);
        }

        return view('livewire.pages.transfers.index', compact('transfers', 'detail'))
            ->layout('layouts.app', ['header' => 'Transferts']);
    }

    // ─── Formulaire création ───

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $transfer = Transfer::with('items.product')->findOrFail($id);
        if ($transfer->status !== 'draft') {
            session()->flash('error', 'Seul un brouillon peut être modifié.');

            return;
        }

        $this->resetForm();
        $this->editId = $transfer->id;
        $this->sourceStoreId = (string) $transfer->source_store_id;
        $this->destinationStoreId = (string) $transfer->destination_store_id;
        $this->transferTitle = $transfer->title ?? '';
        $this->transferNotes = $transfer->notes ?? '';
        $this->transferItems = $transfer->items->map(function ($item) {
            return [
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product?->name ?? '#'.$item->product_id,
                'quantity' => (string) $item->quantity_requested,
            ];
        })->toArray();

        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->sourceStoreId = null;
        $this->destinationStoreId = null;
        $this->transferTitle = '';
        $this->transferNotes = '';
        $this->transferItems = [];
        $this->productSearch = '';
        $this->productResults = [];
        $this->showForm = false;
        $this->showDetail = false;
        $this->detailId = null;
        $this->showShip = false;
        $this->shipTransferId = null;
        $this->shipItems = [];
        $this->showReceive = false;
        $this->receiveTransferId = null;
        $this->receiveItems = [];
    }

    public function searchProduct()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];

            return;
        }

        $companyId = auth()->user()->company_id;
        $this->productResults = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('reference', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'reference', 'stock_quantity', 'purchase_price'])
            ->toArray();
    }

    public function addItem($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        foreach ($this->transferItems as $item) {
            if ($item['product_id'] === (string) $productId) {
                session()->flash('error', 'Ce produit est déjà dans la liste.');

                return;
            }
        }

        $this->transferItems[] = [
            'product_id' => (string) $product->id,
            'product_name' => $product->name.' ('.$product->reference.')',
            'quantity' => '1',
        ];

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function removeItem($index)
    {
        unset($this->transferItems[$index]);
        $this->transferItems = array_values($this->transferItems);
    }

    public function save()
    {
        $this->validate([
            'sourceStoreId' => 'required|exists:stores,id',
            'destinationStoreId' => 'required|exists:stores,id|different:sourceStoreId',
            'transferTitle' => 'nullable|string|max:255',
            'transferItems' => 'required|array|min:1',
            'transferItems.*.product_id' => 'required|exists:products,id',
            'transferItems.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $companyId = auth()->user()->company_id;

        DB::beginTransaction();
        try {
            $transfer = Transfer::updateOrCreate(
                ['id' => $this->editId],
                [
                    'company_id' => $companyId,
                    'title' => $this->transferTitle,
                    'source_store_id' => $this->sourceStoreId,
                    'destination_store_id' => $this->destinationStoreId,
                    'notes' => $this->transferNotes,
                ]
            );

            if (! $this->editId) {
                $transfer->status = 'draft';
                $transfer->requested_by = auth()->id();
                $transfer->requested_at = now();
                $transfer->save();
            }

            if ($this->editId) {
                $transfer->items()->delete();
            }

            $items = [];
            foreach ($this->transferItems as $data) {
                $product = Product::find($data['product_id']);
                $items[] = [
                    'transfer_id' => $transfer->id,
                    'product_id' => $data['product_id'],
                    'quantity_requested' => $data['quantity'],
                    'unit_cost' => $product?->purchase_price ?? 0,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            TransferItem::insert($items);

            DB::commit();
            $this->resetForm();
            session()->flash('message', $this->editId
                ? "Transfert {$transfer->reference} mis à jour."
                : "Transfert {$transfer->reference} créé.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    // ─── Workflow actions ───

    public function submit($id)
    {
        $transfer = Transfer::findOrFail($id);
        if ($transfer->status !== 'draft') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $transfer->update([
            'status' => 'requested',
            'requested_by' => auth()->id(),
            'requested_at' => now(),
        ]);
        session()->flash('message', "Transfert {$transfer->reference} soumis pour approbation.");
    }

    public function approve($id)
    {
        $transfer = Transfer::findOrFail($id);
        if ($transfer->status !== 'requested') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $transfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        session()->flash('message', "Transfert {$transfer->reference} approuvé.");
    }

    public function reject($id)
    {
        $transfer = Transfer::findOrFail($id);
        if ($transfer->status !== 'requested') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $transfer->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        session()->flash('message', "Transfert {$transfer->reference} refusé.");
    }

    public function prepare($id)
    {
        $transfer = Transfer::findOrFail($id);
        if ($transfer->status !== 'approved') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $transfer->update([
            'status' => 'prepared',
            'prepared_by' => auth()->id(),
            'prepared_at' => now(),
        ]);
        session()->flash('message', "Transfert {$transfer->reference} marqué comme préparé.");
    }

    // ─── Expédition ───

    public function openShip($id)
    {
        $transfer = Transfer::with('items.product')->findOrFail($id);
        if ($transfer->status !== 'prepared') {
            session()->flash('error', 'Le transfert doit être préparé avant expédition.');

            return;
        }

        $this->shipTransferId = $id;
        $this->shipItems = $transfer->items->mapWithKeys(function ($item) {
            $max = $item->quantity_requested;

            return [$item->id => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? '#'.$item->product_id,
                'product_id' => $item->product_id ?? null,
                'quantity_requested' => $max,
                'quantity_shipped' => $item->quantity_shipped ?? $max,
            ]];
        })->toArray();

        $this->showShip = true;
    }

    public function saveShip()
    {
        $transfer = Transfer::findOrFail($this->shipTransferId);

        DB::beginTransaction();
        try {
            foreach ($this->shipItems as $itemId => $data) {
                $item = TransferItem::findOrFail($itemId);
                $shippedQty = (float) ($data['quantity_shipped'] ?? 0);

                if ($shippedQty <= 0) {
                    continue;
                }

                $product = Product::findOrFail($item->product_id);
                $oldStock = $product->stock_quantity ?? 0;
                $newStock = max(0, $oldStock - $shippedQty);

                // Decrement source stock
                $product->decrement('stock_quantity', $shippedQty);

                // Update source store pivot
                $sourcePivot = ProductStore::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $transfer->source_store_id],
                    ['stock_quantity' => 0]
                );
                $sourcePivot->decrement('stock_quantity', $shippedQty);

                // Increment transit stock
                $product->increment('transit_stock', $shippedQty);

                // StockMovement: transfer_out (negative)
                StockMovement::create([
                    'company_id' => $transfer->company_id,
                    'product_id' => $product->id,
                    'store_id' => $transfer->source_store_id,
                    'source_store_id' => $transfer->source_store_id,
                    'destination_store_id' => $transfer->destination_store_id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_out',
                    'quantity' => -$shippedQty,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'unit_cost' => $item->unit_cost,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'notes' => "Expédition transfert {$transfer->reference}",
                ]);

                // Update item
                $item->update([
                    'quantity_shipped' => $shippedQty,
                    'status' => 'shipped',
                ]);
            }

            $transfer->update([
                'status' => 'shipped',
                'shipped_by' => auth()->id(),
                'shipped_at' => now(),
            ]);

            DB::commit();
            $this->showShip = false;
            $this->shipTransferId = null;
            $this->shipItems = [];
            session()->flash('message', "Transfert {$transfer->reference} expédié.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    // ─── Réception ───

    public function openReceive($id)
    {
        $transfer = Transfer::with('items.product')->findOrFail($id);
        if (! in_array($transfer->status, ['shipped'])) {
            session()->flash('error', 'Le transfert doit être expédié avant réception.');

            return;
        }

        $this->receiveTransferId = $id;
        $this->receiveItems = $transfer->items->mapWithKeys(function ($item) {
            $max = $item->quantity_shipped ?? $item->quantity_requested;

            return [$item->id => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? '#'.$item->product_id,
                'product_id' => $item->product_id,
                'quantity_shipped' => $max,
                'quantity_received' => $item->quantity_received ?? $max,
            ]];
        })->toArray();

        $this->showReceive = true;
    }

    public function saveReceive()
    {
        $transfer = Transfer::findOrFail($this->receiveTransferId);

        DB::beginTransaction();
        try {
            $allFullyReceived = true;
            $hasAnyReceived = false;

            foreach ($this->receiveItems as $itemId => $data) {
                $item = TransferItem::findOrFail($itemId);
                $receivedQty = (float) ($data['quantity_received'] ?? 0);
                $shippedQty = (float) ($item->quantity_shipped ?? $item->quantity_requested);

                if ($receivedQty <= 0) {
                    $allFullyReceived = false;

                    continue;
                }

                $product = Product::findOrFail($item->product_id);

                // Decrement transit stock
                $product->decrement('transit_stock', min($receivedQty, $shippedQty));

                // Increment destination store pivot
                $destPivot = ProductStore::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $transfer->destination_store_id],
                    ['stock_quantity' => 0]
                );
                $destPivot->increment('stock_quantity', $receivedQty);

                // Also increment product global stock
                $product->increment('stock_quantity', $receivedQty);

                $oldStock = $product->stock_quantity - $receivedQty;

                // StockMovement: transfer_in (positive)
                StockMovement::create([
                    'company_id' => $transfer->company_id,
                    'product_id' => $product->id,
                    'store_id' => $transfer->destination_store_id,
                    'source_store_id' => $transfer->source_store_id,
                    'destination_store_id' => $transfer->destination_store_id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_in',
                    'quantity' => $receivedQty,
                    'stock_before' => max(0, $oldStock),
                    'stock_after' => $product->stock_quantity,
                    'unit_cost' => $item->unit_cost,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'notes' => "Réception transfert {$transfer->reference}",
                ]);

                // Loss handling
                $lostQty = max(0, $shippedQty - $receivedQty);
                $itemStatus = 'received';
                if ($lostQty > 0) {
                    $itemStatus = 'partially_received';
                    $allFullyReceived = false;

                    StockMovement::create([
                        'company_id' => $transfer->company_id,
                        'product_id' => $product->id,
                        'store_id' => null,
                        'user_id' => auth()->id(),
                        'type' => 'loss',
                        'quantity' => -$lostQty,
                        'unit_cost' => $item->unit_cost,
                        'reference_type' => 'transfer',
                        'reference_id' => $transfer->id,
                        'notes' => "Perte en transit sur transfert {$transfer->reference} ({$product->name})",
                    ]);
                }
                $hasAnyReceived = true;

                // Update item
                $item->update([
                    'quantity_received' => $receivedQty,
                    'quantity_lost' => $lostQty,
                    'status' => $itemStatus,
                ]);
            }

            $newStatus = 'fully_received';
            if (! $hasAnyReceived) {
                $newStatus = 'shipped';
            } elseif (! $allFullyReceived) {
                $newStatus = 'partially_received';
            }

            $transfer->update([
                'status' => $newStatus,
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            DB::commit();
            $this->showReceive = false;
            $this->receiveTransferId = null;
            $this->receiveItems = [];
            session()->flash('message', "Transfert {$transfer->reference} réceptionné (statut: {$newStatus}).");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    // ─── Annulation ───

    public function cancel($id)
    {
        $transfer = Transfer::findOrFail($id);
        if (! in_array($transfer->status, ['draft', 'requested', 'approved', 'prepared'])) {
            session()->flash('error', 'Ce transfert ne peut plus être annulé.');

            return;
        }

        $transfer->update(['status' => 'cancelled']);
        session()->flash('message', "Transfert {$transfer->reference} annulé.");
    }

    // ─── Détail ───

    public function viewDetail($id)
    {
        $this->detailId = $id;
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->detailId = null;
        $this->showDetail = false;
    }

    // ─── Helpers ───

    public function statusBadge($status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
            'requested' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
            'approved' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300',
            'prepared' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'shipped' => 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300',
            'partially_received' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300',
            'fully_received' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'rejected' => 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
            'cancelled' => 'bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        };
    }

    public function statusLabel($status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'requested' => 'Demandé',
            'approved' => 'Approuvé',
            'prepared' => 'Préparé',
            'shipped' => 'Expédié',
            'partially_received' => 'Reçu partiellement',
            'fully_received' => 'Reçu totalement',
            'rejected' => 'Refusé',
            'cancelled' => 'Annulé',
            default => $status,
        };
    }

    public function itemStatusLabel($status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'shipped' => 'Expédié',
            'partially_received' => 'Partiellement reçu',
            'received' => 'Reçu',
            'lost' => 'Perdu',
            default => $status,
        };
    }
}
