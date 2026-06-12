<?php

namespace App\Livewire\Pages\Customers;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerPayment;
use App\Models\PaymentSchedule;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public $editingCustomer = null;

    public string $name = '';

    public string $type = 'particular';

    public $customer_category_id;

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $tax_number = '';

    public $credit_limit;

    public string $payment_terms = '';

    public string $notes = '';

    public array $categories = [];

    // --- Detail / Relevé ---
    public $detailCustomer = null;

    // --- Payment form ---
    public bool $showPaymentForm = false;

    public $paymentCustomerId;

    public $payment_sale_id;

    public $payment_amount;

    public $payment_date;

    public string $payment_method = 'cash';

    public string $payment_reference = '';

    public string $payment_notes = '';

    public $payment_schedule_id;

    public array $customerSales = [];

    // --- Schedule form ---
    public bool $showScheduleForm = false;

    public $scheduleCustomerId;

    public $schedule_sale_id;

    public $schedule_due_date;

    public $schedule_amount;

    public string $schedule_notes = '';

    // --- Reminders tab ---
    public string $overdueTab = 'all';

    public function mount()
    {
        $this->categories = CustomerCategory::where('company_id', auth()->user()->company_id)->get();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $customers = Customer::where('company_id', $companyId)
            ->with('category')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        $overdueCustomers = Customer::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->where(function ($q) {
                $q->whereHas('paymentSchedules', fn ($s) => $s->where('due_date', '<', now())->where('status', '!=', 'paid'));
            })
            ->with('category')
            ->orderBy('balance', 'desc')
            ->get();

        $pendingCreditCustomers = Customer::where('company_id', $companyId)
            ->where('balance', '>', 0)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('balance', 'desc')
            ->get();

        return view('livewire.pages.customers.index', compact(
            'customers', 'overdueCustomers', 'pendingCreditCustomers'
        ))->layout('layouts.app', ['header' => 'Clients']);
    }

    // --- CRUD ---
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Customer $customer)
    {
        $this->editingCustomer = $customer;
        $this->name = $customer->name;
        $this->type = $customer->type;
        $this->customer_category_id = $customer->customer_category_id;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->tax_number = $customer->tax_number;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->notes = $customer->notes;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:particular,professional,reseller,wholesaler',
            'customer_category_id' => 'nullable|exists:customer_categories,id',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'customer_category_id' => $this->customer_category_id ?: null,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'tax_number' => $this->tax_number,
            'credit_limit' => $this->credit_limit ?: null,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
        ];

        if ($this->editingCustomer) {
            $this->editingCustomer->update($data);
            session()->flash('message', 'Client mis à jour.');
        } else {
            Customer::create($data);
            session()->flash('message', 'Client créé.');
        }

        $this->resetForm();
    }

    public function toggleActive(Customer $customer)
    {
        $customer->update(['is_active' => ! $customer->is_active]);
    }

    // --- Detail view ---
    public function showDetail(Customer $customer)
    {
        $this->detailCustomer = $customer->load(['category', 'sales' => fn ($q) => $q->latest()->limit(20), 'payments' => fn ($q) => $q->latest()->limit(20), 'paymentSchedules' => fn ($q) => $q->latest()]);
    }

    public function closeDetail()
    {
        $this->detailCustomer = null;
    }

    // --- Payment (partial) ---
    public function openPaymentForm(Customer $customer, $saleId = null, $scheduleId = null)
    {
        $this->paymentCustomerId = $customer->id;
        $this->payment_sale_id = $saleId;
        $this->payment_schedule_id = $scheduleId;
        $this->payment_amount = $scheduleId
            ? PaymentSchedule::find($scheduleId)?->remaining()
            : $customer->balance;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = 'cash';
        $this->payment_reference = '';
        $this->payment_notes = '';
        $this->customerSales = Sale::where('customer_id', $customer->id)
            ->where('payment_method', 'credit')
            ->where('balance', '>', 0)
            ->latest()
            ->get()
            ->toArray();
        $this->showPaymentForm = true;
    }

    public function closePaymentForm()
    {
        $this->showPaymentForm = false;
        $this->paymentCustomerId = null;
        $this->payment_sale_id = null;
        $this->payment_schedule_id = null;
        $this->payment_amount = null;
    }

    public function savePayment()
    {
        $this->validate([
            'paymentCustomerId' => 'required|exists:customers,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($this->paymentCustomerId);

            $payment = CustomerPayment::create([
                'company_id' => auth()->user()->company_id,
                'customer_id' => $customer->id,
                'sale_id' => $this->payment_sale_id ?: null,
                'payment_schedule_id' => $this->payment_schedule_id ?: null,
                'amount' => $this->payment_amount,
                'payment_date' => $this->payment_date,
                'payment_method' => $this->payment_method,
                'reference' => $this->payment_reference ?: null,
                'notes' => $this->payment_notes ?: null,
            ]);

            $customer->decrement('balance', $this->payment_amount);

            if ($this->payment_schedule_id) {
                $schedule = PaymentSchedule::find($this->payment_schedule_id);
                $newPaid = $schedule->paid_amount + $this->payment_amount;
                $schedule->update([
                    'paid_amount' => $newPaid,
                    'status' => $newPaid >= $schedule->amount ? 'paid' : 'partial',
                ]);
            }

            if ($this->payment_sale_id) {
                $sale = Sale::find($this->payment_sale_id);
                if ($sale) {
                    $newPaid = ($sale->paid_amount ?? 0) + $this->payment_amount;
                    $sale->update([
                        'paid_amount' => $newPaid,
                    ]);
                }
            }

            DB::commit();

            // Enregistrer le mouvement de caisse
            $register = CashRegister::where('store_id', auth()->user()->store_id)
                ->where('status', 'open')
                ->first();

            if ($register) {
                $register->addMovement([
                    'user_id' => auth()->id(),
                    'type' => 'customer_payment',
                    'direction' => 'in',
                    'amount' => $this->payment_amount,
                    'payment_method' => $this->payment_method,
                    'description' => 'Paiement client '.$customer->name,
                    'reference' => $payment->reference ?? $payment->id,
                    'sourceable_type' => CustomerPayment::class,
                    'sourceable_id' => $payment->id,
                ]);
            }

            $this->closePaymentForm();
            if ($this->detailCustomer && $this->detailCustomer->id === $customer->id) {
                $this->detailCustomer = $customer->fresh()->load(['sales', 'payments', 'paymentSchedules']);
            }
            session()->flash('message', 'Paiement enregistré. Nouveau solde : '.number_format($customer->fresh()->balance, 0, ',', ' ').' F');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    // --- Schedule ---
    public function openScheduleForm(Customer $customer, $saleId = null)
    {
        $this->scheduleCustomerId = $customer->id;
        $this->schedule_sale_id = $saleId;
        $this->schedule_due_date = now()->addDays(30)->format('Y-m-d');
        $sale = $saleId ? Sale::find($saleId) : null;
        $this->schedule_amount = $sale ? max(0, $sale->total - $sale->paid_amount) : $customer->balance;
        $this->schedule_notes = '';
        $this->showScheduleForm = true;
    }

    public function closeScheduleForm()
    {
        $this->showScheduleForm = false;
        $this->scheduleCustomerId = null;
        $this->schedule_sale_id = null;
    }

    public function saveSchedule()
    {
        $this->validate([
            'scheduleCustomerId' => 'required|exists:customers,id',
            'schedule_sale_id' => 'required|exists:sales,id',
            'schedule_due_date' => 'required|date|after:today',
            'schedule_amount' => 'required|numeric|min:0.01',
        ]);

        $customer = Customer::findOrFail($this->scheduleCustomerId);

        PaymentSchedule::create([
            'company_id' => auth()->user()->company_id,
            'customer_id' => $customer->id,
            'sale_id' => $this->schedule_sale_id,
            'due_date' => $this->schedule_due_date,
            'amount' => $this->schedule_amount,
            'status' => 'pending',
            'notes' => $this->schedule_notes ?: null,
        ]);

        $this->closeScheduleForm();
        if ($this->detailCustomer && $this->detailCustomer->id === $customer->id) {
            $this->detailCustomer = $customer->fresh()->load(['sales', 'payments', 'paymentSchedules']);
        }
        session()->flash('message', 'Échéance planifiée au '.Carbon::parse($this->schedule_due_date)->format('d/m/Y'));
    }

    // --- Overdue customers (relances) ---
    public function sendReminder(Customer $customer)
    {
        session()->flash('message', 'Relance envoyée à '.$customer->name.' (simulation).');
    }

    // --- Reset ---
    public function resetForm()
    {
        $this->editingCustomer = null;
        $this->name = '';
        $this->type = 'particular';
        $this->customer_category_id = null;
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->tax_number = '';
        $this->credit_limit = null;
        $this->payment_terms = '';
        $this->notes = '';
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
