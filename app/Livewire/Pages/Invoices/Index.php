<?php

namespace App\Livewire\Pages\Invoices;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.invoices.index')
            ->layout('layouts.app', ['header' => 'Factures']);
    }
}
