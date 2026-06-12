<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\EmecfSyncService;
use Illuminate\Console\Command;

class SyncEmecfInvoices extends Command
{
    protected $signature = 'invoices:sync-emecf
        {invoice? : ID de la facture à synchroniser (omettre pour synchroniser toutes les factures en attente)}
        {--force : Forcer la synchronisation même si déjà synchronisée}
        {--dry-run : Simuler sans envoyer à l\'API}
        {--limit=10 : Nombre max de factures à synchroniser (par défaut 10)}
    ';

    protected $description = 'Synchroniser les factures avec le système e-MECeF (Bénin DGI)';

    public function handle(EmecfSyncService $syncService): int
    {
        $invoiceId = $this->argument('invoice');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($invoiceId) {
            return $this->syncSingleInvoice($syncService, (int) $invoiceId);
        }

        return $this->syncPendingInvoices($syncService, $limit, $force, $dryRun);
    }

    private function syncSingleInvoice(EmecfSyncService $syncService, int $invoiceId): int
    {
        $invoice = Invoice::with(['items', 'customer', 'sale', 'user'])
            ->find($invoiceId);

        if (!$invoice) {
            $this->error("Facture #{$invoiceId} introuvable.");
            return Command::FAILURE;
        }

        $this->line("Facture #{$invoice->id}: {$invoice->reference} ({$invoice->customer?->name ?? 'N/A'})");

        if ($invoice->isEmecfSynced()) {
            $this->warn('  Déjà synchronisée.');
            return Command::SUCCESS;
        }

        $this->info('  Synchronisation en cours...');

        $result = $syncService->syncInvoice($invoice);

        if ($result['success']) {
            $this->info('  ✅ ' . $result['message']);
            $this->line("     UID: {$result['data']['uid']}");
            $this->line("     Code MECeF: {$result['data']['code_mec_ef']}");
            return Command::SUCCESS;
        }

        $this->error('  ❌ ' . $result['message']);
        return Command::FAILURE;
    }

    private function syncPendingInvoices(
        EmecfSyncService $syncService,
        int $limit,
        bool $force,
        bool $dryRun,
    ): int {
        $query = Invoice::with(['items', 'customer', 'sale', 'user'])
            ->where('company_id', $this->getCompanyId())
            ->whereNotNull('sale_id');

        if (!$force) {
            $query->whereNull('emecf_status');
        }

        $total = $query->count();
        $invoices = $query->take($limit)->get();

        if ($total === 0) {
            $this->info('Aucune facture en attente de synchronisation.');
            return Command::SUCCESS;
        }

        $this->info("{$total} facture(s) trouvée(s). Synchronisation de {$invoices->count()} facture(s)...\n");

        if ($dryRun) {
            $this->warn('--- MODE DRY-RUN --- Aucun appel API effectué ---');
            foreach ($invoices as $invoice) {
                $status = $invoice->isEmecfSynced() ? '✅ déjà synchro' : '⏳ en attente';
                $this->line("  #{$invoice->id} {$invoice->reference} — {$invoice->customer?->name} [{$status}]");
            }
            $this->warn('--- FIN DRY-RUN ---');
            return Command::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $this->line("  #{$invoice->id} {$invoice->reference} — {$invoice->customer?->name ?? 'N/A'}...");

            $result = $syncService->syncInvoice($invoice);

            if ($result['success']) {
                $this->info("    ✅ {$result['message']}");
                $success++;
            } else {
                $this->error("    ❌ {$result['message']}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Résultat : {$success} succès, {$failed} échec(s) sur {$invoices->count()} facture(s).");

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function getCompanyId(): ?int
    {
        return \App\Models\Company::value('id');
    }
}
