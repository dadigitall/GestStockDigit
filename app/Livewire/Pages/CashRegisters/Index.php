<?php

namespace App\Livewire\Pages\CashRegisters;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Store;
use App\Models\User;
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

    public string $name = '';

    public string $code = '';

    public ?string $storeId = null;

    public array $stores = [];

    public array $users = [];

    // Cashiers
    public array $selectedCashiers = [];

    // Opening
    public bool $showOpenModal = false;

    public ?int $openRegisterId = null;

    public float $initialBalance = 0;

    // Movement
    public bool $showMovementModal = false;

    public ?int $movementRegisterId = null;

    public string $movementType = 'cash_sale';

    public string $movementDirection = 'in';

    public float $movementAmount = 0;

    public string $movementDescription = '';

    public string $movementPaymentMethod = 'cash';

    // Closing
    public bool $showCloseModal = false;

    public ?int $closeRegisterId = null;

    public float $countedAmount = 0;

    public string $closingNote = '';

    public string $cashierSignature = '';

    // Validation
    public bool $showValidateModal = false;

    public ?int $validateRegisterId = null;

    public string $validatorSignature = '';

    // Detail
    public bool $showDetail = false;

    public ?int $detailId = null;

    // Rapport
    public bool $showRapport = false;

    public ?int $rapportId = null;

    public string $rapportPeriod = 'shift'; // shift, today, week, month, custom

    public ?string $rapportDateFrom = null;

    public ?string $rapportDateTo = null;

    public function mount()
    {
        $companyId = auth()->user()->company_id;
        $this->stores = Store::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->users = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $registers = CashRegister::where('company_id', $companyId)
            ->with(['store', 'user', 'openedBy', 'closedBy', 'cashiers'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        $movements = null;
        if ($this->detailId) {
            $detail = CashRegister::with(['store', 'user', 'openedBy', 'closedBy', 'validatedBy', 'cashiers'])->find($this->detailId);
            $movements = CashMovement::where('cash_register_id', $this->detailId)
                ->with('user')
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20, pageName: 'movements-page');
        }

        return view('livewire.pages.cash-registers.index', compact('registers', 'detail', 'movements'))
            ->layout('components.layouts.app', ['title' => 'Caisses']);
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function edit(int $id)
    {
        $register = CashRegister::with('cashiers')->findOrFail($id);
        $this->editId = $id;
        $this->name = $register->name;
        $this->code = $register->code;
        $this->storeId = (string) $register->store_id;
        $this->selectedCashiers = $register->cashiers->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'storeId' => 'required|exists:stores,id',
        ]);

        $companyId = auth()->user()->company_id;

        DB::transaction(function () use ($companyId) {
            if ($this->editId) {
                $register = CashRegister::where('company_id', $companyId)->findOrFail($this->editId);
                $register->update([
                    'name' => $this->name,
                    'code' => $this->code,
                    'store_id' => $this->storeId,
                ]);
                $register->cashiers()->sync($this->selectedCashiers);
            } else {
                $register = CashRegister::create([
                    'company_id' => $companyId,
                    'store_id' => $this->storeId,
                    'name' => $this->name,
                    'code' => $this->code,
                ]);
                $register->cashiers()->attach($this->selectedCashiers);
            }
        });

        $this->showForm = false;
        $this->resetForm();
    }

    public function confirmOpen(int $id)
    {
        $this->openRegisterId = $id;
        $this->initialBalance = 0;
        $this->showOpenModal = true;
    }

    public function openRegister()
    {
        $this->validate([
            'initialBalance' => 'required|numeric|min:0',
        ]);

        $register = CashRegister::findOrFail($this->openRegisterId);
        $register->open($this->initialBalance, auth()->id());

        $this->showOpenModal = false;
        $this->openRegisterId = null;
        $this->initialBalance = 0;
    }

    public function confirmMovement(int $id, string $direction)
    {
        $this->movementRegisterId = $id;
        $this->movementDirection = $direction;
        $this->movementType = $direction === 'in' ? 'cash_sale' : 'internal_expense';
        $this->movementAmount = 0;
        $this->movementDescription = '';
        $this->movementPaymentMethod = 'cash';
        $this->showMovementModal = true;
    }

    public function updatedMovementType()
    {
        $directions = [
            'cash_sale' => 'in',
            'customer_payment' => 'in',
            'customer_refund' => 'out',
            'supplier_payment' => 'out',
            'internal_expense' => 'out',
            'owner_withdrawal' => 'out',
            'bank_deposit' => 'out',
            'correction' => 'in',
        ];

        $this->movementDirection = $directions[$this->movementType] ?? 'in';
    }

    public function addMovement()
    {
        $this->validate([
            'movementType' => 'required|string',
            'movementAmount' => 'required|numeric|min:1',
            'movementDescription' => 'nullable|string|max:500',
            'movementPaymentMethod' => 'required|string',
        ]);

        $register = CashRegister::findOrFail($this->movementRegisterId);

        $register->addMovement([
            'user_id' => auth()->id(),
            'type' => $this->movementType,
            'direction' => $this->movementDirection,
            'amount' => $this->movementAmount,
            'payment_method' => $this->movementPaymentMethod,
            'description' => $this->movementDescription,
        ]);

        $this->showMovementModal = false;
        $this->movementRegisterId = null;
        $this->movementAmount = 0;
        $this->movementDescription = '';
    }

    public function confirmClose(int $id)
    {
        $register = CashRegister::findOrFail($id);
        $this->closeRegisterId = $id;
        $this->countedAmount = (float) $register->expected_balance;
        $this->closingNote = '';
        $this->cashierSignature = '';
        $this->showCloseModal = true;
    }

    public function closeRegister()
    {
        $this->validate([
            'countedAmount' => 'required|numeric|min:0',
            'closingNote' => 'nullable|string|max:1000',
            'cashierSignature' => 'required|string|min:2',
        ], [
            'cashierSignature.required' => 'La signature du caissier est requise.',
        ]);

        $register = CashRegister::findOrFail($this->closeRegisterId);
        $register->close($this->countedAmount, $this->closingNote, $this->cashierSignature, auth()->id());

        $this->showCloseModal = false;
        $this->closeRegisterId = null;
    }

    // Validation responsable (8.50)
    public function confirmValidate(int $id)
    {
        $register = CashRegister::findOrFail($id);
        $this->validateRegisterId = $id;
        $this->validatorSignature = '';
        $this->showValidateModal = true;
    }

    public function validateClosing()
    {
        $this->validate([
            'validatorSignature' => 'required|string|min:2',
        ], [
            'validatorSignature.required' => 'La signature du validateur est requise.',
        ]);

        $register = CashRegister::findOrFail($this->validateRegisterId);
        $register->validateClosing($this->validatorSignature, auth()->id());

        $this->showValidateModal = false;
        $this->validateRegisterId = null;
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

    // Rapport de caisse
    public function showRapport(int $id)
    {
        $this->rapportId = $id;
        $this->rapportPeriod = 'shift';
        $this->rapportDateFrom = null;
        $this->rapportDateTo = null;
        $this->showRapport = true;
    }

    public function closeRapport()
    {
        $this->showRapport = false;
        $this->rapportId = null;
    }

    public function getRapportDataProperty()
    {
        if (! $this->rapportId) {
            return null;
        }

        $register = CashRegister::with('movements')->find($this->rapportId);
        if (! $register) {
            return null;
        }

        $query = $register->movements();

        if ($this->rapportPeriod === 'shift' && $register->opened_at) {
            $query->where('movement_date', '>=', $register->opened_at);
            if ($register->closed_at) {
                $query->where('movement_date', '<=', $register->closed_at);
            }
        } elseif ($this->rapportPeriod === 'today') {
            $query->whereDate('movement_date', today());
        } elseif ($this->rapportPeriod === 'week') {
            $query->whereBetween('movement_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->rapportPeriod === 'month') {
            $query->whereMonth('movement_date', now()->month)->whereYear('movement_date', now()->year);
        } elseif ($this->rapportPeriod === 'custom' && $this->rapportDateFrom) {
            $query->whereDate('movement_date', '>=', $this->rapportDateFrom);
            if ($this->rapportDateTo) {
                $query->whereDate('movement_date', '<=', $this->rapportDateTo);
            }
        }

        $movements = $query->with('user')->orderBy('movement_date', 'desc')->get();

        $summary = [
            'opening_balance' => $register->initial_balance,
            'cash_sales' => $movements->where('type', 'cash_sale')->sum('amount'),
            'customer_payments' => $movements->where('type', 'customer_payment')->sum('amount'),
            'corrections_in' => $movements->where('type', 'correction')->where('direction', 'in')->sum('amount'),
            'customer_refunds' => $movements->where('type', 'customer_refund')->sum('amount'),
            'supplier_payments' => $movements->where('type', 'supplier_payment')->sum('amount'),
            'internal_expenses' => $movements->where('type', 'internal_expense')->sum('amount'),
            'owner_withdrawals' => $movements->where('type', 'owner_withdrawal')->sum('amount'),
            'bank_deposits' => $movements->where('type', 'bank_deposit')->sum('amount'),
            'corrections_out' => $movements->where('type', 'correction')->where('direction', 'out')->sum('amount'),
            'total_in' => $movements->where('direction', 'in')->sum('amount'),
            'total_out' => $movements->where('direction', 'out')->sum('amount'),
            'by_cash' => $movements->where('payment_method', 'cash')->sum('amount'),
            'by_mobile_money' => $movements->where('payment_method', 'mobile_money')->sum('amount'),
            'by_card' => $movements->where('payment_method', 'card')->sum('amount'),
            'by_check' => $movements->where('payment_method', 'check')->sum('amount'),
            'by_bank_transfer' => $movements->where('payment_method', 'bank_transfer')->sum('amount'),
            'by_credit' => $movements->where('payment_method', 'credit')->sum('amount'),
            'expected_balance' => $register->expected_balance,
        ];

        return [
            'register' => $register,
            'movements' => $movements,
            'summary' => $summary,
        ];
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->storeId = null;
        $this->selectedCashiers = [];
    }

    public function getOpenRegistersCountProperty()
    {
        return CashRegister::where('company_id', auth()->user()->company_id)
            ->where('status', 'open')
            ->count();
    }
}
