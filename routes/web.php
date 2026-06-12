<?php

use App\Http\Controllers\CancellationPrintController;
use App\Http\Controllers\CashRegisterPrintController;
use App\Http\Controllers\DeliveryNotePrintController;
use App\Http\Controllers\InventoryPrintController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\PurchaseReceiptController;
use App\Http\Controllers\QuotationPrintController;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Entrepots\Index as EntrepotsIndex;
use App\Livewire\Pages\Magasins\Index as MagasinsIndex;
use App\Livewire\Pages\Products\Form;
use App\Livewire\Pages\Products\Index;
use App\Livewire\Pages\Roles\Index as RolesIndex;
use App\Livewire\Pages\Stores\Show;
use App\Livewire\Pages\Users\Index as UsersIndex;
use App\Models\Sale;
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
    Route::get('stores/{store}', Show::class)->name('stores.show');
    Route::get('magasins', MagasinsIndex::class)->name('magasins.index');
    Route::get('entrepots', EntrepotsIndex::class)->name('entrepots.index');
    Route::get('purchases', App\Livewire\Pages\Purchases\Index::class)->name('purchases.index');
    Route::get('purchases/receipt/{goodsReceipt}/print', [PurchaseReceiptController::class, 'print'])->name('purchases.receipt.print');
    Route::get('invoices', App\Livewire\Pages\Invoices\Index::class)->name('invoices.index');
    Route::get('invoices/{invoice}/print', [InvoicePrintController::class, 'print'])->name('invoices.print');
    Route::get('pos', App\Livewire\Pages\Pos\Index::class)->name('pos.index');
    Route::get('wholesale', App\Livewire\Pages\Wholesale\Index::class)->name('wholesale.index');
    Route::get('pos/receipt/{sale}/print', function (Sale $sale) {
        $sale->load(['items', 'customer', 'store', 'user']);

        return view('pos.receipt-print', compact('sale'));
    })->name('pos.receipt.print');
    Route::get('stock', App\Livewire\Pages\Stock\Index::class)->name('stock.index');
    Route::get('stock-losses', App\Livewire\Pages\StockLosses\Index::class)->name('stock-losses.index');
    Route::get('price-tiers', App\Livewire\Pages\PriceTiers\Index::class)->name('price-tiers.index');
    Route::get('promotions', App\Livewire\Pages\Promotions\Index::class)->name('promotions.index');
    Route::get('coupons', App\Livewire\Pages\Coupons\Index::class)->name('coupons.index');
    Route::get('quotations', App\Livewire\Pages\Quotations\Index::class)->name('quotations.index');
    Route::get('customer-orders', App\Livewire\Pages\CustomerOrders\Index::class)->name('customer-orders.index');
    Route::get('quotations/{quotation}/print', [QuotationPrintController::class, 'print'])->name('quotations.print');
    Route::get('delivery-notes', App\Livewire\Pages\DeliveryNotes\Index::class)->name('delivery-notes.index');
    Route::get('delivery-notes/{deliveryNote}/print', [DeliveryNotePrintController::class, 'print'])->name('delivery-notes.print');
    Route::get('transfers', App\Livewire\Pages\Transfers\Index::class)->name('transfers.index');
    Route::get('inventories', App\Livewire\Pages\Inventories\Index::class)->name('inventories.index');
    Route::get('inventories/{inventory}/print', [InventoryPrintController::class, 'print'])->name('inventories.print');
    Route::get('units', App\Livewire\Pages\Units\Index::class)->name('units.index');
    Route::get('reports', App\Livewire\Pages\Reports\Index::class)->name('reports.index');
    Route::get('settings', App\Livewire\Pages\Settings\Index::class)->name('settings.index');
    Route::get('users', UsersIndex::class)->name('users.index');
    Route::get('roles', RolesIndex::class)->name('roles.index');
    Route::get('companies', App\Livewire\Pages\Companies\Index::class)->name('companies.index');
    Route::get('document-templates', App\Livewire\Pages\DocumentTemplates\Index::class)->name('document-templates.index');
    Route::get('cash-registers', App\Livewire\Pages\CashRegisters\Index::class)->name('cash-registers.index');
    Route::get('cash-registers/{cashRegister}/print', [CashRegisterPrintController::class, 'rapport'])->name('cash-registers.print');
    Route::get('customer-returns', App\Livewire\Pages\CustomerReturns\Index::class)->name('customer-returns.index');
    Route::get('sales/{sale}/cancellation', [CancellationPrintController::class, 'print'])->name('sales.cancellation.print');
    Route::get('support', App\Livewire\Pages\Support\Index::class)->name('support.index');
    Route::get('alerts', App\Livewire\Pages\Alerts\Index::class)->name('alerts.index');
    Route::get('audit-logs', App\Livewire\Pages\AuditLogs\Index::class)->name('audit-logs.index');
    Route::get('imports', App\Livewire\Pages\Imports\Index::class)->name('imports.index');
    Route::get('exports', App\Livewire\Pages\Exports\Index::class)->name('exports.index');
    Route::get('exports/{type}/{format}', [App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
