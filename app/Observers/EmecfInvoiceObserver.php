<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\EmecfSyncService;
use Illuminate\Support\Facades\Log;

class EmecfInvoiceObserver
{
    public function __construct(
        private readonly EmecfSyncService $emecfSyncService,
    ) {}

    /**
     * Auto-sync new invoices to e-MECeF when they are created from a sale.
     */
    public function created(Invoice $invoice): void
    {
        // Only sync invoices linked to a sale (created from POS/wholesale)
        if (!$invoice->sale_id) {
            return;
        }

        // Don't re-sync if already marked
        if ($invoice->isEmecfSynced()) {
            return;
        }

        // Skip in CLI context (seeding, commands) to avoid API calls
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        try {
            $result = $this->emecfSyncService->syncInvoice($invoice);

            if ($result['success']) {
                Log::info('e-MECeF auto-sync reussi', [
                    'invoice_id' => $invoice->id,
                    'reference' => $invoice->reference,
                    'emecf_uid' => $result['data']['uid'] ?? null,
                ]);
            } else {
                Log::warning('e-MECeF auto-sync echoue', [
                    'invoice_id' => $invoice->id,
                    'reference' => $invoice->reference,
                    'error' => $result['message'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('e-MECeF auto-sync exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
