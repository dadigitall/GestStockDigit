<?php

namespace App\Livewire\Pages\Units;

use App\Models\Unit;
use App\Models\UnitConversion;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $name = '';

    public $slug = '';

    public $type = 'custom';

    public $base_unit = false;

    public $editingUnit = null;

    public $showForm = false;

    public $tab = 'units';

    public $conversions = [];

    public function mount()
    {
        $this->loadConversions();
    }

    public function loadConversions()
    {
        $companyId = auth()->user()->company_id;
        $this->conversions = UnitConversion::where('company_id', $companyId)
            ->with('fromUnit', 'toUnit')
            ->get()
            ->keyBy(fn ($c) => $c->from_unit_id.'-'.$c->to_unit_id)
            ->toArray();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $units = Unit::where('company_id', $companyId)->orderBy('name')->paginate(20);
        $allUnits = Unit::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.pages.units.index', compact('units', 'allUnits'))
            ->layout('layouts.app', ['header' => 'Unités & Conversions']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Unit $unit)
    {
        $this->editingUnit = $unit;
        $this->name = $unit->name;
        $this->slug = $unit->slug;
        $this->type = $unit->type;
        $this->base_unit = $unit->base_unit;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|alpha_dash',
            'base_unit' => 'boolean',
        ]);

        $companyId = auth()->user()->company_id;

        $existing = Unit::where('company_id', $companyId)
            ->where('slug', $this->slug)
            ->when($this->editingUnit, fn ($q) => $q->where('id', '!=', $this->editingUnit->id))
            ->exists();

        if ($existing) {
            $this->addError('slug', 'Ce slug est déjà utilisé.');

            return;
        }

        $data = [
            'company_id' => $companyId,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'base_unit' => $this->base_unit,
        ];

        if ($this->editingUnit) {
            $this->editingUnit->update($data);
        } else {
            Unit::create($data);
        }

        $this->resetForm();
    }

    public function delete(Unit $unit)
    {
        $unit->delete();
    }

    public function setConversion($fromId, $toId, $factor)
    {
        $companyId = auth()->user()->company_id;

        if ($factor <= 0) {
            UnitConversion::where('company_id', $companyId)
                ->where('from_unit_id', $fromId)
                ->where('to_unit_id', $toId)
                ->delete();

            $this->loadConversions();

            return;
        }

        UnitConversion::updateOrCreate(
            [
                'company_id' => $companyId,
                'from_unit_id' => $fromId,
                'to_unit_id' => $toId,
            ],
            ['factor' => $factor]
        );

        $this->loadConversions();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->type = 'custom';
        $this->base_unit = false;
        $this->editingUnit = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
