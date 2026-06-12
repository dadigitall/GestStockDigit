<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Codianselme\LaraSygmef\Services\EmecfService;
use Illuminate\Support\Facades\Log;

class EmecfSyncService
{
    public function __construct(
        private readonly EmecfService $emecfService,
    ) {}

    /**
     * Send an invoice to e-MECeF and confirm it.
     *
     * @return array{success: bool, message: string, data?: array}
     */
    public function syncInvoice(Invoice $invoice): array
    {
        if ($invoice->isEmecfSynced()) {
            return [
                'success' => false,
                'message' => 'Cette facture est déjà synchronisée avec e-MECeF.',
            ];
        }

        $customer = $invoice->customer;
        $items = $invoice->items;

        if ($items->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucun article dans la facture.',
            ];
        }

        $paymentMethod = $this->mapPaymentMethod(
            $invoice->sale?->payment_method ?? 'cash'
        );

        // Build e-MECeF payload
        $payload = [
            'ifu' => config('emecf.default_ifu'),
            'type' => 'FV', // Facture de Vente
            'operator' => [
                'name' => $invoice->user?->name ?? 'Opérateur',
            ],
            'client' => [
                'name' => $customer?->name ?? 'Client Divers',
                'contact' => $customer?->phone ?? '',
                'ifu' => $customer?->tax_number ?? '',
                'address' => $customer?->address ?? '',
            ],
            'items' => $items->map(fn (InvoiceItem $item) => [
                'name' => $item->product_name,
                'price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'taxGroup' => 'B', // B = 18% TVA (Benin standard)
            ])->toArray(),
            'payment' => [
                [
                    'name' => $paymentMethod,
                    'amount' => (float) $invoice->total,
                ],
            ],
        ];

        try {
            // Step 1: Submit invoice to e-MECeF
            $result = $this->emecfService->submitInvoice($payload);

            if (!$result['success']) {
                Log::error('e-MECeF submission failed', [
                    'invoice_id' => $invoice->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => $result['error'] ?? 'Échec de la soumission à e-MECeF.',
                ];
            }

            $uid = $result['data']['uid'] ?? null;
            $localId = $result['data']['local_id'] ?? null;

            if (!$uid) {
                return [
                    'success' => false,
                    'message' => 'Aucun UID reçu de l\'API e-MECeF.',
                ];
            }

            // Step 2: Confirm invoice
            $confirmResult = $this->emecfService->finalizeInvoice($uid, 'confirm');

            if (!$confirmResult['success']) {
                Log::error('e-MECeF confirmation failed', [
                    'invoice_id' => $invoice->id,
                    'uid' => $uid,
                    'error' => $confirmResult['error'] ?? 'Unknown error',
                ]);

                // Update invoice with pending status
                $invoice->update([
                    'emecf_uid' => $uid,
                    'emecf_invoice_id' => $localId,
                    'emecf_status' => 'pending',
                    'emecf_sent_at' => now(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Facture soumise mais non confirmée : ' . ($confirmResult['error'] ?? 'Erreur inconnue'),
                ];
            }

            $data = $confirmResult['data'];

            // Step 3: Update our invoice with e-MECeF data
            $invoice->update([
                'emecf_invoice_id' => $localId,
                'emecf_uid' => $uid,
                'emecf_code' => $data['codeMECeFDGI'] ?? $data['codeMECeF'] ?? null,
                'emecf_qr_code' => $data['qrCode'] ?? null,
                'emecf_status' => 'confirmed',
                'emecf_sent_at' => now(),
            ]);

            Log::info('Invoice synced to e-MECeF', [
                'invoice_id' => $invoice->id,
                'emecf_uid' => $uid,
                'emecf_code' => $invoice->emecf_code,
            ]);

            return [
                'success' => true,
                'message' => 'Facture synchronisée avec e-MECeF avec succès.',
                'data' => [
                    'uid' => $uid,
                    'code_mec_ef' => $invoice->emecf_code,
                    'qr_code' => $invoice->emecf_qr_code,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('e-MECeF sync exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception lors de la synchronisation : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Map our payment methods to e-MECeF payment types.
     */
    private function mapPaymentMethod(string $method): string
    {
        return match ($method) {
            'cash' => 'ESPECES',
            'mobile_money' => 'MOBILEMONEY',
            'card' => 'CARTEBANCAIRE',
            'check' => 'CHEQUE',
            'transfer' => 'VIREMENT',
            'credit' => 'ESPECES', // Fallback to ESPECES
            default => 'ESPECES',
        };
    }
}
