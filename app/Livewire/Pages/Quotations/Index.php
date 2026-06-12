<?php

namespace App\Livewire\Pages\Quotations;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
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

    public ?string $validityDate = null;

    public string $commercialTerms = '';

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
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $quotations = Quotation::where('company_id', $companyId)
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
            $detail = Quotation::where('company_id', $companyId)
                ->with(['items.product', 'customer', 'user', 'convertedInvoice'])
                ->find($this->detailId);
        }

        return view('livewire.pages.quotations.index', compact('quotations', 'detail'))
            ->layout('layouts.app', ['header' => 'Devis']);
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
            session()->flash('error', 'Ce produit est déjà dans le devis.');

            return;
        }

        $price = (float) ($product->sale_price ?? 0);
        $this->cart[] = [
            'id' => $product->id,
            'name' => $product->name,
            'reference' => $product->reference,
            'unit' => $product->unit_sale ?? 'piece',
            'qty' => 1,
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
        $this->validityDate = null;
        $this->commercialTerms = '';
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
            'validityDate' => 'nullable|date|after:today',
            'cart' => 'required|array|min:1',
        ]);

        $companyId = auth()->user()->company_id;

        DB::beginTransaction();
        try {
            $quotation = Quotation::updateOrCreate(
                ['id' => $this->editId],
                [
                    'company_id' => $companyId,
                    'customer_id' => $this->customerId,
                    'user_id' => auth()->id(),
                    'store_id' => auth()->user()->store_id,
                    'validity_date' => $this->validityDate,
                    'commercial_terms' => $this->commercialTerms,
                    'notes' => $this->notes,
                    'subtotal' => $this->subtotal,
                    'tax_amount' => $this->taxAmount,
                    'discount' => $this->discount,
                    'total' => $this->total,
                ]
            );

            if (! $this->editId) {
                $quotation->reference = Quotation::generateReference();
                $quotation->status = 'draft';
                $quotation->save();
            }

            if ($this->editId) {
                $quotation->items()->delete();
            }

            $items = [];
            foreach ($this->cart as $data) {
                $lineTotal = $data['qty'] * $data['price'];
                $lineDiscount = $lineTotal * ($data['discount'] / 100);
                $items[] = [
                    'quotation_id' => $quotation->id,
                    'product_id' => $data['id'],
                    'product_name' => $data['name'],
                    'product_reference' => $data['reference'],
                    'unit' => $data['unit'] ?? 'piece',
                    'quantity' => $data['qty'],
                    'unit_price' => $data['price'],
                    'discount' => $data['discount'],
                    'tax_rate' => $data['tax_rate'],
                    'subtotal' => $lineTotal - $lineDiscount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            QuotationItem::insert($items);

            DB::commit();
            $this->resetForm();
            session()->flash('message', $this->editId
                ? "Devis {$quotation->reference} mis à jour."
                : "Devis {$quotation->reference} créé.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $quotation = Quotation::with('items.product')->findOrFail($id);
        if ($quotation->status !== 'draft') {
            session()->flash('error', 'Seul un brouillon peut être modifié.');

            return;
        }

        $this->resetForm();
        $this->editId = $quotation->id;
        $this->customerId = (string) $quotation->customer_id;
        $this->validityDate = $quotation->validity_date?->format('Y-m-d');
        $this->commercialTerms = $quotation->commercial_terms ?? '';
        $this->notes = $quotation->notes ?? '';
        $this->discount = (float) ($quotation->discount ?? 0);

        $this->cart = $quotation->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id),
                'reference' => $item->product_reference ?? '',
                'unit' => $item->unit ?? 'piece',
                'qty' => (float) ($item->quantity ?? 1),
                'price' => (float) ($item->unit_price ?? 0),
                'discount' => (float) ($item->discount ?? 0),
                'tax_rate' => (float) ($item->tax_rate ?? 0),
                'subtotal' => (float) ($item->subtotal ?? 0),
            ];
        })->toArray();

        $this->calculateTotals();
        $this->showForm = true;
    }

    public function send($id)
    {
        $quotation = Quotation::findOrFail($id);
        if ($quotation->status !== 'draft') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        session()->flash('message', "Devis {$quotation->reference} envoyé au client.");
    }

    public function accept($id)
    {
        $quotation = Quotation::findOrFail($id);
        if ($quotation->status !== 'sent') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $quotation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
        session()->flash('message', "Devis {$quotation->reference} accepté.");
    }

    public function refuse($id)
    {
        $quotation = Quotation::findOrFail($id);
        if ($quotation->status !== 'sent') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $quotation->update([
            'status' => 'refused',
            'refused_at' => now(),
        ]);
        session()->flash('message', "Devis {$quotation->reference} refusé.");
    }

    public function cancel($id)
    {
        $quotation = Quotation::findOrFail($id);
        if (! in_array($quotation->status, ['draft', 'sent'])) {
            session()->flash('error', 'Ce devis ne peut plus être annulé.');

            return;
        }

        $quotation->update(['status' => 'cancelled']);
        session()->flash('message', "Devis {$quotation->reference} annulé.");
    }

    public function convertToInvoice($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        if ($quotation->status !== 'accepted') {
            session()->flash('error', 'Seul un devis accepté peut être transformé en facture.');

            return;
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->company_id;

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $quotation->customer_id,
                'store_id' => $quotation->store_id ?? auth()->user()->store_id,
                'user_id' => auth()->id(),
                'reference' => Invoice::generateReference(),
                'type' => 'invoice',
                'status' => 'draft',
                'quotation_id' => $quotation->id,
                'subtotal' => $quotation->subtotal,
                'tax_amount' => $quotation->tax_amount,
                'discount' => $quotation->discount,
                'total' => $quotation->total,
                'paid_amount' => 0,
                'amount_due' => $quotation->total,
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'notes' => "Facture issue du devis {$quotation->reference}",
            ]);

            $invoiceItems = [];
            foreach ($quotation->items as $item) {
                $invoiceItems[] = [
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_reference' => $item->product_reference,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $item->subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            InvoiceItem::insert($invoiceItems);

            $quotation->update([
                'status' => 'converted',
                'converted_to_invoice_id' => $invoice->id,
            ]);

            DB::commit();
            session()->flash('message', "Devis {$quotation->reference} transformé en facture {$invoice->reference}.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        $quotation = Quotation::findOrFail($id);
        $ref = $quotation->reference;
        $quotation->delete();
        session()->flash('message', "Devis {$ref} supprimé.");
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
            'sent' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
            'accepted' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'refused' => 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300',
            'expired' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'converted' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300',
            'cancelled' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        };
    }

    public function statusLabel($status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'refused' => 'Refusé',
            'expired' => 'Expiré',
            'converted' => 'Transformé',
            'cancelled' => 'Annulé',
            default => $status,
        };
    }
}
