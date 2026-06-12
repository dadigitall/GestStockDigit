<?php

namespace App\Livewire\Pages\DeliveryNotes;

use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
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

    public ?string $sourceType = null;

    public ?string $sourceId = null;

    public ?string $deliveryDate = null;

    public string $notes = '';

    public array $cart = [];

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

        $this->deliveryDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $deliveryNotes = DeliveryNote::where('company_id', $companyId)
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
            $detail = DeliveryNote::where('company_id', $companyId)
                ->with(['items', 'customer', 'user'])
                ->find($this->detailId);
        }

        return view('livewire.pages.delivery-notes.index', compact('deliveryNotes', 'detail'))
            ->layout('layouts.app', ['header' => 'Bons de livraison']);
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
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('reference', 'like', "%{$this->productSearch}%")
                    ->orWhere('barcode', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'reference', 'stock_quantity', 'unit_sale', 'unit_purchase'])
            ->toArray();
    }

    public function addToCart($productId)
    {
        $product = Product::where('company_id', auth()->user()->company_id)
            ->findOrFail($productId);

        $existing = collect($this->cart)->firstWhere('id', $productId);
        if ($existing) {
            session()->flash('error', 'Ce produit est déjà dans le bon de livraison.');

            return;
        }

        $this->cart[] = [
            'id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit_sale ?? 'piece',
            'quantity_requested' => 1,
            'quantity_delivered' => 1,
            'notes' => '',
        ];

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function updateItem($index, $field, $value)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index][$field] = $value;

        if ($field === 'quantity_requested' || $field === 'quantity_delivered') {
            $val = (float) $value;
            if ($val < 0) {
                $this->cart[$index][$field] = 0;
            }
        }
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->customerId = null;
        $this->sourceType = null;
        $this->sourceId = null;
        $this->deliveryDate = now()->format('Y-m-d');
        $this->notes = '';
        $this->cart = [];
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
            'deliveryDate' => 'required|date',
            'cart' => 'required|array|min:1',
        ]);

        $companyId = auth()->user()->company_id;

        DB::beginTransaction();
        try {
            $deliveryNote = DeliveryNote::updateOrCreate(
                ['id' => $this->editId],
                [
                    'company_id' => $companyId,
                    'customer_id' => $this->customerId,
                    'user_id' => auth()->id(),
                    'store_id' => auth()->user()->store_id,
                    'source_type' => $this->sourceType,
                    'source_id' => $this->sourceId,
                    'delivery_date' => $this->deliveryDate,
                    'notes' => $this->notes,
                ]
            );

            if (! $this->editId) {
                $deliveryNote->reference = DeliveryNote::generateReference();
                $deliveryNote->status = 'draft';
                $deliveryNote->save();
            }

            if ($this->editId) {
                $deliveryNote->items()->delete();
            }

            $items = [];
            foreach ($this->cart as $data) {
                $items[] = [
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $data['id'],
                    'product_name' => $data['product_name'],
                    'unit' => $data['unit'] ?? 'piece',
                    'quantity_requested' => (float) ($data['quantity_requested'] ?? 0),
                    'quantity_delivered' => (float) ($data['quantity_delivered'] ?? 0),
                    'notes' => $data['notes'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DeliveryNoteItem::insert($items);

            DB::commit();
            $this->resetForm();
            session()->flash('message', $this->editId
                ? "Bon de livraison {$deliveryNote->reference} mis à jour."
                : "Bon de livraison {$deliveryNote->reference} créé.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $deliveryNote = DeliveryNote::with('items')->findOrFail($id);
        if ($deliveryNote->status !== 'draft') {
            session()->flash('error', 'Seul un brouillon peut être modifié.');

            return;
        }

        $this->resetForm();
        $this->editId = $deliveryNote->id;
        $this->customerId = (string) $deliveryNote->customer_id;
        $this->sourceType = $deliveryNote->source_type;
        $this->sourceId = $deliveryNote->source_id;
        $this->deliveryDate = $deliveryNote->delivery_date?->format('Y-m-d');
        $this->notes = $deliveryNote->notes ?? '';

        $this->cart = $deliveryNote->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'product_name' => $item->product_name ?? '#'.$item->product_id,
                'unit' => $item->unit ?? 'piece',
                'quantity_requested' => (float) ($item->quantity_requested ?? 0),
                'quantity_delivered' => (float) ($item->quantity_delivered ?? 0),
                'notes' => $item->notes ?? '',
            ];
        })->toArray();

        $this->showForm = true;
    }

    public function markDelivered($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        if (! in_array($deliveryNote->status, ['draft', 'partially_delivered'])) {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $deliveryNote->update([
            'status' => 'delivered',
            'received_date' => now(),
        ]);
        session()->flash('message', "Bon de livraison {$deliveryNote->reference} marqué comme livré.");
    }

    public function markPartial($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        if ($deliveryNote->status !== 'draft') {
            session()->flash('error', 'Action non autorisée.');

            return;
        }

        $deliveryNote->update(['status' => 'partially_delivered']);
        session()->flash('message', "Bon de livraison {$deliveryNote->reference} marqué comme partiellement livré.");
    }

    public function cancel($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        if (! in_array($deliveryNote->status, ['draft', 'partially_delivered'])) {
            session()->flash('error', 'Ce bon de livraison ne peut plus être annulé.');

            return;
        }

        $deliveryNote->update(['status' => 'cancelled']);
        session()->flash('message', "Bon de livraison {$deliveryNote->reference} annulé.");
    }

    public function delete($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        $ref = $deliveryNote->reference;
        $deliveryNote->delete();
        session()->flash('message', "Bon de livraison {$ref} supprimé.");
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
            'delivered' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'partially_delivered' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'cancelled' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        };
    }

    public function statusLabel($status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'delivered' => 'Livré',
            'partially_delivered' => 'Partiel',
            'cancelled' => 'Annulé',
            default => $status,
        };
    }
}
