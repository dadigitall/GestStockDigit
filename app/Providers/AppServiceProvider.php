<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Observers\EmecfInvoiceObserver;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Services\AlertService;
use App\Services\AuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AlertService::class);
        $this->app->singleton(AuditService::class);
    }

    public function boot(): void
    {
        Product::observe(AuditObserver::class);
        Category::observe(AuditObserver::class);
        Customer::observe(AuditObserver::class);
        Supplier::observe(AuditObserver::class);
        Sale::observe(AuditObserver::class);
        PurchaseOrder::observe(AuditObserver::class);
        Invoice::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
        Role::observe(AuditObserver::class);

        // Auto-sync new invoices to e-MECeF
        Invoice::observe(EmecfInvoiceObserver::class);

        Event::listen(function (Login $event) {
            if ($event->user instanceof User) {
                $ip = request()->ip();
                $userAgent = request()->userAgent();

                app(AuditService::class)->logAuthEvent('login', $event->user);

                app(AlertService::class)->createAlert(
                    companyId: $event->user->company_id,
                    type: 'suspicious_login',
                    severity: 'info',
                    title: 'Nouvelle connexion : ' . $event->user->name,
                    message: "L'utilisateur {$event->user->name} s'est connecté depuis {$ip}.",
                    data: [
                        'user_id' => $event->user->id,
                        'ip' => $ip,
                        'user_agent' => $userAgent,
                    ],
                    sendEmail: false,
                );
            }
        });

        Event::listen(function (Logout $event) {
            if ($event->user instanceof User) {
                app(AuditService::class)->logAuthEvent('logout', $event->user);
            }
        });

        Event::listen(function (Failed $event) {
            $email = $event->credentials['email'] ?? 'inconnu';
            app(AuditService::class)->logAuthEvent('failed_login', null, "Tentative pour : {$email}");
        });
    }
}
