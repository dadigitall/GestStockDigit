<?php

namespace App\Livewire\Pages\CustomerOrders;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
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

    public ?string $customerId = null;

    public ?string $orderDate = null;

    public ?string $expectedDeliveryDate = null;

    public string $notes = '';

    public array $cart = [];

    public float $subtotal = 0;

    public float $taxAmount = 0;

    public float $discount = 0;

    public float $total = 0;

    public bool $showDetail = false;

    public ?int $detailId = null;

    public string $productSearch = '';

    public array $productResults = [];

    public array $customers = [];

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->customers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('type', ['professional', 'reseller', 'wholesaler'])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        $this->orderDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $orders = CustomerOrder::where('company_id', $companyId)
            ->with(['customer', 'user'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->detailId) {
            $detail = CustomerOrder::where('company_id', $companyId)
                ->with(['items.product', 'customer', 'user'])
                ->find($this->detailId);
        }

        return view('livewire.pages.customer-orders.index', compact('orders', 'detail'))
            ->layout('layouts.app', ['header' => 'Commandes clients']);
    }

    public function updatedProductSearch()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];

            return;
        }

        $companyId = auth()->user()->company_id;
        $this->productResults = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('reference', 'like', "%{$this->productSearch}%")
                    ->orWhere('barcode', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'reference', 'sale_price', 'tax_rate', 'stock_quantity', 'unit_sale'])
            ->toArray();
    }

    public function addToCart($productId)
    {
        $product = Product::where('company_id', auth()->user()->company_id)
            ->findOrFail($productId);

        $existing = collect($this->cart)->firstWhere('id', $productId);
        if ($existing) {
            session()->flash('error', 'Ce produit est déjà dans la commande.');

            return;
        }

        $price = (float) ($product->sale_price ?? 0);
        $this->cart[] = [
            'id' => $product->id,
            'name' => $product->name,
            'reference' => $product->reference,
            'unit' => $product->unit_sale ?? 'piece',
            'qty' => 1,
            'quantity_prepared' => 0,
            'quantity_delivered' => 0,
            'price' => $price,
            'discount' => 0,
            'tax_rate' => (float) ($product->tax_rate ?? 0),
            'subtotal' => $price,
        ];

        $this->productSearch = '';
        $this->productResults = [];
        $this->calculateTotals();
    }

    public function updateItem($index, $field, $value)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index][$field] = (float) $value;

        if ($this->cart[$index]['qty'] <= 0) {
            $this->cart[$index]['qty'] = 1;
        }
        if ($this->cart[$index]['price'] < 0) {
            $this->cart[$index]['price'] = 0;
        }
        if ($this->cart[$index]['discount'] < 0) {
            $this->cart[$index]['discount'] = 0;
        }
        if ($this->cart[$index]['tax_rate'] < 0) {
            $this->cart[$index]['tax_rate'] = 0;
        }

        $qty = $this->cart[$index]['qty'];
        $price = $this->cart[$index]['price'];
        $discount = $this->cart[$index]['discount'];
        $lineTotal = $qty * $price;
        $lineDiscount = $lineTotal * ($discount / 100);
        $this->cart[$index]['subtotal'] = $lineTotal - $lineDiscount;

        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum(function ($item) {
            $lineTotal = $item['qty'] * $item['price'];
            $lineDiscount = $lineTotal * ($item['discount'] / 100);

            return $lineTotal - $lineDiscount;
        });

        $this->taxAmount = collect($this->cart)->sum(function ($item) {
            $lineTotal = $item['qty'] * $item['price'];
            $lineDiscount = $lineTotal * ($item['discount'] / 100);
            $lineNet = $lineTotal - $lineDiscount;

            return $lineNet * ($item['tax_rate'] / 100);
        });

        $this->total = $this->subtotal + $this->taxAmount - $this->discount;
        $this->total = max(0, $this->total);
    }

    public function updatedDiscount()
    {
        $this->discount = max(0, (float) $this->discount);
        $this->calculateTotals();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->customerId = null;
        $this->orderDate = now()->format('Y-m-d');
        $this->expectedDeliveryDate = null;
        $this->notes = '';
        $this->cart = [];
        $this->subtotal = 0;
        $this->taxAmount = 0;
        $this->discount = 0;
        $this->total = 0;
        $this->productSearch = '';
        $this->productResults = [];
        $this->showForm = false;
        $this->showDetail = false;
        $this->detailId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'customerId' => 'required|exists:customers,id',
            'orderDate' => 'required|date',
            'expectedDeliveryDate' => 'nullable|date|after_or_equal:orderDate',
            'cart' => 'required|array|min:1',
        ]);

        $companyId = auth()->user()->company_id;

        DB::beginTransaction();
        try {
            $order = CustomerOrder::updateOrCreate(
                ['id' => $this->editId],
                [
                    'company_id' => $companyId,
                    'customer_id' => $this->customerId,
                    'user_id' => auth()->id(),
                    'store_id' => auth()->user()->store_id,
                    'order_date' => $this->orderDate,
                    'expected_delivery_date' => $this->expectedDeliveryDate,
                    'notes' => $this->notes,
                    'subtotal' => $this->subtotal,
                    'tax_amount' => $this->taxAmount,
                    'discount' => $this->discount,
                    'total' => $this->total,
                ]
            );

            if (! $this->editId) {
                $order->reference = CustomerOrder::generateReference();
                $order->status = 'draft';
                $order->save();
            }

            if ($this->editId) {
                $order->items()->delete();
            }

            $items = [];
            foreach ($this->cart as $data) {
                $lineTotal = $data['qty'] * $data['price'];
                $lineDiscount = $lineTotal * ($data['discount'] / 100);
                $items[] = [
                    'customer_order_id' => $order->id,
                    'product_id' => $data['id'],
                    'product_name' => $data['name'],
                    'product_reference' => $data['reference'],
                    'unit' => $data['unit'] ?? 'piece',
                    'quantity' => $data['qty'],
                    'quantity_prepared' => $data['quantity_prepared'] ?? 0,
                    'quantity_delivered' => $data['quantity_delivered'] ?? 0,
                    'unit_price' => $data['price'],
                    'discount' => $data['discount'],
                    'tax_rate' => $data['tax_rate'],
                    'subtotal' => $lineTotal - $lineDiscount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            CustomerOrderItem::insert($items);

            DB::commit();
            $this->resetForm();
            session()->flash('message', $this->editId
                ? "Commande {$order->reference} mise à jour."
                : "Commande {$order->reference} créée.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $order = CustomerOrder::with('items.product')->findOrFail($id);
        if ($order->status !== 'draft') {
            session()->flash('error', 'Seul un brouillon peut être modifié.');

            return;
        }

        $this->resetForm();
        $this->editId = $order->id;
        $this->customerId = (string) $order->customer_id;
        $this->orderDate = $order->order_date?->format('Y-m-d');
        $this->expectedDeliveryDate = $order->expected_delivery_date?->format('Y-m-d');
        $this->notes = $order->notes ?? '';
        $this->discount = (float) ($order->discount ?? 0);

        $this->cart = $order->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id),
                'reference' => $item->product_reference ?? '',
                'unit' => $item->unit ?? 'piece',
                'qty' => (float) ($item->quantity ?? 1),
                'quantity_prepared' => (float) ($item->quantity_prepared ?? 0),
                'quantity_delivered' => (float) ($item->quantity_delivered ?? 0),
                'price' => (float) ($item->unit_price ?? 0),
                'discount' => (float) ($item->discount ?? 0),
                'tax_rate' => (float) ($item->tax_rate ?? 0),
                'subtotal' => (float) ($item->subtotal ?? 0),
            ];
        })->toArray();

        $this->calculateTotals();
        $this->showForm = true;
    }

    public function confirm($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if ($order->status !== 'draft') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $order->update(['status' => 'confirmed']);
        session()->flash('message', "Commande {$order->reference} confirmée.");
    }

    public function startPreparing($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if (! in_array($order->status, ['confirmed'])) {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $order->update(['status' => 'preparing']);
        session()->flash('message', "Préparation commencée pour {$order->reference}.");
    }

    public function markReady($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if ($order->status !== 'preparing') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $order->update(['status' => 'ready']);
        session()->flash('message', "Commande {$order->reference} prête.");
    }

    public function markDelivered($id)
    {
        $order = CustomerOrder::with('items')->findOrFail($id);
        if ($order->status !== 'ready') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        DB::beginTransaction();
        try {
            foreach ($order->items as $item) {
                $item->update([
                    'quantity_delivered' => $item->quantity,
                ]);
            }

            $order->update(['status' => 'delivered']);
            DB::commit();
            session()->flash('message', "Commande {$order->reference} livrée.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function markInvoiced($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if (! in_array($order->status, ['delivered', 'ready'])) {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $order->update(['status' => 'invoiced']);
        session()->flash('message', "Commande {$order->reference} facturée.");
    }

    public function markPaid($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if ($order->status !== 'invoiced') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $order->update(['status' => 'paid']);
        session()->flash('message', "Commande {$order->reference} payée.");
    }

    public function cancel($id)
    {
        $order = CustomerOrder::findOrFail($id);
        if (! in_array($order->status, ['draft', 'confirmed', 'preparing'])) {
            session()->flash('error', 'Cette commande ne peut plus être annulée.');

            return;
        }

        $order->update(['status' => 'cancelled']);
        session()->flash('message', "Commande {$order->reference} annulée.");
    }

    public function delete($id)
    {
        $order = CustomerOrder::findOrFail($id);
        $ref = $order->reference;
        $order->delete();
        session()->flash('message', "Commande {$ref} supprimée.");
    }

    public function view($id)
    {
        $this->detailId = $id;
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->detailId = null;
        $this->showDetail = false;
    }

    public function statusBadge($status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
            'confirmed' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
            'preparing' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'ready' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'delivered' => 'bg-teal-100 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300',
            'invoiced' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300',
            'paid' => 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300',
            'cancelled' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        };
    }

    public function statusLabel($status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'confirmed' => 'Confirmée',
            'preparing' => 'En préparation',
            'ready' => 'Prête',
            'delivered' => 'Livrée',
            'invoiced' => 'Facturée',
            'paid' => 'Payée',
            'cancelled' => 'Annulée',
            default => $status,
        };
    }
}
