<?php

namespace App\Livewire\Pages\Support;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.support.index')
            ->layout('layouts.app', ['header' => 'Support']);
    }
}
