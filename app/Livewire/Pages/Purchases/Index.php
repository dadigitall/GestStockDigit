<?php

namespace App\Livewire\Pages\Purchases;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.purchases.index')
            ->layout('layouts.app', ['header' => 'Achats']);
    }
}
