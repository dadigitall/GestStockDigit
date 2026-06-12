<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;

Route::get('/', function (): JsonResponse {
    return response()->json([
        'name' => 'GestStockDigit API',
        'version' => '1.0',
        'documentation' => '/api',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/login' => 'Authentification',
                'POST /api/auth/logout' => 'Déconnexion (auth requis)',
            ],
            'products' => [
                'GET /api/products' => 'Liste des produits',
                'GET /api/products/{id}' => 'Détail d\'un produit',
                'POST /api/products' => 'Créer un produit',
                'PUT /api/products/{id}' => 'Modifier un produit',
                'DELETE /api/products/{id}' => 'Supprimer un produit',
                'GET /api/products/{id}/stock' => 'Stock d\'un produit',
            ],
            'sales' => [
                'GET /api/sales' => 'Liste des ventes',
                'POST /api/sales' => 'Créer une vente',
                'GET /api/sales/{id}' => 'Détail d\'une vente',
            ],
            'invoices' => [
                'GET /api/invoices' => 'Liste des factures',
                'GET /api/invoices/{id}' => 'Détail d\'une facture',
            ],
            'stock' => [
                'GET /api/stock' => 'Niveaux de stock',
                'PUT /api/stock/sync' => 'Synchronisation stock (e-commerce)',
            ],
            'customers' => [
                'GET /api/customers' => 'Liste des clients',
                'POST /api/customers' => 'Créer un client',
            ],
            'suppliers' => [
                'GET /api/suppliers' => 'Liste des fournisseurs',
            ],
        ],
    ]);
});

Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.auth.logout');

Route::middleware('auth:sanctum')->prefix('products')->name('api.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('/{id}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/stock', [ProductController::class, 'stock'])->name('stock');
});

Route::middleware('auth:sanctum')->prefix('sales')->name('api.sales.')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('index');
    Route::post('/', [SaleController::class, 'store'])->name('store');
    Route::get('/{id}', [SaleController::class, 'show'])->name('show');
});

Route::middleware('auth:sanctum')->prefix('invoices')->name('api.invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
});

Route::middleware('auth:sanctum')->prefix('stock')->name('api.stock.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::put('/sync', [StockController::class, 'sync'])->name('sync');
});

Route::middleware('auth:sanctum')->prefix('customers')->name('api.customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
});

Route::middleware('auth:sanctum')->prefix('suppliers')->name('api.suppliers.')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
});
