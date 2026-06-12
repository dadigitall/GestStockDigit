<?php

namespace App\Livewire\Pages\Purchases;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'requisitions';

    public string $search = '';

    // --- Requisition fields ---
    public $showRequisitionForm = false;

    public $editingRequisition = null;

    public $requisition_store_id;

    public $requisition_priority = 'medium';

    public $requisition_justification;

    public $requisition_desired_date;

    public $requisition_notes;

    public $requisition_items = [];

    public $stores = [];

    // --- PO fields ---
    public $showPOForm = false;

    public $editingPO = null;

    public $po_supplier_id;

    public $po_store_id;

    public $po_source = 'manual';

    public $po_purchase_requisition_id;

    public $po_payment_terms;

    public $po_delivery_date;

    public $po_shipping_cost = 0;

    public $po_notes;

    public $po_items = [];

    public $suppliers = [];

    // --- Receipt fields ---
    public $showReceiptForm = false;

    public $editingReceipt = null;

    public $receipt_purchase_order_id;

    public $receipt_notes;

    public $receipt_items = [];

    // --- Return fields ---
    public $showReturnForm = false;

    public $editingReturn = null;

    public $return_supplier_id;

    public $return_store_id;

    public $return_purchase_order_id;

    public $return_goods_receipt_id;

    public $return_reason_type = 'defective';

    public $return_return_type = 'partial';

    public $return_notes;

    public $return_items = [];

    // --- Shared ---
    public $productSearch = '';

    public $productResults = [];

    public $contextItemIndex;

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->stores = Store::where('company_id', $companyId)->get();
        $this->suppliers = Supplier::where('company_id', $companyId)->get();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $requisitions = PurchaseRequisition::where('company_id', $companyId)
            ->with(['store', 'requester', 'items.product'])
            ->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('justification', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'requisitionsPage');

        $purchaseOrders = PurchaseOrder::where('company_id', $companyId)
            ->with(['supplier', 'store', 'items.product'])
            ->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'poPage');

        $receipts = GoodsReceipt::where('company_id', $companyId)
            ->with(['purchaseOrder', 'supplier', 'items.product'])
            ->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'receiptsPage');

        $returns = SupplierReturn::where('company_id', $companyId)
            ->with(['supplier', 'items.product'])
            ->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'returnsPage');

        return view('livewire.pages.purchases.index', compact(
            'requisitions', 'purchaseOrders', 'receipts', 'returns'
        ))->layout('layouts.app', ['header' => 'Achats & Approvisionnements']);
    }

    // --- Requisition methods ---
    public function createRequisition()
    {
        $this->resetRequisitionForm();
        $this->showRequisitionForm = true;
    }

    public function editRequisition(PurchaseRequisition $requisition)
    {
        $this->editingRequisition = $requisition;
        $this->requisition_store_id = $requisition->store_id;
        $this->requisition_priority = $requisition->priority;
        $this->requisition_justification = $requisition->justification;
        $this->requisition_desired_date = $requisition->desired_date?->format('Y-m-d');
        $this->requisition_notes = $requisition->notes;
        $this->requisition_items = $requisition->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'product_name' => $i->product->name,
            'quantity' => $i->quantity,
            'notes' => $i->notes,
        ])->toArray();
        $this->showRequisitionForm = true;
    }

    public function addRequisitionItem()
    {
        $this->requisition_items[] = ['product_id' => '', 'product_name' => '', 'quantity' => 1, 'notes' => ''];
    }

    public function removeRequisitionItem($index)
    {
        unset($this->requisition_items[$index]);
        $this->requisition_items = array_values($this->requisition_items);
    }

    public function saveRequisition()
    {
        $this->validate([
            'requisition_store_id' => 'required|exists:stores,id',
            'requisition_priority' => 'required|in:low,medium,high,urgent',
            'requisition_justification' => 'nullable|string',
            'requisition_desired_date' => 'nullable|date',
            'requisition_items' => 'required|array|min:1',
            'requisition_items.*.product_id' => 'required|exists:products,id',
            'requisition_items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'company_id' => auth()->user()->company_id,
                'store_id' => $this->requisition_store_id,
                'requested_by' => auth()->id(),
                'reference' => $this->editingRequisition ? $this->editingRequisition->reference : PurchaseRequisition::generateReference(),
                'priority' => $this->requisition_priority,
                'justification' => $this->requisition_justification,
                'desired_date' => $this->requisition_desired_date,
                'status' => $this->editingRequisition ? $this->editingRequisition->status : 'draft',
                'notes' => $this->requisition_notes,
            ];

            if ($this->editingRequisition) {
                $this->editingRequisition->update($data);
                $this->editingRequisition->items()->delete();
                $requisition = $this->editingRequisition;
            } else {
                $requisition = PurchaseRequisition::create($data);
            }

            foreach ($this->requisition_items as $item) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $requisition->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            $this->resetRequisitionForm();
            session()->flash('message', 'Demande d\'approvisionnement '.($this->editingRequisition ? 'mise à jour.' : 'créée.'));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function submitRequisition(PurchaseRequisition $requisition)
    {
        $requisition->update(['status' => 'submitted']);
        session()->flash('message', 'Demande soumise.');
    }

    public function approveRequisition(PurchaseRequisition $requisition)
    {
        $requisition->update(['status' => 'approved']);
        session()->flash('message', 'Demande approuvée.');
    }

    public function rejectRequisition(PurchaseRequisition $requisition)
    {
        $requisition->update(['status' => 'rejected']);
        session()->flash('message', 'Demande rejetée.');
    }

    public function cancelRequisition(PurchaseRequisition $requisition)
    {
        $requisition->update(['status' => 'cancelled']);
        session()->flash('message', 'Demande annulée.');
    }

    public function cancelPO(PurchaseOrder $po)
    {
        $po->update(['status' => 'cancelled']);
        session()->flash('message', 'Commande annulée.');
    }

    public function resetRequisitionForm()
    {
        $this->editingRequisition = null;
        $this->requisition_store_id = $this->stores->first()?->id;
        $this->requisition_priority = 'medium';
        $this->requisition_justification = '';
        $this->requisition_desired_date = '';
        $this->requisition_notes = '';
        $this->requisition_items = [];
        $this->showRequisitionForm = false;
    }

    // --- PO methods ---
    public function createPO()
    {
        $this->resetPOForm();
        $this->showPOForm = true;
    }

    public function createPOFromRequisition(PurchaseRequisition $requisition)
    {
        $this->resetPOForm();
        $this->po_purchase_requisition_id = $requisition->id;
        $this->po_source = 'requisition';
        $this->po_store_id = $requisition->store_id;
        foreach ($requisition->items as $item) {
            $this->po_items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->purchase_price ?? 0,
                'discount' => 0,
                'tax_rate' => 0,
            ];
        }
        $this->showPOForm = true;
    }

    public function editPO(PurchaseOrder $po)
    {
        $this->editingPO = $po;
        $this->po_supplier_id = $po->supplier_id;
        $this->po_store_id = $po->store_id;
        $this->po_source = $po->source;
        $this->po_purchase_requisition_id = $po->purchase_requisition_id;
        $this->po_payment_terms = $po->payment_terms;
        $this->po_delivery_date = $po->delivery_date?->format('Y-m-d');
        $this->po_notes = $po->notes;
        $this->po_items = $po->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'product_name' => $i->product->name,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
            'discount' => $i->discount,
            'tax_rate' => $i->tax_rate,
        ])->toArray();
        $this->showPOForm = true;
    }

    public function addPOItem()
    {
        $this->po_items[] = ['product_id' => '', 'product_name' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0, 'tax_rate' => 0];
    }

    public function removePOItem($index)
    {
        unset($this->po_items[$index]);
        $this->po_items = array_values($this->po_items);
    }

    public function savePO()
    {
        $this->validate([
            'po_supplier_id' => 'required|exists:suppliers,id',
            'po_store_id' => 'required|exists:stores,id',
            'po_items' => 'required|array|min:1',
            'po_items.*.product_id' => 'required|exists:products,id',
            'po_items.*.quantity' => 'required|numeric|min:0.01',
            'po_items.*.unit_price' => 'required|numeric|min:0',
            'po_shipping_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $itemsData = [];

            foreach ($this->po_items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $lineSubtotal * ($item['discount'] / 100);
                $lineTaxable = $lineSubtotal - $lineDiscount;
                $lineTax = $lineTaxable * ($item['tax_rate'] / 100);
                $lineTotal = $lineTaxable + $lineTax;

                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $lineDiscount,
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $lineTotal,
                ];
            }

            $data = [
                'company_id' => auth()->user()->company_id,
                'supplier_id' => $this->po_supplier_id,
                'store_id' => $this->po_store_id,
                'user_id' => auth()->id(),
                'reference' => $this->editingPO ? $this->editingPO->reference : PurchaseOrder::generateReference(),
                'source' => $this->po_source,
                'purchase_requisition_id' => $this->po_purchase_requisition_id ?: null,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount' => $totalDiscount,
                'shipping_cost' => $this->po_shipping_cost,
                'total' => $subtotal - $totalDiscount + $totalTax + ($this->po_shipping_cost ?? 0),
                'payment_terms' => $this->po_payment_terms,
                'delivery_date' => $this->po_delivery_date,
                'status' => $this->editingPO ? $this->editingPO->status : 'draft',
                'notes' => $this->po_notes,
            ];

            if ($this->editingPO) {
                $this->editingPO->update($data);
                $this->editingPO->items()->delete();
                $po = $this->editingPO;
            } else {
                $po = PurchaseOrder::create($data);
            }

            foreach ($itemsData as $item) {
                PurchaseOrderItem::create(array_merge($item, ['purchase_order_id' => $po->id]));
            }

            if ($this->po_purchase_requisition_id) {
                PurchaseRequisition::where('id', $this->po_purchase_requisition_id)
                    ->where('status', 'approved')
                    ->update(['status' => 'in_progress']);
            }

            DB::commit();
            $this->resetPOForm();
            session()->flash('message', 'Commande fournisseur '.($this->editingPO ? 'mise à jour.' : 'créée.'));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function sendPO(PurchaseOrder $po)
    {
        $po->update(['status' => 'sent']);
        session()->flash('message', 'Commande envoyée au fournisseur.');
    }

    public function resetPOForm()
    {
        $this->editingPO = null;
        $this->po_supplier_id = $this->suppliers->first()?->id;
        $this->po_store_id = $this->stores->first()?->id;
        $this->po_source = 'manual';
        $this->po_purchase_requisition_id = null;
        $this->po_payment_terms = '';
        $this->po_delivery_date = '';
        $this->po_shipping_cost = 0;
        $this->po_notes = '';
        $this->po_items = [];
        $this->showPOForm = false;
    }

    // --- Receipt methods ---
    public function createReceipt()
    {
        $this->resetReceiptForm();
        $this->showReceiptForm = true;
    }

    public function createReceiptFromPO(PurchaseOrder $po)
    {
        $this->resetReceiptForm();
        $this->receipt_purchase_order_id = $po->id;
        $this->receipt_items = $po->items->map(fn ($i) => [
            'purchase_order_item_id' => $i->id,
            'product_id' => $i->product_id,
            'product_name' => $i->product->name,
            'quantity_ordered' => $i->quantity,
            'quantity_accepted' => $i->quantity,
            'quantity_rejected' => 0,
            'lot_number' => '',
            'expiry_date' => '',
            'unit_cost' => $i->unit_price,
        ])->toArray();
        $this->showReceiptForm = true;
    }

    public function saveReceipt()
    {
        $this->validate([
            'receipt_purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_items' => 'required|array|min:1',
            'receipt_items.*.product_id' => 'required|exists:products,id',
            'receipt_items.*.quantity_accepted' => 'required|numeric|min:0',
            'receipt_items.*.quantity_rejected' => 'required|numeric|min:0',
            'receipt_items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::findOrFail($this->receipt_purchase_order_id);

            $receipt = GoodsReceipt::create([
                'company_id' => auth()->user()->company_id,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'store_id' => $po->store_id,
                'user_id' => auth()->id(),
                'reference' => GoodsReceipt::generateReference(),
                'status' => 'completed',
                'notes' => $this->receipt_notes,
            ]);

            foreach ($this->receipt_items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_accepted' => $item['quantity_accepted'],
                    'quantity_rejected' => $item['quantity_rejected'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?: null,
                    'unit_cost' => $item['unit_cost'],
                ]);

                if ($item['quantity_accepted'] > 0) {
                    $product = Product::find($item['product_id']);
                    $oldQty = $product->stock_quantity ?? 0;
                    $oldPmp = $product->purchase_price ?? 0;

                    $product->increment('stock_quantity', $item['quantity_accepted']);

                    // Mise à jour du PMP (prix moyen pondéré)
                    $newQty = $oldQty + $item['quantity_accepted'];
                    $newPmp = $newQty > 0
                        ? (($oldPmp * $oldQty) + ($item['unit_cost'] * $item['quantity_accepted'])) / $newQty
                        : $item['unit_cost'];
                    $product->update(['purchase_price' => round($newPmp, 2)]);

                    StockMovement::create([
                        'company_id' => auth()->user()->company_id,
                        'product_id' => $item['product_id'],
                        'store_id' => $po->store_id,
                        'user_id' => auth()->id(),
                        'type' => 'purchase_entry',
                        'quantity' => $item['quantity_accepted'],
                        'unit' => $product->unit_sale ?? 'unit',
                        'reference_type' => GoodsReceipt::class,
                        'reference_id' => $receipt->id,
                        'notes' => 'Réception commande '.$po->reference,
                        'stock_before' => $oldQty,
                        'stock_after' => $oldQty + $item['quantity_accepted'],
                    ]);
                }
            }

            $po->update(['status' => 'partially_received']);
            $fullyReceived = $po->items->every(function ($poItem) {
                $receivedQty = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->sum('quantity_accepted');

                return $receivedQty >= $poItem->quantity;
            });
            if ($fullyReceived) {
                $po->update(['status' => 'received']);
            }

            // Mise à jour dette fournisseur
            $supplier = Supplier::find($po->supplier_id);
            if ($supplier) {
                $supplier->increment('balance', $po->total);
            }

            // Mise à jour statut demande d'approvisionnement
            if ($po->purchase_requisition_id) {
                $reqStatus = $fullyReceived ? 'delivered_fully' : 'delivered_partially';
                PurchaseRequisition::where('id', $po->purchase_requisition_id)
                    ->whereIn('status', ['in_progress', 'approved'])
                    ->update(['status' => $reqStatus]);
            }

            DB::commit();
            $this->resetReceiptForm();
            session()->flash('message', 'Réception enregistrée. Stock mis à jour.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function resetReceiptForm()
    {
        $this->editingReceipt = null;
        $this->receipt_purchase_order_id = null;
        $this->receipt_notes = '';
        $this->receipt_items = [];
        $this->showReceiptForm = false;
    }

    // --- Return methods ---
    public function createReturn()
    {
        $this->resetReturnForm();
        $this->showReturnForm = true;
    }

    public function addReturnItem()
    {
        $this->return_items[] = ['product_id' => '', 'product_name' => '', 'quantity' => 1, 'unit_cost' => 0, 'reason' => ''];
    }

    public function removeReturnItem($index)
    {
        unset($this->return_items[$index]);
        $this->return_items = array_values($this->return_items);
    }

    public function saveReturn()
    {
        $this->validate([
            'return_supplier_id' => 'required|exists:suppliers,id',
            'return_store_id' => 'required|exists:stores,id',
            'return_items' => 'required|array|min:1',
            'return_items.*.product_id' => 'required|exists:products,id',
            'return_items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $return = SupplierReturn::create([
                'company_id' => auth()->user()->company_id,
                'supplier_id' => $this->return_supplier_id,
                'store_id' => $this->return_store_id,
                'user_id' => auth()->id(),
                'purchase_order_id' => $this->return_purchase_order_id ?: null,
                'goods_receipt_id' => $this->return_goods_receipt_id ?: null,
                'reference' => SupplierReturn::generateReference(),
                'reason_type' => $this->return_reason_type,
                'return_type' => $this->return_return_type,
                'status' => 'completed',
                'notes' => $this->return_notes,
            ]);

            foreach ($this->return_items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $lineTotal;

                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'reason' => $item['reason'] ?? null,
                ]);

                $product = Product::find($item['product_id']);
                $oldQty = $product->stock_quantity ?? 0;
                $product->decrement('stock_quantity', $item['quantity']);

                StockMovement::create([
                    'company_id' => auth()->user()->company_id,
                    'product_id' => $item['product_id'],
                    'store_id' => $this->return_store_id,
                    'user_id' => auth()->id(),
                    'type' => 'supplier_return',
                    'quantity' => -$item['quantity'],
                    'unit' => $product->unit_sale ?? 'unit',
                    'reference_type' => SupplierReturn::class,
                    'reference_id' => $return->id,
                    'notes' => 'Retour fournisseur '.$return->reference,
                    'stock_before' => $oldQty,
                    'stock_after' => $oldQty - $item['quantity'],
                ]);
            }

            // Génération de l'avoir fournisseur
            if ($totalAmount > 0) {
                SupplierCreditNote::create([
                    'company_id' => auth()->user()->company_id,
                    'supplier_id' => $this->return_supplier_id,
                    'supplier_return_id' => $return->id,
                    'reference' => SupplierCreditNote::generateReference(),
                    'amount' => $totalAmount,
                    'reason' => 'Retour '.($this->return_reason_type === 'defective' ? 'produits défectueux' : ($this->return_reason_type === 'error' ? 'erreur livraison' : 'produits expirés')),
                    'status' => 'pending',
                ]);

                Supplier::where('id', $this->return_supplier_id)->decrement('balance', $totalAmount);
            }

            DB::commit();
            $this->resetReturnForm();
            session()->flash('message', 'Retour fournisseur enregistré. Stock mis à jour. Avoir généré.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function resetReturnForm()
    {
        $this->editingReturn = null;
        $this->return_supplier_id = $this->suppliers->first()?->id;
        $this->return_store_id = $this->stores->first()?->id;
        $this->return_purchase_order_id = null;
        $this->return_goods_receipt_id = null;
        $this->return_reason_type = 'defective';
        $this->return_return_type = 'partial';
        $this->return_notes = '';
        $this->return_items = [];
        $this->showReturnForm = false;
    }

    // --- Product search ---
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
            ->get(['id', 'name', 'sku', 'purchase_price'])
            ->toArray();
    }

    public function selectProduct($productId, $context, $index)
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $items = match ($context) {
            'requisition' => 'requisition_items',
            'po' => 'po_items',
            'return' => 'return_items',
            default => null,
        };

        if ($items && isset($this->{$items}[$index])) {
            $this->{$items}[$index]['product_id'] = $product->id;
            $this->{$items}[$index]['product_name'] = $product->name.($product->sku ? " ({$product->sku})" : '');
            if ($context === 'po' && $this->{$items}[$index]['unit_price'] == 0) {
                $this->{$items}[$index]['unit_price'] = $product->purchase_price ?? 0;
            }
        }

        $this->productResults = [];
        $this->productSearch = '';
    }

    public function getPendingRequisitionsProperty()
    {
        return PurchaseRequisition::where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['draft', 'submitted', 'approved', 'in_progress'])
            ->count();
    }

    public function getPendingPOsProperty()
    {
        return PurchaseOrder::where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['draft', 'sent', 'partially_received'])
            ->count();
    }
}
