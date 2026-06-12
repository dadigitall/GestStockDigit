<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function products(string $format)
    {
        $data = Product::where('company_id', auth()->user()->company_id)
            ->with('category', 'supplier')
            ->get()
            ->toArray();

        $headers = ['Référence', 'Code-barres', 'Nom', 'Catégorie', 'Fournisseur', 'Prix achat', 'Prix vente', 'Stock', 'Stock min', 'Unité', 'Actif'];

        $rows = array_map(fn($p) => [
            $p['reference'] ?? '', $p['barcode'] ?? '', $p['name'] ?? '',
            $p['category']['name'] ?? '', $p['supplier']['name'] ?? '',
            $p['purchase_price'] ?? 0, $p['sale_price'] ?? 0,
            $p['stock_quantity'] ?? 0, $p['min_stock'] ?? 0,
            $p['unit_sale'] ?? '', $p['is_active'] ?? true ? 'Oui' : 'Non',
        ], $data);

        return $this->respond($format, 'produits', $headers, $rows);
    }

    public function customers(string $format)
    {
        $data = Customer::where('company_id', auth()->user()->company_id)->get()->toArray();
        $headers = ['Nom', 'Type', 'Téléphone', 'Email', 'Adresse', 'Limite crédit', 'Conditions paiement', 'Actif'];
        $rows = array_map(fn($c) => [
            $c['name'] ?? '', $c['type'] ?? '', $c['phone'] ?? '', $c['email'] ?? '',
            $c['address'] ?? '', $c['credit_limit'] ?? 0, $c['payment_terms'] ?? '', $c['is_active'] ? 'Oui' : 'Non',
        ], $data);

        return $this->respond($format, 'clients', $headers, $rows);
    }

    public function suppliers(string $format)
    {
        $data = Supplier::where('company_id', auth()->user()->company_id)->get()->toArray();
        $headers = ['Nom', 'Type', 'Contact', 'Téléphone', 'Email', 'Adresse', 'Conditions paiement', 'Délai livraison (j)', 'Actif'];
        $rows = array_map(fn($s) => [
            $s['name'] ?? '', $s['type'] ?? '', $s['contact_name'] ?? '', $s['phone'] ?? '',
            $s['email'] ?? '', $s['address'] ?? '', $s['payment_terms'] ?? '',
            $s['delivery_delay_days'] ?? 0, $s['is_active'] ? 'Oui' : 'Non',
        ], $data);

        return $this->respond($format, 'fournisseurs', $headers, $rows);
    }

    public function stock(string $format)
    {
        $data = Product::where('company_id', auth()->user()->company_id)
            ->where('is_stockable', true)
            ->get()->toArray();

        $headers = ['Référence', 'Nom', 'Stock actuel', 'Stock min', 'Valeur stock', 'Dernier mouvement'];
        $rows = array_map(fn($p) => [
            $p['reference'] ?? '', $p['name'] ?? '',
            $p['stock_quantity'] ?? 0, $p['min_stock'] ?? 0,
            ($p['stock_quantity'] ?? 0) * ($p['purchase_price'] ?? 0),
            $p['updated_at'] ?? '',
        ], $data);

        return $this->respond($format, 'stocks', $headers, $rows);
    }

    public function sales(string $format)
    {
        $data = Sale::where('company_id', auth()->user()->company_id)
            ->with('customer', 'items')
            ->get();

        $headers = ['N° Facture', 'Client', 'Date', 'Montant HT', 'TVA', 'Montant TTC', 'Statut', 'Mode paiement'];
        $rows = $data->map(fn($i) => [
            $i->reference ?? '', $i->customer?->name ?? '',
            $i->sold_at?->format('Y-m-d') ?? $i->created_at->format('Y-m-d'),
            $i->subtotal ?? 0, $i->tax_amount ?? 0, $i->total ?? 0,
            $i->status ?? '', $i->payment_method ?? '',
        ])->toArray();

        return $this->respond($format, 'ventes', $headers, $rows);
    }

    public function purchases(string $format)
    {
        $data = PurchaseOrder::where('company_id', auth()->user()->company_id)
            ->with('supplier')
            ->get();

        $headers = ['N°', 'Fournisseur', 'Date', 'Montant HT', 'TVA', 'Montant TTC', 'Statut'];
        $rows = $data->map(fn($i) => [
            $i->reference ?? '', $i->supplier?->name ?? '',
            $i->created_at->format('Y-m-d'),
            $i->subtotal ?? 0, $i->tax_amount ?? 0, $i->total ?? 0,
            $i->status ?? '',
        ])->toArray();

        return $this->respond($format, 'achats', $headers, $rows);
    }

    public function invoices(string $format)
    {
        $companyId = auth()->user()->company_id;

        $saleInvoices = Sale::where('company_id', $companyId)
            ->with('customer')->get()
            ->map(fn($i) => [
                'N°' => $i->reference ?? '',
                'Type' => 'Vente',
                'Tiers' => $i->customer?->name ?? '',
                'Date' => $i->sold_at?->format('Y-m-d') ?? $i->created_at->format('Y-m-d'),
                'Montant' => $i->total ?? 0,
                'Statut' => $i->status ?? '',
            ]);

        $purchaseInvoices = PurchaseOrder::where('company_id', $companyId)
            ->with('supplier')->get()
            ->map(fn($i) => [
                'N°' => $i->reference ?? '',
                'Type' => 'Achat',
                'Tiers' => $i->supplier?->name ?? '',
                'Date' => $i->created_at->format('Y-m-d'),
                'Montant' => $i->total ?? 0,
                'Statut' => $i->status ?? '',
            ]);

        $rows = $saleInvoices->concat($purchaseInvoices)->sortBy('Date')->values()->toArray();
        $headers = ['N°', 'Type', 'Tiers', 'Date', 'Montant', 'Statut'];

        return $this->respond($format, 'factures', $headers, $rows);
    }

    public function auditLogs(string $format)
    {
        if (!class_exists(\App\Models\AuditLog::class)) {
            $headers = ['Info'];
            $rows = [['Module journal d\'audit non installé.']];
            return $this->respond($format, 'journal_audit', $headers, $rows);
        }

        $data = \App\Models\AuditLog::where('company_id', auth()->user()->company_id)
            ->orderBy('created_at', 'desc')
            ->limit(5000)
            ->get()
            ->toArray();

        $headers = ['Date', 'Utilisateur', 'Action', 'Entité', 'ID Entité', 'Détails', 'Adresse IP'];
        $rows = array_map(fn($l) => [
            $l['created_at'] ?? '', $l['user_name'] ?? $l['user_id'] ?? '',
            $l['action'] ?? '', $l['entity_type'] ?? '', $l['entity_id'] ?? '',
            is_string($l['details'] ?? null) ? substr($l['details'], 0, 200) : '',
            $l['ip_address'] ?? '',
        ], $data);

        return $this->respond($format, 'journal_audit', $headers, $rows);
    }

    public function download(string $type, string $format)
    {
        if (!in_array($format, ['csv', 'xls'])) {
            abort(400, 'Format non supporté');
        }

        $allowed = ['products', 'customers', 'suppliers', 'stock', 'sales', 'purchases', 'invoices', 'audit'];
        if (!in_array($type, $allowed)) {
            abort(404, 'Type d\'export inconnu');
        }

        app(AuditService::class)->log([
            'company_id' => auth()->user()->company_id,
            'action' => 'exported',
            'module' => 'exports',
            'entity_type' => match ($type) {
                'products' => 'App\Models\Product',
                'customers' => 'App\Models\Customer',
                'suppliers' => 'App\Models\Supplier',
                'stock' => 'App\Models\Product',
                'sales' => 'App\Models\Sale',
                'purchases' => 'App\Models\PurchaseOrder',
                'audit' => 'App\Models\AuditLog',
                default => null,
            },
            'reason' => "Export {$type} au format {$format}",
        ]);

        $method = match ($type) {
            'audit' => 'auditLogs',
            default => $type,
        };

        return $this->{$method}($format);
    }

    protected function respond(string $format, string $name, array $headers, array $rows): StreamedResponse|\Illuminate\Http\Response
    {
        return match ($format) {
            'csv' => $this->csv($name, $headers, $rows),
            'xls' => $this->xls($name, $headers, $rows),
            default => abort(400, 'Format non supporté'),
        };
    }

    protected function csv(string $name, array $headers, array $rows): StreamedResponse
    {
        return Response::streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, "export_{$name}_" . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function xls(string $name, array $headers, array $rows): \Illuminate\Http\Response
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="UTF-8"><title>' . $name . '</title></head><body>';
        $html .= '<table border="1"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th style="background:#4f46e5;color:#fff;padding:6px 10px;font-weight:bold;">' . e($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="padding:4px 8px;">' . e((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return Response::make($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"export_{$name}_" . now()->format('Ymd_His') . '.xls"',
        ]);
    }
}
