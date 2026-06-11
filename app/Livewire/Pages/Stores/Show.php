<?php

namespace App\Livewire\Pages\Stores;

use App\Models\Store;
use Livewire\Component;

class Show extends Component
{
    public Store $store;

    public $children = [];

    public $ancestors = [];

    public function mount(Store $store)
    {
        $this->store = $store->load('parent', 'manager', 'company');
        $this->children = Store::where('parent_id', $store->id)
            ->with('manager')
            ->orderBy('name')
            ->get();

        $this->ancestors = $this->getAncestors($store);
    }

    protected function getAncestors($store)
    {
        $ancestors = [];
        $current = $store->parent;
        while ($current) {
            $ancestors[] = $current;
            $current = $current->parent;
        }

        return array_reverse($ancestors);
    }

    public function render()
    {
        return view('livewire.pages.stores.show', [
            'fullTree' => Store::fullTree($this->store->company_id),
        ])
            ->layout('layouts.app', ['header' => $this->store->name]);
    }
}
