<?php

namespace App\Livewire\Pages\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Jobs\SyncInvoiceToEmecf;
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

    public bool $showForm = false;

    public bool $syncingToEmecf = false;

    public int $emecfPollAttempts = 0;

    public const MAX_EMECF_POLLS = 10;

    public $editId = null;

    public $customerId;

    public $type = 'sale';

    public $issueDate;

    public $dueDate;

    public $paymentTerms = '';

    public $notes = '';

    public array $cart = [];

    public $subtotal = 0;

    public $taxAmount = 0;

    public $discount = 0;

    public $total = 0;

    public $paidAmount = 0;

    public $amountDue = 0;

    public bool $showDetail = false;

    public $detailId = null;

    public string $productSearch = '';

    public array $productResults = [];

    public $customers = [];

    // Payment modal
    public bool $showPaymentModal = false;

    public $paymentInvoiceId = null;

    public $paymentAmount = 0;

    protected function rules()
    {
        return [
            'customerId' => 'required|exists:customers,id',
            'type' => 'required|in:sale,proforma,deposit,balance,credit_note',
            'issueDate' => 'required|date',
            'dueDate' => 'nullable|date|after_or_equal:issueDate',
            'cart' => 'required|array|min:1',
        ];
    }

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $this->issueDate = now()->format('Y-m-d');
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
                    ->orWhere('reference', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'reference', 'unit_sale', 'sale_price', 'tax_rate'])
            ->toArray();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $exists = collect($this->cart)->firstWhere('product_id', $product->id);
        if ($exists) {
            foreach ($this->cart as $i => $item) {
                if ($item['product_id'] == $product->id) {
                    $this->cart[$i]['qty'] = ($item['qty'] ?? 1) + 1;
                    $this->updateItem($i, 'qty', $this->cart[$i]['qty']);
                    break;
                }
            }
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'reference' => $product->reference ?? '',
                'unit' => $product->unit_sale ?? 'pc',
                'qty' => 1,
                'price' => (float) ($product->sale_price ?? 0),
                'discount' => 0,
                'tax_rate' => (float) ($product->tax_rate ?? 0),
                'subtotal' => (float) ($product->sale_price ?? 0),
            ];
        }

        $this->productResults = [];
        $this->productSearch = '';
        $this->calculateTotals();
    }

    public function updateItem($index, $field, $value)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index][$field] = $value;

        $item = $this->cart[$index];
        $lineTotal = ($item['qty'] ?? 0) * ($item['price'] ?? 0);
        $lineDiscount = $lineTotal * (($item['discount'] ?? 0) / 100);
        $lineTaxable = $lineTotal - $lineDiscount;
        $lineTax = $lineTaxable * (($item['tax_rate'] ?? 0) / 100);
        $this->cart[$index]['subtotal'] = $lineTaxable + $lineTax;

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
        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($this->cart as $item) {
            $lineTotal = ($item['qty'] ?? 0) * ($item['price'] ?? 0);
            $lineDiscount = $lineTotal * (($item['discount'] ?? 0) / 100);
            $lineTaxable = $lineTotal - $lineDiscount;
            $lineTax = $lineTaxable * (($item['tax_rate'] ?? 0) / 100);

            $subtotal += $lineTotal;
            $discount += $lineDiscount;
            $tax += $lineTax;
        }

        $this->subtotal = $subtotal;
        $this->discount = $discount;
        $this->taxAmount = $tax;
        $this->total = $subtotal - $discount + $tax;
        $this->amountDue = $this->total - $this->paidAmount;
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->customerId = null;
        $this->type = 'sale';
        $this->issueDate = now()->format('Y-m-d');
        $this->dueDate = '';
        $this->paymentTerms = '';
        $this->notes = '';
        $this->cart = [];
        $this->subtotal = 0;
        $this->taxAmount = 0;
        $this->discount = 0;
        $this->total = 0;
        $this->paidAmount = 0;
        $this->amountDue = 0;
        $this->showForm = false;
        $this->showDetail = false;
        $this->detailId = null;
    }

    public function create()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->company_id;

            $data = [
                'company_id' => $companyId,
                'customer_id' => $this->customerId,
                'user_id' => auth()->id(),
                'reference' => Invoice::generateReference(),
                'type' => $this->type,
                'status' => 'draft',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount' => $this->discount,
                'total' => $this->total,
                'paid_amount' => 0,
                'amount_due' => $this->total,
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate ?: null,
                'payment_terms' => $this->paymentTerms,
                'notes' => $this->notes,
            ];

            $invoice = Invoice::create($data);

            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_reference' => $item['reference'],
                    'unit' => $item['unit'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            $this->resetForm();
            session()->flash('message', 'Facture créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $this->editId = $id;
        $this->customerId = $invoice->customer_id;
        $this->type = $invoice->type;
        $this->issueDate = $invoice->issue_date->format('Y-m-d');
        $this->dueDate = $invoice->due_date?->format('Y-m-d');
        $this->paymentTerms = $invoice->payment_terms;
        $this->notes = $invoice->notes;
        $this->paidAmount = $invoice->paid_amount;

        $this->cart = $invoice->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'name' => $i->product_name,
            'reference' => $i->product_reference ?? '',
            'unit' => $i->unit ?? 'pc',
            'qty' => (float) $i->quantity,
            'price' => (float) $i->unit_price,
            'discount' => (float) $i->discount,
            'tax_rate' => (float) $i->tax_rate,
            'subtotal' => (float) $i->subtotal,
        ])->toArray();

        $this->calculateTotals();
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($this->editId);

            $invoice->update([
                'customer_id' => $this->customerId,
                'type' => $this->type,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'discount' => $this->discount,
                'total' => $this->total,
                'amount_due' => $this->total - $this->paidAmount,
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate ?: null,
                'payment_terms' => $this->paymentTerms,
                'notes' => $this->notes,
            ]);

            $invoice->items()->delete();

            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_reference' => $item['reference'],
                    'unit' => $item['unit'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            $this->resetForm();
            session()->flash('message', 'Facture mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function markSent($id)
    {
        Invoice::where('id', $id)->where('company_id', auth()->user()->company_id)
            ->update(['status' => 'sent']);
        session()->flash('message', 'Facture marquée comme envoyée.');
    }

    public function markPaid($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $invoice->total,
            'amount_due' => 0,
            'paid_at' => now(),
        ]);
        session()->flash('message', 'Facture marquée comme payée.');
    }

    public function recordPayment($id)
    {
        $this->paymentInvoiceId = $id;
        $invoice = Invoice::findOrFail($id);
        $this->paymentAmount = $invoice->amount_due;
        $this->showPaymentModal = true;
    }

    public function savePayment()
    {
        $this->validate(['paymentAmount' => 'required|numeric|min:0.01']);

        $invoice = Invoice::findOrFail($this->paymentInvoiceId);
        $newPaid = $invoice->paid_amount + $this->paymentAmount;
        $amountDue = max(0, $invoice->total - $newPaid);

        $updateData = [
            'paid_amount' => $newPaid,
            'amount_due' => $amountDue,
        ];

        if ($amountDue <= 0) {
            $updateData['status'] = 'paid';
            $updateData['paid_at'] = now();
        } elseif ($newPaid > 0) {
            $updateData['status'] = 'partially_paid';
        }

        $invoice->update($updateData);

        $this->showPaymentModal = false;
        $this->paymentInvoiceId = null;
        $this->paymentAmount = 0;
        session()->flash('message', 'Paiement enregistré.');
    }

    public function cancel($id)
    {
        Invoice::where('id', $id)->where('company_id', auth()->user()->company_id)
            ->update(['status' => 'cancelled']);
        session()->flash('message', 'Facture annulée.');
    }

    public function delete($id)
    {
        Invoice::where('id', $id)->where('company_id', auth()->user()->company_id)
            ->delete();
        session()->flash('message', 'Facture supprimée.');
    }

    public function view($id)
    {
        $this->detailId = $id;
        $this->showDetail = true;
        $this->showForm = false;
    }

    public function syncToEmecf()
    {
        if (!$this->detailId) {
            session()->flash('error', 'Aucune facture sélectionnée.');
            return;
        }

        $invoice = Invoice::where('company_id', auth()->user()->company_id)
            ->find($this->detailId);

        if (!$invoice) {
            session()->flash('error', 'Facture introuvable.');
            return;
        }

        if ($invoice->isEmecfSynced()) {
            session()->flash('message', 'Facture déjà synchronisée avec e-MECeF.');
            return;
        }

        SyncInvoiceToEmecf::dispatch($invoice->id);

        $this->syncingToEmecf = true;
        $this->emecfPollAttempts = 0;

        session()->flash('message', '⏳ Synchronisation e-MECeF lancée en arrière-plan...');
    }

    /**
     * Polling method: refresh detail data while waiting for e-MECeF sync to complete.
     * Polls every 3 seconds, stops after ~30s timeout.
     */
    public function checkEmecfSyncStatus(): void
    {
        if (!$this->syncingToEmecf || !$this->detailId) {
            return;
        }

        $this->emecfPollAttempts++;

        // Timeout after MAX_EMECF_POLLS attempts (~30s)
        if ($this->emecfPollAttempts >= self::MAX_EMECF_POLLS) {
            $this->syncingToEmecf = false;
            session()->flash('error', '⚠️ La synchronisation e-MECeF a pris plus de temps que prévu. Vérifiez les logs.');
            return;
        }

        // Re-fetch the invoice to check if emecf_status changed
        $invoice = Invoice::where('company_id', auth()->user()->company_id)
            ->find($this->detailId);

        if (!$invoice) {
            return;
        }

        // If the sync completed (or got a status), stop polling and show success
        if ($invoice->emecf_status !== null) {
            $this->syncingToEmecf = false;
            $status = $invoice->emecf_status === 'confirmed' ? '✅' : '⚠️';
            session()->flash('message', "{$status} Synchronisation e-MECeF terminée (statut : {$invoice->emecf_status}).");
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $invoices = Invoice::where('company_id', $companyId)
            ->with(['customer', 'items'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('reference', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->showDetail && $this->detailId) {
            $detail = Invoice::with(['customer', 'items', 'user'])
                ->where('company_id', $companyId)
                ->find($this->detailId);
        }

        return view('livewire.pages.invoices.index', compact('invoices', 'detail'))
            ->layout('layouts.app', ['header' => 'Factures']);
    }
}
