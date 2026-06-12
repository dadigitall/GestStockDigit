<?php

namespace App\Services;

use App\Mail\AlertMail;
use App\Models\Alert;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Lot;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertService
{
    public function runAllChecks(?int $companyId = null): array
    {
        $companies = $companyId
            ? [\App\Models\Company::find($companyId)]
            : \App\Models\Company::where('is_active', true)->get();

        $results = [];

        foreach ($companies as $company) {
            if (!$company) continue;
            $cid = $company->id;

            $results[$cid] = [
                'stock_out' => $this->checkStockOut($cid),
                'low_stock' => $this->checkLowStock($cid),
                'near_expiry' => $this->checkNearExpiry($cid),
                'unpaid_invoice' => $this->checkUnpaidInvoices($cid),
                'credit_exceeded' => $this->checkCreditExceeded($cid),
                'late_order' => $this->checkLateOrders($cid),
                'transfer_pending' => $this->checkTransferPending($cid),
                'requisition_received' => $this->checkRequisitions($cid),
                'cash_register_open' => $this->checkCashRegisters($cid),
                'inventory_discrepancy' => $this->checkInventoryDiscrepancies($cid),
            ];
        }

        return $results;
    }

    public function checkStockOut(int $companyId): int
    {
        $products = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('stock_quantity', '<=', 0)
            ->get();

        foreach ($products as $product) {
            $this->createAlert(
                companyId: $companyId,
                type: 'stock_out',
                severity: 'danger',
                title: 'Rupture de stock : ' . $product->name,
                message: "Le produit {$product->name} (réf. {$product->reference}) est en rupture de stock.",
                actionUrl: route('products.edit', $product->id),
                notifiable: $product,
                data: ['product_id' => $product->id, 'stock' => $product->stock_quantity],
            );
        }

        return $products->count();
    }

    public function checkLowStock(int $companyId): int
    {
        $products = Product::where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('stock_quantity', '>', 0)
            ->get();

        foreach ($products as $product) {
            $this->createAlert(
                companyId: $companyId,
                type: 'low_stock',
                severity: 'warning',
                title: 'Stock bas : ' . $product->name,
                message: "Le produit {$product->name} (réf. {$product->reference}) a atteint son stock minimum. Stock : {$product->stock_quantity}, Min : {$product->min_stock}.",
                actionUrl: route('stock.index'),
                notifiable: $product,
                data: ['product_id' => $product->id, 'stock' => $product->stock_quantity, 'min' => $product->min_stock],
            );
        }

        return $products->count();
    }

    public function checkNearExpiry(int $companyId): int
    {
        $lots = Lot::where('company_id', $companyId)
            ->where('remaining_quantity', '>', 0)
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->with('product:id,name,reference')
            ->get();

        foreach ($lots as $lot) {
            $this->createAlert(
                companyId: $companyId,
                type: 'near_expiry',
                severity: 'warning',
                title: 'Expiration proche : ' . ($lot->product->name ?? 'N/A'),
                message: "Le lot {$lot->lot_number} du produit {$lot->product->name} expire le {$lot->expiry_date->format('d/m/Y')}. Quantité restante : {$lot->remaining_quantity}.",
                actionUrl: route('stock.index'),
                notifiable: $lot,
                data: ['lot_id' => $lot->id, 'product_id' => $lot->product_id, 'expiry' => $lot->expiry_date->format('Y-m-d'), 'remaining' => $lot->remaining_quantity],
            );
        }

        return $lots->count();
    }

    public function checkUnpaidInvoices(int $companyId): int
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->where('amount_due', '>', 0)
            ->where('due_date', '<', now())
            ->with('customer:id,name')
            ->get();

        foreach ($invoices as $invoice) {
            $this->createAlert(
                companyId: $companyId,
                type: 'unpaid_invoice',
                severity: 'danger',
                title: 'Facture impayée : ' . ($invoice->reference ?? 'N/A'),
                message: "La facture {$invoice->reference} de {$invoice->customer->name} est impayée. Montant dû : " . number_format($invoice->amount_due, 0, ',', ' ') . " F. Échue depuis le {$invoice->due_date->format('d/m/Y')}.",
                actionUrl: route('invoices.index'),
                notifiable: $invoice,
                data: ['invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount_due' => $invoice->amount_due],
            );
        }

        return $invoices->count();
    }

    public function checkCreditExceeded(int $companyId): int
    {
        $customers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('credit_limit', '>', 0)
            ->whereColumn('balance', '>', 'credit_limit')
            ->get();

        foreach ($customers as $customer) {
            $this->createAlert(
                companyId: $companyId,
                type: 'credit_exceeded',
                severity: 'danger',
                title: 'Crédit dépassé : ' . $customer->name,
                message: "Le client {$customer->name} a dépassé sa limite de crédit. Solde : " . number_format($customer->balance, 0, ',', ' ') . " F, Limite : " . number_format($customer->credit_limit, 0, ',', ' ') . " F.",
                actionUrl: route('customers.edit', $customer->id),
                notifiable: $customer,
                data: ['customer_id' => $customer->id, 'balance' => $customer->balance, 'credit_limit' => $customer->credit_limit],
            );
        }

        return $customers->count();
    }

    public function checkLateOrders(int $companyId): int
    {
        $orders = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['approved', 'ordered'])
            ->where('delivery_date', '<', now())
            ->with('supplier:id,name')
            ->get();

        foreach ($orders as $order) {
            $this->createAlert(
                companyId: $companyId,
                type: 'late_order',
                severity: 'warning',
                title: 'Commande fournisseur en retard : ' . ($order->reference ?? 'N/A'),
                message: "La commande {$order->reference} auprès de {$order->supplier->name} est en retard. Livraison attendue le {$order->delivery_date->format('d/m/Y')}.",
                actionUrl: route('purchase-orders.show', $order->id),
                notifiable: $order,
                data: ['order_id' => $order->id, 'supplier_id' => $order->supplier_id, 'expected_date' => $order->delivery_date?->format('Y-m-d')],
            );
        }

        return $orders->count();
    }

    public function checkTransferPending(int $companyId): int
    {
        $transfers = Transfer::where('company_id', $companyId)
            ->whereIn('status', ['requested'])
            ->with('sourceStore:id,name', 'destinationStore:id,name')
            ->get();

        foreach ($transfers as $transfer) {
            $this->createAlert(
                companyId: $companyId,
                type: 'transfer_pending',
                severity: 'info',
                title: 'Transfert à valider',
                message: "Un transfert de {$transfer->sourceStore->name} vers {$transfer->destinationStore->name} est en attente de validation.",
                actionUrl: route('transfers.show', $transfer->id),
                notifiable: $transfer,
                data: ['transfer_id' => $transfer->id],
                userId: null,
            );
        }

        return $transfers->count();
    }

    public function checkRequisitions(int $companyId): int
    {
        $requisitions = PurchaseRequisition::where('company_id', $companyId)
            ->where('status', 'pending')
            ->with('store:id,name', 'requester:id,name')
            ->get();

        foreach ($requisitions as $req) {
            $this->createAlert(
                companyId: $companyId,
                type: 'requisition_received',
                severity: 'info',
                title: 'Demande d\'approvisionnement',
                message: "Une demande d'approvisionnement de {$req->store->name} par {$req->requester->name} est en attente.",
                actionUrl: route('purchase-requisitions.index'),
                notifiable: $req,
                data: ['requisition_id' => $req->id],
            );
        }

        return $requisitions->count();
    }

    public function checkCashRegisters(int $companyId): int
    {
        $registers = CashRegister::where('company_id', $companyId)
            ->where('status', 'open')
            ->where('opened_at', '<', now()->subHours(12))
            ->with('store:id,name', 'user:id,name,first_name,last_name')
            ->get();

        foreach ($registers as $register) {
            $this->createAlert(
                companyId: $companyId,
                type: 'cash_register_open',
                severity: 'warning',
                title: 'Caisse non clôturée : ' . ($register->name ?? 'N/A'),
                message: "La caisse {$register->name} de {$register->store->name} est ouverte depuis " . $register->opened_at->diffForHumans() . " par {$register->user->first_name} {$register->user->last_name}.",
                actionUrl: route('cash-registers.index'),
                notifiable: $register,
                data: ['register_id' => $register->id],
            );
        }

        return $registers->count();
    }

    public function checkInventoryDiscrepancies(int $companyId): int
    {
        $inventories = Inventory::where('company_id', $companyId)
            ->where('total_discrepancies', '>', 0)
            ->whereIn('status', ['completed', 'validated'])
            ->with('store:id,name')
            ->get();

        foreach ($inventories as $inventory) {
            $this->createAlert(
                companyId: $companyId,
                type: 'inventory_discrepancy',
                severity: 'warning',
                title: 'Écart d\'inventaire : ' . ($inventory->reference ?? 'N/A'),
                message: "L'inventaire {$inventory->reference} de {$inventory->store->name} a {$inventory->total_discrepancies} écart(s) pour une valeur de " . number_format($inventory->total_discrepancy_value, 0, ',', ' ') . " F.",
                actionUrl: route('inventories.show', $inventory->id),
                notifiable: $inventory,
                data: ['inventory_id' => $inventory->id, 'discrepancies' => $inventory->total_discrepancies, 'value' => $inventory->total_discrepancy_value],
            );
        }

        return $inventories->count();
    }

    public function createAlert(
        int $companyId,
        string $type,
        string $severity,
        string $title,
        string $message,
        ?string $actionUrl = null,
        $notifiable = null,
        array $data = [],
        ?int $userId = null,
        bool $sendEmail = true,
    ): ?Alert {
        $notifiableType = $notifiable ? get_class($notifiable) : null;
        $notifiableId = $notifiable?->id;

        if ($notifiableType && $notifiableId) {
            $existing = Alert::where('company_id', $companyId)
                ->where('type', $type)
                ->where('notifiable_type', $notifiableType)
                ->where('notifiable_id', $notifiableId)
                ->whereNull('read_at')
                ->where('created_at', '>=', now()->subDay())
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $alert = Alert::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'data' => $data,
        ]);

        if ($sendEmail) {
            $this->sendEmail($alert);
        }

        return $alert;
    }

    public function sendEmail(Alert $alert): void
    {
        try {
            $users = User::where('company_id', $alert->company_id)
                ->where('is_active', true)
                ->get();

            foreach ($users as $user) {
                Mail::to($user->email)->queue(new AlertMail(
                    title: $alert->title,
                    message: $alert->message,
                    actionUrl: $alert->action_url,
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send alert email: ' . $e->getMessage());
        }
    }

    public function logSensitiveAction(User $user, string $action, array $context = []): ?Alert
    {
        return $this->createAlert(
            companyId: $user->company_id,
            type: 'sensitive_action',
            severity: 'info',
            title: 'Action sensible : ' . $action,
            message: "L'utilisateur {$user->name} a effectué une action sensible : {$action}.",
            data: array_merge(['user_id' => $user->id, 'action' => $action], $context),
            sendEmail: false,
        );
    }

    public function logSuspiciousLogin(User $user, string $ip, string $userAgent): ?Alert
    {
        return $this->createAlert(
            companyId: $user->company_id,
            type: 'suspicious_login',
            severity: 'danger',
            title: 'Connexion suspecte',
            message: "Connexion suspecte détectée pour l'utilisateur {$user->name} depuis l'IP {$ip}.",
            data: ['user_id' => $user->id, 'ip' => $ip, 'user_agent' => $userAgent],
            sendEmail: true,
        );
    }
}
