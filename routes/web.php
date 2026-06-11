<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Products\Form;
use App\Livewire\Pages\Products\Index;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('products', Index::class)->name('products.index');
    Route::get('products/create', Form::class)->name('products.create');
    Route::get('products/{product}/edit', Form::class)->name('products.edit');
    Route::get('categories', App\Livewire\Pages\Categories\Index::class)->name('categories.index');
    Route::get('suppliers', App\Livewire\Pages\Suppliers\Index::class)->name('suppliers.index');
    Route::get('customers', App\Livewire\Pages\Customers\Index::class)->name('customers.index');
    Route::get('stores', App\Livewire\Pages\Stores\Index::class)->name('stores.index');
    Route::get('purchases', App\Livewire\Pages\Purchases\Index::class)->name('purchases.index');
    Route::get('invoices', App\Livewire\Pages\Invoices\Index::class)->name('invoices.index');
    Route::get('pos', App\Livewire\Pages\Pos\Index::class)->name('pos.index');
    Route::get('stock', App\Livewire\Pages\Stock\Index::class)->name('stock.index');
    Route::get('reports', App\Livewire\Pages\Reports\Index::class)->name('reports.index');
    Route::get('settings', App\Livewire\Pages\Settings\Index::class)->name('settings.index');
    Route::get('support', App\Livewire\Pages\Support\Index::class)->name('support.index');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
