<?php

namespace App\Observers;

use App\Jobs\SyncInvoiceToEmecf;
use App\Models\Invoice;

class EmecfInvoiceObserver
{
    /**
     * Dispatch a queued job to sync new invoices to e-MECeF
     * when they are created from a sale.
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

        // Dispatch the job to the default queue (database driver)
        SyncInvoiceToEmecf::dispatch($invoice->id);
    }
}
