<?php

namespace App\Livewire\Pages\Suppliers;

use App\Models\Supplier;
use App\Models\SupplierEvaluation;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $editingSupplier = null;

    public $name;

    public $type;

    public $phone;

    public $email;

    public $address;

    public $contact_name;

    public $payment_terms;

    public $delivery_delay_days;

    public $currency;

    public $notes;

    public $evaluation;

    public $showEvaluationForm = false;

    public $respectDelays = 3;

    public $productQuality = 3;

    public $returnRate = 3;

    public $averagePrice = 3;

    public $reliability = 3;

    public $purchaseVolume = 3;

    public $evaluationComment = '';

    public function render()
    {
        $suppliers = Supplier::where('company_id', auth()->user()->company_id)
            ->with('latestEvaluation')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.pages.suppliers.index', compact('suppliers'))
            ->layout('layouts.app', ['header' => 'Fournisseurs']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Supplier $supplier)
    {
        $this->editingSupplier = $supplier;
        $this->name = $supplier->name;
        $this->type = $supplier->type;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->contact_name = $supplier->contact_name;
        $this->payment_terms = $supplier->payment_terms;
        $this->delivery_delay_days = $supplier->delivery_delay_days;
        $this->currency = $supplier->currency;
        $this->notes = $supplier->notes;
        $this->showForm = true;
        $this->showEvaluationForm = false;
        $this->loadEvaluation();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:local,international,manufacturer,distributor',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_delay_days' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'contact_name' => $this->contact_name,
            'payment_terms' => $this->payment_terms,
            'delivery_delay_days' => $this->delivery_delay_days ?: null,
            'currency' => $this->currency ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingSupplier) {
            $this->editingSupplier->update($data);
        } else {
            $supplier = Supplier::create($data);
            $this->editingSupplier = $supplier;
        }

        session()->flash('message', 'Fournisseur '.($this->editingSupplier->wasRecentlyCreated ? 'créé' : 'mis à jour').'.');
        $this->resetForm();
    }

    public function loadEvaluation()
    {
        if (! $this->editingSupplier) {
            return;
        }

        $this->evaluation = SupplierEvaluation::where('supplier_id', $this->editingSupplier->id)
            ->latest()
            ->first();

        if ($this->evaluation) {
            $this->respectDelays = $this->evaluation->respect_delays ?? 3;
            $this->productQuality = $this->evaluation->product_quality ?? 3;
            $this->returnRate = $this->evaluation->return_rate ?? 3;
            $this->averagePrice = $this->evaluation->average_price ?? 3;
            $this->reliability = $this->evaluation->reliability ?? 3;
            $this->purchaseVolume = $this->evaluation->purchase_volume ?? 3;
            $this->evaluationComment = $this->evaluation->comment ?? '';
        }
    }

    public function saveEvaluation()
    {
        if (! $this->editingSupplier) {
            return;
        }

        $this->validate([
            'respectDelays' => 'required|integer|min:1|max:5',
            'productQuality' => 'required|integer|min:1|max:5',
            'returnRate' => 'required|integer|min:1|max:5',
            'averagePrice' => 'required|integer|min:1|max:5',
            'reliability' => 'required|integer|min:1|max:5',
            'purchaseVolume' => 'required|integer|min:1|max:5',
            'evaluationComment' => 'nullable|string|max:1000',
        ]);

        $overall = round(
            ($this->respectDelays + $this->productQuality + $this->returnRate
                + $this->averagePrice + $this->reliability + $this->purchaseVolume) / 6,
            1
        );

        SupplierEvaluation::updateOrCreate(
            [
                'supplier_id' => $this->editingSupplier->id,
                'evaluated_by' => auth()->id(),
                'evaluated_at' => now()->format('Y-m-d'),
            ],
            [
                'respect_delays' => $this->respectDelays,
                'product_quality' => $this->productQuality,
                'return_rate' => $this->returnRate,
                'average_price' => $this->averagePrice,
                'reliability' => $this->reliability,
                'purchase_volume' => $this->purchaseVolume,
                'overall_rating' => $overall,
                'comment' => $this->evaluationComment,
            ]
        );

        $this->loadEvaluation();
        $this->showEvaluationForm = true;
        session()->flash('message', 'Évaluation enregistrée.');
    }

    public function setScore($field, $value)
    {
        $this->$field = max(1, min(5, (int) $value));
    }

    public function toggleActive(Supplier $supplier)
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);
    }

    public function resetForm()
    {
        $this->name = '';
        $this->type = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->contact_name = '';
        $this->payment_terms = '';
        $this->delivery_delay_days = null;
        $this->currency = '';
        $this->notes = '';
        $this->evaluation = null;
        $this->respectDelays = 3;
        $this->productQuality = 3;
        $this->returnRate = 3;
        $this->averagePrice = 3;
        $this->reliability = 3;
        $this->purchaseVolume = 3;
        $this->evaluationComment = '';
        $this->editingSupplier = null;
        $this->showForm = false;
        $this->showEvaluationForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
