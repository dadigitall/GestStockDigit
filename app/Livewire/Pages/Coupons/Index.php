<?php

namespace App\Livewire\Pages\Coupons;

use App\Models\Coupon;
use App\Models\Promotion;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editId = null;

    public string $code = '';

    public string $couponType = 'fixed';

    public ?string $value = null;

    public ?string $minOrderAmount = null;

    public ?string $maxDiscount = null;

    public int $usageLimit = 0;

    public int $usagePerCustomer = 0;

    public bool $isActive = true;

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    public ?int $promotionId = null;

    public string $search = '';

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $coupons = Coupon::where('company_id', $companyId)
            ->with('promotion')
            ->when($this->search, fn ($q) => $q->where('code', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $promotions = Promotion::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('livewire.pages.coupons.index', compact('coupons', 'promotions'))
            ->layout('components.layouts.app', ['title' => 'Coupons']);
    }

    public function generateCode()
    {
        $this->code = Coupon::generateCode();
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|string|max:50|unique:coupons,code,NULL,id,company_id,'.auth()->user()->company_id,
            'value' => 'required|numeric|min:0',
        ]);

        Coupon::create([
            'company_id' => auth()->user()->company_id,
            'promotion_id' => $this->promotionId,
            'code' => strtoupper($this->code),
            'type' => $this->couponType,
            'value' => $this->value,
            'min_order_amount' => $this->minOrderAmount ?: 0,
            'max_discount' => $this->maxDiscount,
            'usage_limit' => $this->usageLimit,
            'usage_per_customer' => $this->usagePerCustomer,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ]);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Coupon créé avec succès.');
    }

    public function edit(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->editId = $coupon->id;
        $this->code = $coupon->code;
        $this->couponType = $coupon->type;
        $this->value = (string) $coupon->value;
        $this->minOrderAmount = (string) $coupon->min_order_amount;
        $this->maxDiscount = $coupon->max_discount ? (string) $coupon->max_discount : null;
        $this->usageLimit = $coupon->usage_limit;
        $this->usagePerCustomer = $coupon->usage_per_customer;
        $this->isActive = $coupon->is_active;
        $this->startsAt = $coupon->starts_at?->format('Y-m-d\TH:i');
        $this->endsAt = $coupon->ends_at?->format('Y-m-d\TH:i');
        $this->promotionId = $coupon->promotion_id;
        $this->showForm = true;
    }

    public function update()
    {
        $this->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$this->editId.',id,company_id,'.auth()->user()->company_id,
            'value' => 'required|numeric|min:0',
        ]);

        Coupon::findOrFail($this->editId)->update([
            'promotion_id' => $this->promotionId,
            'code' => strtoupper($this->code),
            'type' => $this->couponType,
            'value' => $this->value,
            'min_order_amount' => $this->minOrderAmount ?: 0,
            'max_discount' => $this->maxDiscount,
            'usage_limit' => $this->usageLimit,
            'usage_per_customer' => $this->usagePerCustomer,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ]);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Coupon mis à jour avec succès.');
    }

    public function toggleActive(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        session()->flash('success', 'Coupon '.($coupon->is_active ? 'activé' : 'désactivé').'.');
    }

    public function delete(int $id)
    {
        Coupon::findOrFail($id)->delete();
        session()->flash('success', 'Coupon supprimé.');
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->code = '';
        $this->couponType = 'fixed';
        $this->value = null;
        $this->minOrderAmount = null;
        $this->maxDiscount = null;
        $this->usageLimit = 0;
        $this->usagePerCustomer = 0;
        $this->isActive = true;
        $this->startsAt = null;
        $this->endsAt = null;
        $this->promotionId = null;
    }
}
