<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de caisse - {{ $cashRegister->name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 15px; }
        .header h1 { font-size: 18px; margin: 0 0 5px 0; }
        .header p { margin: 2px 0; color: #555; font-size: 11px; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 5px 8px; font-size: 11px; }
        .summary .label { color: #555; }
        .summary .value { text-align: right; font-weight: bold; }
        .summary .total-row { border-top: 2px solid #1a1a1a; font-weight: bold; }
        .summary .subtotal { border-top: 1px solid #d1d5db; }
        .summary .in { color: #059669; }
        .summary .out { color: #dc2626; }
        .section-title { font-size: 13px; font-weight: bold; margin: 15px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        table.details { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.details th { background: #f3f4f6; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #d1d5db; }
        table.details td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        table.details .amount { text-align: right; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 10px; color: #888; text-align: center; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-in { background: #d1fae5; color: #065f46; }
        .badge-out { background: #fee2e2; color: #991b1b; }
        .signatures { margin-top: 30px; display: flex; justify-content: space-between; }
        .signatures div { text-align: center; width: 45%; }
        .signatures .line { border-top: 1px solid #1a1a1a; margin-top: 40px; padding-top: 5px; font-size: 10px; color: #555; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de caisse</h1>
        <p><strong>{{ $cashRegister->name }}</strong> · {{ $cashRegister->store?->name }}</p>
        <p>{{ $cashRegister->code ? "Code: {$cashRegister->code} · " : '' }}
           Période: {{ ucfirst($period) }}
           @if($period === 'custom' && request('from')) du {{ request('from') }} au {{ request('to') ?? request('from') }} @endif
        </p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="summary">
        <div class="section-title">Clôture de caisse (8.50)</div>
        <table>
            <tr><td class="label">Fond initial</td><td class="value">{{ number_format($cashRegister->initial_balance, 0, ',', ' ') }} F</td></tr>
            @php
                $summary = $cashRegister->closingSummary();
            @endphp
            @if($summary)
                <tr><td class="label">Total ventes espèces</td><td class="value in">+{{ number_format($summary['cash_sales_cash'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Total Mobile Money</td><td class="value in">+{{ number_format($summary['mobile_money'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Total carte</td><td class="value in">+{{ number_format($summary['card'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Total crédits</td><td class="value in">+{{ number_format($summary['credits'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="subtotal"></td></tr>
                <tr><td class="label">Total remboursements</td><td class="value out">-{{ number_format($summary['refunds'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Total dépenses</td><td class="value out">-{{ number_format($summary['expenses'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Retraits propriétaire</td><td class="value out">-{{ number_format($summary['owner_withdrawals'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Dépôts bancaires</td><td class="value out">-{{ number_format($summary['bank_deposits'], 0, ',', ' ') }} F</td></tr>
                <tr class="total-row"><td class="label">Montant théorique</td><td class="value">{{ number_format($summary['expected'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Montant compté</td><td class="value">{{ number_format($summary['counted'], 0, ',', ' ') }} F</td></tr>
                <tr><td class="label">Écart</td><td class="value {{ $summary['difference'] >= 0 ? 'in' : 'out' }}">{{ $summary['difference'] >= 0 ? '+' : '' }}{{ number_format($summary['difference'], 0, ',', ' ') }} F</td></tr>
            @endif
        </table>
        @if($cashRegister->closing_note)
            <p style="margin-top:10px; font-size:11px; color:#555;"><strong>Commentaire:</strong> {{ $cashRegister->closing_note }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="section-title">Répartition par méthode de paiement</div>
        <table>
            @foreach($byPaymentMethod as $method => $amount)
                <tr>
                    <td class="label">{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                    <td class="value">{{ number_format($amount, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="label">Total</td>
                <td class="value">{{ number_format($byPaymentMethod->sum(), 0, ',', ' ') }} F</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <div class="section-title">Répartition par type de mouvement</div>
        <table>
            @foreach($byType as $type => $amount)
                @php
                    $direction = in_array($type, ['cash_sale', 'customer_payment', 'correction', 'opening_balance']) ? 'in' : 'out';
                @endphp
                <tr>
                    <td class="label">{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                    <td class="value {{ $direction === 'in' ? 'in' : 'out' }}">
                        {{ $direction === 'in' ? '+' : '-' }}{{ number_format($amount, 0, ',', ' ') }} F
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="label">Total entrées</td>
                <td class="value in">+{{ number_format($totalIn, 0, ',', ' ') }} F</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total sorties</td>
                <td class="value out">-{{ number_format($totalOut, 0, ',', ' ') }} F</td>
            </tr>
            <tr class="total-row">
                <td class="label">Solde théorique</td>
                <td class="value">{{ number_format($cashRegister->expected_balance, 0, ',', ' ') }} F</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Détail des mouvements</div>
    <table class="details">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Méthode</th>
                <th>Par</th>
                <th>Description</th>
                <th class="amount">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                    <td><span class="badge {{ $movement->direction === 'in' ? 'badge-in' : 'badge-out' }}">{{ ucfirst(str_replace('_', ' ', $movement->type)) }}</span></td>
                    <td>{{ $movement->payment_method }}</td>
                    <td>{{ $movement->user?->name }}</td>
                    <td>{{ $movement->description }}</td>
                    <td class="amount {{ $movement->direction === 'in' ? 'in' : 'out' }}">
                        {{ $movement->direction === 'in' ? '+' : '-' }}{{ number_format($movement->amount, 0, ',', ' ') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">Aucun mouvement</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div>
            <div class="line">
                Signature du caissier<br>
                @if($cashRegister->cashier_signature)
                    <em>{{ $cashRegister->cashier_signature }}</em>
                @else
                    ______________________
                @endif
            </div>
        </div>
        <div>
            <div class="line">
                Signature du responsable<br>
                @if($cashRegister->validator_signature)
                    <em>{{ $cashRegister->validator_signature }}</em>
                @else
                    ______________________
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        GestStock Digit — Rapport de caisse généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
