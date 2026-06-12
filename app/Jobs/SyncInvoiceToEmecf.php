<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\EmecfSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInvoiceToEmecf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $invoiceId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmecfSyncService $syncService): void
    {
        $invoice = Invoice::with(['items', 'customer', 'sale', 'user'])
            ->find($this->invoiceId);

        if (!$invoice) {
            Log::warning('SyncInvoiceToEmecf: Facture introuvable', [
                'invoice_id' => $this->invoiceId,
            ]);
            return;
        }

        if ($invoice->isEmecfSynced()) {
            Log::info('SyncInvoiceToEmecf: Facture deja synchronisee', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference,
            ]);
            return;
        }

        if (!$invoice->sale_id) {
            Log::info('SyncInvoiceToEmecf: Facture sans sale_id, ignoree', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference,
            ]);
            return;
        }

        $result = $syncService->syncInvoice($invoice);

        if ($result['success']) {
            Log::info('SyncInvoiceToEmecf: Synchronisation reussie', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference,
                'emecf_uid' => $result['data']['uid'] ?? null,
                'code_mec_ef' => $result['data']['code_mec_ef'] ?? null,
            ]);
        } else {
            Log::warning('SyncInvoiceToEmecf: Synchronisation echouee', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference,
                'error' => $result['message'],
            ]);

            // Retry if it might be transient
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            }
        }
    }
}
