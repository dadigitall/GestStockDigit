<?php

namespace App\Livewire\Pages\Wholesale;

use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $barcode = '';

    public $cart = [];

    public $total = 0;

    public $subtotal = 0;

    public $taxAmount = 0;

    public $discount = 0;

    public $paidAmount = 0;

    public $changeAmount = 0;

    public $paymentMethod = 'cash';

    public $secondaryPaymentMethod = '';

    public $secondaryAmount = 0;

    public $giftCardCode = '';

    public $giftCard = null;

    public $customerId = null;

    public $customers = [];

    public $customerSearch = '';

    public $showPaymentModal = false;

    public $saleCompleted = false;

    public $lastSale = null;

    public $notes = '';

    public $expectedDeliveryDate = '';

    public $commercialTerms = '';

    public function mount()
    {
        $this->customers = Customer::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereIn('type', ['professional', 'reseller', 'wholesaler'])
            ->orderBy('name')
            ->get();
    }

    public function updatedBarcode()
    {
        $code = trim($this->barcode);
        if (strlen($code) < 2) {
            return;
        }

        $product = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where('barcode', $code)
            ->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->barcode = '';
        } else {
            session()->flash('error', "Aucun produit trouvé avec le code-barres '{$code}'.");
            $this->barcode = '';
        }
    }

    public function updatedSearch()
    {
        $this->dispatch('$refresh');
    }

    public function updatedCustomerId()
    {
        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
            $this->commercialTerms = $customer?->commercial_terms ?? '';
        } else {
            $this->commercialTerms = '';
        }
        $this->repriceCart();
    }

    public function repriceCart()
    {
        $customer = $this->customerId ? Customer::find($this->customerId) : null;

        $this->cart = collect($this->cart)->map(function ($item) use ($customer) {
            $product = Product::find($item['id']);
            if ($product) {
                $price = $product->getPriceForCustomer($customer, $item['qty'], auth()->user()->store_id);
                $item['price'] = $price;
                $item['subtotal'] = $item['qty'] * $price;
            }

            return $item;
        })->toArray();

        $this->calculateTotals();
    }

    public function addToCart($productId)
    {
        $product = Product::where('company_id', auth()->user()->company_id)
            ->findOrFail($productId);

        if ($product->is_stockable) {
            $needed = $product->convertToPurchaseQuantity(1);
            if ($needed > $product->stock_quantity) {
                session()->flash('error', "Stock insuffisant pour {$product->name}");

                return;
            }
        }

        $existing = collect($this->cart)->firstWhere('id', $productId);

        if ($existing) {
            $this->cart = collect($this->cart)->map(function ($item) use ($product, $productId) {
                if ($item['id'] == $productId) {
                    $newQty = $item['qty'] + 1;
                    $requestedQty = $product->convertToPurchaseQuantity($newQty);
                    if ($product->is_stockable && $requestedQty > $product->stock_quantity) {
                        session()->flash('error', "Stock insuffisant pour {$product->name}");

                        return $item;
                    }
                    $customer = $this->customerId ? Customer::find($this->customerId) : null;
                    $item['price'] = $product->getPriceForCustomer($customer, $newQty, auth()->user()->store_id);
                    $item['qty'] = $newQty;
                    $item['subtotal'] = $newQty * $item['price'];
                }

                return $item;
            })->toArray();
        } else {
            $customer = $this->customerId ? Customer::find($this->customerId) : null;
            $price = $product->getPriceForCustomer($customer, 1, auth()->user()->store_id);

            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'reference' => $product->reference,
                'price' => $price,
                'qty' => 1,
                'subtotal' => $price,
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
            $product = Product::find($item['id']);
            if ($product && $product->is_stockable) {
                $requestedQty = $product->convertToPurchaseQuantity($qty);
                if ($requestedQty > $product->stock_quantity) {
                    session()->flash('error', "Stock insuffisant pour {$item['name']}");

                    return;
                }
            }
            $item['qty'] = $qty;
            $customer = $this->customerId ? Customer::find($this->customerId) : null;
            $price = $product?->getPriceForCustomer($customer, $qty, auth()->user()->store_id) ?? $item['price'];
            $item['price'] = $price;
            $item['subtotal'] = $qty * $price;
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
        // Enforce company discount max rate
        $company = auth()->user()->company;
        if ($company && $company->discount_max_rate > 0) {
            $maxDiscount = ($this->subtotal + $this->taxAmount) * ($company->discount_max_rate / 100);
            $this->discount = min($this->discount, $maxDiscount);
        }
        $this->calculateTotals();
    }

    public function updatedPaidAmount()
    {
        $this->changeAmount = max(0, (float) $this->paidAmount - $this->total + (float) $this->secondaryAmount);
    }

    public function updatedSecondaryAmount()
    {
        $this->changeAmount = max(0, (float) $this->paidAmount - $this->total + (float) $this->secondaryAmount);
    }

    public function lookupGiftCard()
    {
        $code = trim($this->giftCardCode);
        if (empty($code)) {
            $this->giftCard = null;

            return;
        }

        $this->giftCard = GiftCard::active()
            ->where('company_id', auth()->user()->company_id)
            ->where('code', $code)
            ->first();

        if (! $this->giftCard) {
            session()->flash('error', 'Bon d\'achat introuvable ou épuisé.');
        }
    }

    public function openPayment()
    {
        if (empty($this->cart)) {
            return;
        }
        $this->paidAmount = $this->total;
        $this->changeAmount = 0;
        $this->secondaryPaymentMethod = '';
        $this->secondaryAmount = 0;
        $this->giftCardCode = '';
        $this->giftCard = null;
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

        $effectivePaid = (float) $this->paidAmount + (float) $this->secondaryAmount;

        if ($this->paymentMethod === 'gift_card' && $this->giftCard) {
            $giftAmount = min((float) $this->giftCard->balance, $this->total);
            $effectivePaid = (float) $this->paidAmount + $giftAmount;
        }

        if ($this->paymentMethod === 'credit' && $this->customerId) {
            $customer = Customer::find($this->customerId);
            if ($customer && $customer->wouldExceedCredit($this->total - $effectivePaid)) {
                session()->flash('error', 'Ce client a atteint son plafond de crédit.');

                return;
            }
        }

        $walletAmount = 0;
        if ($this->paymentMethod === 'wallet') {
            $walletAmount += (float) $this->paidAmount;
        }
        if ($this->secondaryPaymentMethod === 'wallet') {
            $walletAmount += (float) $this->secondaryAmount;
        }
        if ($walletAmount > 0 && $this->customerId) {
            $customer = Customer::find($this->customerId);
            if (! $customer || $customer->balance < $walletAmount) {
                session()->flash('error', 'Solde portefeuille insuffisant.');

                return;
            }
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->company_id;
            $storeId = auth()->user()->store_id;

            $primaryPaid = (float) $this->paidAmount;
            $secondaryPaid = (float) $this->secondaryAmount;

            if ($this->paymentMethod === 'gift_card' && $this->giftCard) {
                $giftAmount = min((float) $this->giftCard->balance, $this->total);
                $this->giftCard->decrement('balance', $giftAmount);
                if ($this->giftCard->balance <= 0) {
                    $this->giftCard->update(['status' => 'exhausted']);
                }
                $primaryPaid = $giftAmount;
                $secondaryPaid = (float) $this->paidAmount;
            }

            $sale = Sale::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'customer_id' => $this->customerId ?: null,
                'reference' => Sale::generateReference(),
                'type' => 'wholesale',
                'status' => 'completed',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount' => $this->discount,
                'total' => $this->total,
                'paid_amount' => $primaryPaid + $secondaryPaid,
                'change_amount' => max(0, $primaryPaid + $secondaryPaid - $this->total),
                'payment_method' => $this->paymentMethod,
                'payment_method_secondary' => $this->secondaryPaymentMethod ?: null,
                'payment_secondary_amount' => $this->secondaryPaymentMethod ? $secondaryPaid : null,
                'notes' => $this->notes,
                'sold_at' => now(),
                'expected_delivery_date' => $this->expectedDeliveryDate ?: null,
                'commercial_terms' => $this->commercialTerms ?: null,
            ]);

            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'product_reference' => $item['reference'],
                    'unit' => $product?->unit_sale ?? 'piece',
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                if ($product && $product->is_stockable) {
                    $stockQty = $product->convertToPurchaseQuantity($item['qty']);
                    $stockBefore = $product->stock_quantity;
                    $product->decrement('stock_quantity', $stockQty);

                    StockMovement::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'store_id' => $storeId,
                        'user_id' => auth()->id(),
                        'type' => 'wholesale_sale',
                        'quantity' => -$stockQty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore - $stockQty,
                        'unit_cost' => $product->purchase_price,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'notes' => "Vente gros {$sale->reference}",
                    ]);
                }
            }

            if ($this->paymentMethod === 'wallet' && $this->customerId && $primaryPaid > 0) {
                Customer::find($this->customerId)->decrement('balance', $primaryPaid);
            }
            if ($this->secondaryPaymentMethod === 'wallet' && $this->customerId && $secondaryPaid > 0) {
                Customer::find($this->customerId)->decrement('balance', $secondaryPaid);
            }

            DB::commit();

            $this->lastSale = $sale->load('items');
            $this->saleCompleted = true;
            $this->showPaymentModal = false;
            $this->cart = [];
            $this->reset(['subtotal', 'taxAmount', 'discount', 'total', 'paidAmount', 'changeAmount', 'notes', 'customerId', 'barcode', 'expectedDeliveryDate', 'commercialTerms']);

            session()->flash('success', "Vente en gros {$sale->reference} validée !");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation : '.$e->getMessage());
        }
    }

    public function newSale()
    {
        $this->saleCompleted = false;
        $this->lastSale = null;
        $this->reset(['search', 'cart', 'subtotal', 'taxAmount', 'discount', 'total', 'paidAmount', 'changeAmount', 'notes', 'customerId', 'barcode', 'expectedDeliveryDate', 'commercialTerms']);
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

        return view('livewire.pages.wholesale.index', compact('products'))
            ->layout('layouts.app', ['header' => 'Vente en gros']);
    }
}
