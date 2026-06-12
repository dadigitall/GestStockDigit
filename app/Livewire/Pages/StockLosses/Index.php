<?php

namespace App\Livewire\Pages\StockLosses;

use App\Models\Product;
use App\Models\StockLoss;
use App\Models\StockMovement;
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

    public ?int $editId = null;

    // Form
    public ?string $productSearch = '';

    public ?int $productId = null;

    public string $productName = '';

    public string $lossType = 'unknown_loss';

    public ?string $quantity = null;

    public ?string $unitPrice = null;

    public string $reason = '';

    public string $justification = '';

    public string $notes = '';

    public array $products = [];

    // Detail
    public bool $showDetail = false;

    public ?int $detailId = null;

    public array $productResults = [];

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $losses = StockLoss::where('company_id', $companyId)
            ->with(['product', 'user', 'store', 'approvedBy'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('loss_type', $this->filterType))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $detail = null;
        if ($this->detailId) {
            $detail = StockLoss::with(['product', 'user', 'store', 'approvedBy'])->find($this->detailId);
        }

        // Product search
        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('reference', 'like', "%{$this->productSearch}%");
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->productResults = [];
        }

        return view('livewire.pages.stock-losses.index', compact('losses', 'detail'))
            ->layout('components.layouts.app', ['title' => 'Pertes et casses']);
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editId = null;
    }

    public function selectProduct(int $id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->productName = $product->name;
        $this->productSearch = $product->name;
        $this->unitPrice = (string) $product->purchase_price;
        $this->productResults = [];
    }

    public function removeProduct()
    {
        $this->productId = null;
        $this->productName = '';
        $this->productSearch = '';
        $this->unitPrice = null;
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    public function updatedUnitPrice()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        // computed in view
    }

    public function save()
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'lossType' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0.01',
            'unitPrice' => 'required|numeric|min:0',
        ]);

        $companyId = auth()->user()->company_id;
        $storeId = auth()->user()->store_id ?? auth()->user()->stores->first()?->id;
        $total = (float) $this->quantity * (float) $this->unitPrice;

        DB::transaction(function () use ($companyId, $storeId, $total) {
            $loss = StockLoss::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'product_id' => $this->productId,
                'reference' => StockLoss::generateReference(),
                'loss_type' => $this->lossType,
                'quantity' => $this->quantity,
                'unit_price' => $this->unitPrice,
                'total_value' => $total,
                'reason' => $this->reason,
                'justification' => $this->justification,
                'status' => 'pending',
                'notes' => $this->notes,
            ]);

            // Auto-approve if user has permission
            if (auth()->user()->can('approve stock losses')) {
                $loss->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                $this->applyStockLoss($loss);
            }
        });

        $this->showForm = false;
        $this->resetForm();
    }

    public function applyStockLoss(StockLoss $loss): void
    {
        $storeId = $loss->store_id;

        // Decrement stock
        $pivot = DB::table('product_store')
            ->where('product_id', $loss->product_id)
            ->where('store_id', $storeId)
            ->first();

        if ($pivot) {
            DB::table('product_store')
                ->where('id', $pivot->id)
                ->decrement('stock_quantity', $loss->quantity);
        }

        StockMovement::create([
            'company_id' => $loss->company_id,
            'store_id' => $storeId,
            'product_id' => $loss->product_id,
            'user_id' => auth()->id(),
            'type' => 'loss_out',
            'quantity' => $loss->quantity,
            'unit_price' => $loss->unit_price,
            'total' => $loss->total_value,
            'reference' => $loss->reference,
            'notes' => 'Pertes et casses: '.$loss->loss_type.' - '.($loss->reason ?: ''),
            'movement_date' => now(),
        ]);
    }

    public function approve(int $id)
    {
        $loss = StockLoss::findOrFail($id);
        $loss->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->applyStockLoss($loss);
    }

    public function reject(int $id)
    {
        StockLoss::findOrFail($id)->update(['status' => 'rejected']);
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

    public function resetForm()
    {
        $this->productSearch = '';
        $this->productId = null;
        $this->productName = '';
        $this->lossType = 'unknown_loss';
        $this->quantity = null;
        $this->unitPrice = null;
        $this->reason = '';
        $this->justification = '';
        $this->notes = '';
        $this->productResults = [];
    }
}
