<?php

namespace App\Livewire\Pages\Pos;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $cart = [];

    public $total = 0;

    public $subtotal = 0;

    public $taxAmount = 0;

    public $discount = 0;

    public $paidAmount = 0;

    public $changeAmount = 0;

    public $paymentMethod = 'cash';

    public $customerId = null;

    public $customerSearch = '';

    public $customers = [];

    public $showPaymentModal = false;

    public $saleCompleted = false;

    public $lastSale = null;

    public $notes = '';

    public function mount()
    {
        $this->customers = Customer::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch()
    {
        $this->dispatch('$refresh');
    }

    public function addToCart($productId)
    {
        $product = Product::where('company_id', auth()->user()->company_id)
            ->findOrFail($productId);

        if ($product->stock_quantity <= 0 && $product->is_stockable) {
            session()->flash('error', "Stock insuffisant pour {$product->name}");

            return;
        }

        $existing = collect($this->cart)->firstWhere('id', $productId);

        if ($existing) {
            $this->cart = collect($this->cart)->map(function ($item) use ($product, $productId) {
                if ($item['id'] == $productId) {
                    if ($product->is_stockable && $item['qty'] >= $product->stock_quantity) {
                        session()->flash('error', "Stock insuffisant pour {$product->name}");

                        return $item;
                    }
                    $item['qty']++;
                    $item['subtotal'] = $item['qty'] * $item['price'];
                }

                return $item;
            })->toArray();
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'reference' => $product->reference,
                'price' => $product->sale_price,
                'qty' => 1,
                'subtotal' => $product->sale_price,
                'stock' => $product->stock_quantity,
                'tax_rate' => $product->tax_rate,
            ];
        }

        $this->calculateTotals();
    }

    public function updateQty($index, $qty)
    {
        $qty = max(0, (int) $qty);

        if ($qty <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        } else {
            $item = &$this->cart[$index];
            if ($item['stock'] !== null && $qty > $item['stock']) {
                session()->flash('error', "Stock insuffisant pour {$item['name']}");

                return;
            }
            $item['qty'] = $qty;
            $item['subtotal'] = $qty * $item['price'];
        }
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
        $this->subtotal = collect($this->cart)->sum('subtotal');
        $this->taxAmount = collect($this->cart)->sum(function ($item) {
            return $item['subtotal'] * ($item['tax_rate'] / 100);
        });
        $this->total = $this->subtotal + $this->taxAmount - $this->discount;
        $this->total = max(0, $this->total);
    }

    public function updatedDiscount()
    {
        $this->discount = max(0, min((float) $this->discount, $this->subtotal + $this->taxAmount));
        $this->calculateTotals();
    }

    public function updatedPaidAmount()
    {
        $this->changeAmount = max(0, (float) $this->paidAmount - $this->total);
    }

    public function openPayment()
    {
        if (empty($this->cart)) {
            return;
        }
        $this->paidAmount = $this->total;
        $this->changeAmount = 0;
        $this->showPaymentModal = true;
        $this->saleCompleted = false;
    }

    public function closePayment()
    {
        $this->showPaymentModal = false;
    }

    public function confirmSale()
    {
        $this->validate([
            'paidAmount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|string',
        ]);

        if ((float) $this->paidAmount < $this->total) {
            session()->flash('error', 'Le montant payé est inférieur au total.');

            return;
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->company_id;
            $storeId = auth()->user()->store_id;

            $sale = Sale::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'customer_id' => $this->customerId ?: null,
                'reference' => Sale::generateReference(),
                'type' => 'retail',
                'status' => 'completed',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount' => $this->discount,
                'total' => $this->total,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->changeAmount,
                'payment_method' => $this->paymentMethod,
                'notes' => $this->notes,
                'sold_at' => now(),
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'product_reference' => $item['reference'],
                    'unit' => 'piece',
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                $product = Product::find($item['id']);
                if ($product && $product->is_stockable) {
                    $stockBefore = $product->stock_quantity;
                    $product->decrement('stock_quantity', $item['qty']);

                    StockMovement::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'store_id' => $storeId,
                        'user_id' => auth()->id(),
                        'type' => 'sale',
                        'quantity' => -$item['qty'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore - $item['qty'],
                        'unit_cost' => $product->purchase_price,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'notes' => "Vente {$sale->reference}",
                    ]);
                }
            }

            DB::commit();

            $this->lastSale = $sale->load('items');
            $this->saleCompleted = true;
            $this->showPaymentModal = false;
            $this->cart = [];
            $this->reset(['subtotal', 'taxAmount', 'discount', 'total', 'paidAmount', 'changeAmount', 'notes', 'customerId']);

            session()->flash('success', "Vente {$sale->reference} validée !");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation : '.$e->getMessage());
        }
    }

    public function newSale()
    {
        $this->saleCompleted = false;
        $this->lastSale = null;
        $this->reset(['search', 'cart', 'subtotal', 'taxAmount', 'discount', 'total', 'paidAmount', 'changeAmount', 'notes', 'customerId']);
    }

    public function render()
    {
        $products = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->take(24)
            ->get();

        return view('livewire.pages.pos.index', compact('products'))
            ->layout('layouts.app', ['header' => 'Point de vente']);
    }
}
