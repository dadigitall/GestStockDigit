<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport {{ ucfirst($reportType) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        h3 { font-size: 12px; margin-top: 15px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #f5f5f5; text-align: left; padding: 4px 6px; font-size: 9px; text-transform: uppercase; }
        td { padding: 3px 6px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }
        .text-bold { font-weight: bold; }
        .summary { margin-bottom: 15px; }
        .summary td { border: none; padding: 2px 10px; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: 10px; right: 10px; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <h1>Rapport {{ ucfirst($reportType) }}</h1>
    <p style="color:#666;font-size:9px;">{{ $companyName }} — {{ $dateLabel }}</p>

    @if($reportType === 'sales')
        <h2>Résumé des ventes</h2>
        <table class="summary">
            <tr><td>Chiffre d'affaires</td><td class="text-right text-bold">{{ number_format($salesSummary['total_revenue'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Nombre de ventes</td><td class="text-right">{{ $salesSummary['total_sales'] ?? 0 }}</td></tr>
            <tr><td>Panier moyen</td><td class="text-right">{{ number_format($salesSummary['avg_basket'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Marge brute</td><td class="text-right text-bold">{{ number_format($salesSummary['gross_margin'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Taux de marge</td><td class="text-right">{{ $salesSummary['margin_rate'] ?? 0 }}%</td></tr>
        </table>

        @if(count($salesByProduct) > 0)
            <h2>Ventes par produit</h2>
            <table>
                <tr><th>Produit</th><th class="text-right">Quantité</th><th class="text-right">Revenu</th></tr>
                @foreach($salesByProduct as $p)
                    <tr><td>{{ $p['product_name'] }}</td><td class="text-right">{{ (int) $p['total_qty'] }}</td><td class="text-right">{{ number_format($p['total_revenue'], 0, ',', ' ') }} F</td></tr>
                @endforeach
            </table>
        @endif

        @if(count($marginByProduct) > 0)
            <h2>Marges par produit</h2>
            <table>
                <tr><th>Produit</th><th class="text-right">Revenu</th><th class="text-right">Coût</th><th class="text-right">Marge</th></tr>
                @foreach($marginByProduct as $m)
                    <tr><td>{{ $m['product_name'] }}</td><td class="text-right">{{ number_format($m['revenue'], 0, ',', ' ') }} F</td><td class="text-right">{{ number_format($m['cost'], 0, ',', ' ') }} F</td><td class="text-right {{ $m['margin'] >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($m['margin'], 0, ',', ' ') }} F</td></tr>
                @endforeach
            </table>
        @endif

    @elseif($reportType === 'stock')
        <h2>État du stock</h2>
        @if(count($stockState) > 0)
            <table>
                <tr><th>Produit</th><th class="text-right">Stock</th><th class="text-right">Min</th><th class="text-right">Prix achat</th></tr>
                @foreach($stockState as $p)
                    @if(!isset($p['_summary']))
                        <tr><td>{{ $p['name'] ?? 'N/A' }}</td><td class="text-right">{{ number_format($p['stock_quantity'] ?? 0, 2, ',', ' ') }}</td><td class="text-right">{{ number_format($p['min_stock'] ?? 0, 2, ',', ' ') }}</td><td class="text-right">{{ number_format($p['purchase_price'] ?? 0, 0, ',', ' ') }} F</td></tr>
                    @endif
                @endforeach
            </table>
        @endif

        <h2>Valeur du stock : {{ number_format($stockState['_summary']['total_value'] ?? 0, 0, ',', ' ') }} F</h2>

        @if(count($expiredProducts) > 0)
            <h2>Produits expirés</h2>
            <table>
                <tr><th>Produit</th><th>Lot</th><th class="text-right">Qté</th></tr>
                @foreach($expiredProducts as $lot)
                    <tr><td>{{ $lot->product_name ?? 'N/A' }}</td><td>{{ $lot->lot_number ?? 'N/A' }}</td><td class="text-right text-red">{{ number_format($lot->remaining_quantity ?? 0, 2, ',', ' ') }}</td></tr>
                @endforeach
            </table>
        @endif

    @elseif($reportType === 'purchases')
        <h2>Achats par fournisseur</h2>
        @if(count($purchaseBySupplier) > 0)
            <table>
                <tr><th>Fournisseur</th><th class="text-right">Commandes</th><th class="text-right">Montant</th></tr>
                @foreach($purchaseBySupplier as $s)
                    <tr><td>{{ $s['supplier']['name'] ?? 'N/A' }}</td><td class="text-right">{{ $s['total_orders'] }}</td><td class="text-right">{{ number_format($s['total_amount'], 0, ',', ' ') }} F</td></tr>
                @endforeach
            </table>
        @endif

        @if(count($pendingOrders) > 0)
            <h2>Commandes en attente</h2>
            <table>
                <tr><th>Réf.</th><th>Fournisseur</th><th>Statut</th><th class="text-right">Total</th></tr>
                @foreach($pendingOrders as $o)
                    @if(!isset($o['_summary']))
                        <tr><td>{{ $o['reference'] ?? $o->reference ?? 'N/A' }}</td><td>{{ $o['supplier']['name'] ?? $o->supplier->name ?? 'N/A' }}</td><td>{{ $o['status'] ?? $o->status ?? 'N/A' }}</td><td class="text-right">{{ number_format($o['total'] ?? $o->total ?? 0, 0, ',', ' ') }} F</td></tr>
                    @endif
                @endforeach
            </table>
        @endif

    @elseif($reportType === 'financial')
        <h2>Synthèse financière</h2>
        <table class="summary">
            <tr><td>Encaissements</td><td class="text-right text-bold text-green">{{ number_format($cashSummary['total_in'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Décaissements</td><td class="text-right text-bold text-red">{{ number_format($cashSummary['total_out'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Solde</td><td class="text-right text-bold">{{ number_format($cashSummary['balance'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Bénéfice brut</td><td class="text-right text-bold text-green">{{ number_format($cashSummary['gross_profit'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Marge brute</td><td class="text-right">{{ $cashSummary['gross_margin_pct'] ?? 0 }}%</td></tr>
        </table>

        @if(count($creditSales) > 0)
            <h2>Ventes à crédit</h2>
            <table>
                <tr><th>Réf.</th><th>Client</th><th class="text-right">Dû</th></tr>
                @foreach($creditSales as $s)
                    <tr><td>{{ $s['reference'] ?? $s->reference ?? 'N/A' }}</td><td>{{ $s['customer']['name'] ?? $s->customer->name ?? 'N/A' }}</td><td class="text-right text-red">{{ number_format($s['due'] ?? $s->total - $s->paid_amount ?? 0, 0, ',', ' ') }} F</td></tr>
                @endforeach
            </table>
        @endif

    @elseif($reportType === 'analysis')
        <h2>Analyse comparative</h2>
        <table class="summary">
            <tr><td>Période précédente</td><td class="text-right">{{ number_format($previousPeriod['revenue'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Période actuelle</td><td class="text-right">{{ number_format($currentPeriod['revenue'] ?? 0, 0, ',', ' ') }} F</td></tr>
            <tr><td>Évolution</td><td class="text-right text-bold">{{ $currentPeriod && $previousPeriod && ($previousPeriod['revenue'] ?? 0) > 0 ? round((($currentPeriod['revenue'] - $previousPeriod['revenue']) / $previousPeriod['revenue']) * 100, 1) : 0 }}%</td></tr>
        </table>

        @if(count($abcAnalysis) > 0)
            <h2>Analyse ABC</h2>
            <table>
                <tr><th>Classe</th><th>Produit</th><th class="text-right">%</th><th class="text-right">Cumul</th></tr>
                @foreach($abcAnalysis as $a)
                    <tr><td>{{ $a['class'] }}</td><td>{{ $a['product_name'] }}</td><td class="text-right">{{ $a['percent'] }}%</td><td class="text-right">{{ $a['cumulative'] }}%</td></tr>
                @endforeach
            </table>
        @endif

        @if(count($seasonality) > 0)
            <h2>Saisonnalité</h2>
            <table>
                <tr><th>Période</th><th class="text-right">Ventes</th><th class="text-right">Montant</th></tr>
                @foreach($seasonality as $s)
                    <tr><td>{{ $s['period'] }}</td><td class="text-right">{{ $s['count'] }}</td><td class="text-right">{{ number_format($s['total'], 0, ',', ' ') }} F</td></tr>
                @endforeach
            </table>
        @endif

        @if(count($marginByFamily) > 0)
            <h2>Marge par famille</h2>
            <table>
                <tr><th>Famille</th><th class="text-right">Revenu</th><th class="text-right">Marge</th><th class="text-right">Taux</th></tr>
                @foreach($marginByFamily as $m)
                    @php $mPct = $m['revenue'] > 0 ? round(($m['margin'] / $m['revenue']) * 100, 1) : 0; @endphp
                    <tr><td>{{ $m['name'] }}</td><td class="text-right">{{ number_format($m['revenue'], 0, ',', ' ') }} F</td><td class="text-right">{{ number_format($m['margin'], 0, ',', ' ') }} F</td><td class="text-right">{{ $mPct }}%</td></tr>
                @endforeach
            </table>
        @endif

        @if(count($stockoutForecast) > 0)
            <h2>Prévision des ruptures</h2>
            <table>
                <tr><th>Produit</th><th class="text-right">Stock</th><th class="text-right">Ventes/j</th><th class="text-right">J avant rupture</th></tr>
                @foreach($stockoutForecast as $f)
                    <tr>
                        <td>{{ $f['name'] }}</td>
                        <td class="text-right">{{ number_format($f['stock_quantity'], 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($f['daily_rate'], 2, ',', ' ') }}</td>
                        <td class="text-right">{{ $f['days_until_out'] >= 999 ? '—' : $f['days_until_out'] . ' j' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif

    <div class="footer">Généré le {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
