<?php

namespace App\Livewire\Pages\CustomerReturns;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
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

    // Return form
    public ?string $customerId = null;

    public ?string $saleId = null;

    public ?string $saleSearch = '';

    public string $returnType = 'partial';

    public string $reason = 'other';

    public string $reasonDescription = '';

    public bool $globalRestock = true;

    public string $refundMethod = 'cash';

    public string $notes = '';

    public array $cart = [];

    // Cancel sale
    public bool $showCancelModal = false;

    public ?int $cancelSaleId = null;

    public string $cancelReason = '';

    // Detail
    public bool $showDetail = false;

    public ?int $detailId = null;

    public array $customers = [];

    public array $products = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->customers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $returns = CustomerReturn::where('company_id', $companyId)
            ->with(['customer', 'user', 'items.product'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->detailId) {
            $detail = CustomerReturn::with(['items.product', 'customer', 'user', 'sale', 'approvedBy', 'creditNote'])->find($this->detailId);
        }

        // Search for sale by reference
        $saleResults = [];
        if (strlen($this->saleSearch) >= 2) {
            $saleResults = Sale::where('company_id', $companyId)
                ->where('reference', 'like', "%{$this->saleSearch}%")
                ->with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('livewire.pages.customer-returns.index', compact('returns', 'detail', 'saleResults'))
            ->layout('components.layouts.app', ['title' => 'Retours clients']);
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function selectSale(int $id)
    {
        $sale = Sale::with(['items.product', 'customer'])->findOrFail($id);
        $this->saleId = (string) $sale->id;
        $this->customerId = (string) $sale->customer_id;
        $this->saleSearch = $sale->reference;

        $this->cart = $sale->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'sale_item_id' => $item->id,
                'name' => $item->product_name,
                'qty' => abs($item->quantity),
                'price' => abs($item->unit_price),
                'subtotal' => abs($item->subtotal),
                'return_qty' => abs($item->quantity),
                'condition' => 'good',
                'restock' => $this->globalRestock,
            ];
        })->toArray();
    }

    public function updatedGlobalRestock()
    {
        $this->cart = collect($this->cart)->map(function ($item) {
            $item['restock'] = $this->globalRestock;

            return $item;
        })->toArray();
    }

    public function addProductToCart()
    {
        // For manual products not from a sale
    }

    public function removeFromCart(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function save()
    {
        $this->validate([
            'customerId' => 'required|exists:customers,id',
            'returnType' => 'required|in:total,partial,exchange',
            'reason' => 'required|string|max:50',
            'refundMethod' => 'required|string',
            'cart' => 'required|array|min:1',
        ]);

        $companyId = auth()->user()->company_id;
        $storeId = auth()->user()->store_id ?? auth()->user()->stores->first()?->id;

        DB::transaction(function () use ($companyId, $storeId) {
            $totalRefund = collect($this->cart)->sum(fn ($i) => $i['return_qty'] * $i['price']);
            $totalMarginImpact = 0;

            $return = CustomerReturn::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'customer_id' => $this->customerId,
                'sale_id' => $this->saleId ?: null,
                'reference' => CustomerReturn::generateReference(),
                'return_type' => $this->returnType,
                'reason' => $this->reason,
                'reason_description' => $this->reasonDescription,
                'restock' => $this->globalRestock,
                'refund_method' => $this->refundMethod,
                'refund_amount' => $totalRefund,
                'margin_impact' => 0,
                'status' => 'pending',
                'notes' => $this->notes,
            ]);

            foreach ($this->cart as $item) {
                $lineTotal = $item['return_qty'] * $item['price'];
                $product = Product::find($item['id']);
                $purchasePrice = $product?->purchase_price ?? 0;
                $marginImpact = ($item['price'] - $purchasePrice) * $item['return_qty'];
                $totalMarginImpact += $marginImpact;

                CustomerReturnItem::create([
                    'customer_return_id' => $return->id,
                    'product_id' => $item['id'],
                    'sale_item_id' => $item['sale_item_id'] ?? null,
                    'quantity' => $item['return_qty'],
                    'unit_price' => $item['price'],
                    'total' => $lineTotal,
                    'product_condition' => $item['condition'] ?? 'good',
                    'restock' => $item['restock'] ?? true,
                    'refund_amount' => $lineTotal,
                ]);

                // Restock
                if ($item['restock'] ?? true) {
                    $pivot = DB::table('product_store')
                        ->where('product_id', $item['id'])
                        ->where('store_id', $storeId)
                        ->first();

                    if ($pivot) {
                        DB::table('product_store')
                            ->where('id', $pivot->id)
                            ->increment('stock_quantity', $item['return_qty']);
                    }

                    StockMovement::create([
                        'company_id' => $companyId,
                        'store_id' => $storeId,
                        'product_id' => $item['id'],
                        'user_id' => auth()->id(),
                        'type' => 'return_in',
                        'quantity' => $item['return_qty'],
                        'unit_price' => $item['price'],
                        'total' => $lineTotal,
                        'reference' => $return->reference,
                        'notes' => 'Retour client: '.$this->reason,
                        'movement_date' => now(),
                    ]);
                }

                // Impact cash register if open
                $register = CashRegister::where('store_id', $storeId)
                    ->where('status', 'open')
                    ->first();

                if ($register) {
                    $register->addMovement([
                        'user_id' => auth()->id(),
                        'type' => 'customer_refund',
                        'direction' => 'out',
                        'amount' => $lineTotal,
                        'payment_method' => $this->refundMethod,
                        'description' => 'Remboursement retour '.$return->reference,
                        'reference' => $return->reference,
                        'sourceable_type' => CustomerReturn::class,
                        'sourceable_id' => $return->id,
                    ]);
                }
            }

            // Generate credit note (avoir) if refund_method is credit_note
            if ($this->refundMethod === 'credit_note') {
                $creditNote = Invoice::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'user_id' => auth()->id(),
                    'customer_id' => $this->customerId,
                    'reference' => Invoice::generateReference(),
                    'type' => 'credit_note',
                    'status' => 'paid',
                    'subtotal' => $totalRefund,
                    'tax_amount' => 0,
                    'discount' => 0,
                    'total' => $totalRefund,
                    'paid_amount' => $totalRefund,
                    'amount_due' => 0,
                    'issue_date' => now(),
                    'due_date' => now(),
                    'notes' => 'Avoir généré depuis le retour '.$return->reference,
                    'paid_at' => now(),
                ]);

                foreach ($this->cart as $item) {
                    $lineTotal = $item['return_qty'] * $item['price'];
                    InvoiceItem::create([
                        'invoice_id' => $creditNote->id,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'product_reference' => null,
                        'unit' => 'piece',
                        'quantity' => -abs($item['return_qty']),
                        'unit_price' => $item['price'],
                        'discount' => 0,
                        'tax_rate' => 0,
                        'subtotal' => -$lineTotal,
                    ]);
                }

                $return->update(['credit_note_id' => $creditNote->id]);
            }

            $return->update([
                'status' => 'completed',
                'margin_impact' => -abs($totalMarginImpact),
            ]);
        });

        $this->showForm = false;
        $this->resetForm();
    }

    public function approve(int $id)
    {
        $return = CustomerReturn::findOrFail($id);
        $return->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function reject(int $id)
    {
        $return = CustomerReturn::findOrFail($id);
        $return->update(['status' => 'rejected']);
    }

    // Cancel Sale (8.52)
    public function confirmCancelSale(int $id)
    {
        $this->cancelSaleId = $id;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function cancelSale()
    {
        abort_unless(auth()->user()->can('cancel sales'), 403);

        $this->validate([
            'cancelReason' => 'required|string|min:5|max:500',
        ]);

        $sale = Sale::with('items', 'customer', 'store')->findOrFail($this->cancelSaleId);
        $companyId = auth()->user()->company_id;
        $storeId = auth()->user()->store_id ?? $sale->store_id;

        DB::transaction(function () use ($sale, $companyId, $storeId) {
            // Restore stock
            foreach ($sale->items as $item) {
                $pivot = DB::table('product_store')
                    ->where('product_id', $item->product_id)
                    ->where('store_id', $sale->store_id)
                    ->first();

                if ($pivot) {
                    DB::table('product_store')
                        ->where('id', $pivot->id)
                        ->increment('stock_quantity', abs($item->quantity));
                }

                StockMovement::create([
                    'company_id' => $companyId,
                    'store_id' => $sale->store_id,
                    'product_id' => $item->product_id,
                    'user_id' => auth()->id(),
                    'type' => 'cancellation_in',
                    'quantity' => abs($item->quantity),
                    'unit_price' => abs($item->unit_price),
                    'total' => abs($item->subtotal),
                    'reference' => 'CNC-'.$sale->reference,
                    'notes' => 'Annulation vente: '.$this->cancelReason,
                    'movement_date' => now(),
                ]);
            }

            // Impact cash register
            $register = CashRegister::where('store_id', $sale->store_id)
                ->where('status', 'open')
                ->first();

            if ($register && $sale->paid_amount > 0) {
                $register->addMovement([
                    'user_id' => auth()->id(),
                    'type' => 'customer_refund',
                    'direction' => 'out',
                    'amount' => $sale->paid_amount,
                    'payment_method' => $sale->payment_method ?? 'cash',
                    'description' => 'Annulation vente '.$sale->reference.': '.$this->cancelReason,
                    'reference' => 'CNC-'.$sale->reference,
                    'sourceable_type' => Sale::class,
                    'sourceable_id' => $sale->id,
                ]);
            }

            $sale->update([
                'status' => 'cancelled',
                'notes' => ($sale->notes ? $sale->notes."\n" : '').'Annulation: '.$this->cancelReason,
            ]);
        });

        $this->showCancelModal = false;
        $this->cancelSaleId = null;
        $this->cancelReason = '';

        session()->flash('success', 'Vente annulée avec succès. Document d\'annulation: CNC-'.$sale->reference);
    }

    public function viewDetail(int $id)
    {
        $this->detailId = $id;
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->detailId = null;
    }

    public function resetForm()
    {
        $this->customerId = null;
        $this->saleId = null;
        $this->saleSearch = '';
        $this->returnType = 'partial';
        $this->reason = 'other';
        $this->reasonDescription = '';
        $this->globalRestock = true;
        $this->refundMethod = 'cash';
        $this->notes = '';
        $this->cart = [];
        $this->products = [];
    }
}
