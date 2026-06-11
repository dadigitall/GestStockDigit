<?php

namespace App\Livewire\Pages\Pos;

use Livewire\Component;
use App\Models\Product;

class Index extends Component
{
    public $search = '';
    public $cart = [];
    public $total = 0;

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);
        $existing = collect($this->cart)->firstWhere('id', $productId);

        if ($existing) {
            $this->cart = collect($this->cart)->map(function ($item) use ($productId) {
                if ($item['id'] == $productId) {
                    $item['qty']++;
                    $item['subtotal'] = $item['qty'] * $item['price'];
                }
                return $item;
            })->toArray();
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price,
                'qty' => 1,
                'subtotal' => $product->sale_price,
            ];
        }

        $this->calculateTotal();
    }

    public function updateQty($index, $qty)
    {
        if ($qty <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        } else {
            $this->cart[$index]['qty'] = $qty;
            $this->cart[$index]['subtotal'] = $qty * $this->cart[$index]['price'];
        }
        $this->calculateTotal();
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum('subtotal');
    }

    public function render()
    {
        $products = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->take(20)
            ->get();

        return view('livewire.pages.pos.index', compact('products'))
            ->layout('layouts.app', ['header' => 'Point de vente (POS)']);
    }
}
