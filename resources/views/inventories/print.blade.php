<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport d'inventaire {{ $inventory->reference }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; }
        .header h1 { font-size: 18px; margin: 0; color: #4f46e5; }
        .header p { margin: 4px 0 0; color: #6b7280; }
        .meta { margin-bottom: 20px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 3px 8px; vertical-align: top; }
        .meta .label { color: #6b7280; width: 120px; }
        .meta .value { font-weight: 600; }
        .summary { display: flex; gap: 16px; margin-bottom: 20px; }
        .summary-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; text-align: center; }
        .summary-box .number { font-size: 20px; font-weight: 700; }
        .summary-box .lbl { font-size: 9px; color: #6b7280; margin-top: 2px; }
        table.items { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 6px 4px; border-bottom: 2px solid #e5e7eb; font-size: 8px; text-transform: uppercase; color: #6b7280; }
        table.items td { padding: 4px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-red { color: #dc2626; }
        .text-green { color: #059669; }
        .text-amber { color: #d97706; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: center; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 99px; font-size: 8px; font-weight: 600; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #6b7280; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport d'inventaire</h1>
        <p>{{ $inventory->reference }} — {{ $inventory->title }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="meta">
        <table>
            <tr><td class="label">Type</td><td class="value">{{ ucfirst(str_replace('_', ' ', $inventory->type)) }}</td></tr>
            <tr><td class="label">Statut</td><td class="value">{{ str_replace('_', ' ', $inventory->status) }}</td></tr>
            <tr><td class="label">Créé par</td><td class="value">{{ $inventory->creator?->name ?? '-' }}</td></tr>
            <tr><td class="label">Date création</td><td class="value">{{ $inventory->created_at->format('d/m/Y H:i') }}</td></tr>
            @if($inventory->store)<tr><td class="label">Magasin</td><td class="value">{{ $inventory->store->name }}</td></tr>@endif
            @if($inventory->category)<tr><td class="label">Catégorie</td><td class="value">{{ $inventory->category->name }}</td></tr>@endif
            @if($inventory->frozen_at)<tr><td class="label">Gelé le</td><td class="value">{{ $inventory->frozen_at->format('d/m/Y H:i') }}</td></tr>@endif
            @if($inventory->completed_at)<tr><td class="label">Terminé le</td><td class="value">{{ $inventory->completed_at->format('d/m/Y H:i') }}</td></tr>@endif
            @if($inventory->validated_by)<tr><td class="label">Validé par</td><td class="value">{{ $inventory->validator?->name ?? '-' }}</td></tr>@endif
            @if($inventory->notes)<tr><td colspan="2" style="padding-top:8px"><strong>Notes :</strong> {{ $inventory->notes }}</td></tr>@endif
        </table>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="number">{{ $inventory->items->count() }}</div>
            <div class="lbl">Articles</div>
        </div>
        <div class="summary-box">
            <div class="number" style="color: {{ $inventory->total_discrepancies > 0 ? '#d97706' : '#059669' }}">{{ $inventory->total_discrepancies }}</div>
            <div class="lbl">Écarts</div>
        </div>
        <div class="summary-box">
            <div class="number" style="color: {{ $inventory->total_discrepancy_value != 0 ? '#dc2626' : '#059669' }}">{{ number_format($inventory->total_discrepancy_value, 0, ',', ' ') }} F</div>
            <div class="lbl">Valeur écart</div>
        </div>
        <div class="summary-box">
            <div class="number">{{ $inventory->approvedItems->count() }}</div>
            <div class="lbl">Approuvés</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Stock</th>
                <th>Lot</th>
                <th class="text-right">Théorique</th>
                <th class="text-right">Physique</th>
                <th class="text-right">Écart qté</th>
                <th class="text-right">Écart valeur</th>
                <th>Responsable</th>
                <th>Justification</th>
                <th class="text-center">Décision</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory->items as $item)
                @php $hasDiff = $item->discrepancy_quantity != 0; @endphp
                <tr>
                    <td>{{ $item->product?->name ?? '#' . $item->product_id }}</td>
                    <td>{{ $item->store?->name ?? '-' }}</td>
                    <td>{{ $item->lot?->lot_number ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->theoretical_quantity, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ $item->physical_quantity !== null ? number_format($item->physical_quantity, 0, ',', ' ') : '—' }}</td>
                    <td class="text-right {{ $item->discrepancy_quantity > 0 ? 'text-green' : ($item->discrepancy_quantity < 0 ? 'text-red' : '') }}">
                        {{ $item->discrepancy_quantity > 0 ? '+' : '' }}{{ number_format($item->discrepancy_quantity, 0, ',', ' ') }}
                    </td>
                    <td class="text-right {{ $item->discrepancy_value != 0 ? 'text-red' : '' }}">{{ number_format(abs($item->discrepancy_value), 0, ',', ' ') }} F</td>
                    <td>{{ $item->counter?->name ?? '-' }}</td>
                    <td>{{ $item->justification ?? '-' }}</td>
                    <td class="text-center">
                        @if($item->decision === 'approved')
                            <span class="badge badge-green">✓ Approuvé</span>
                        @elseif($item->decision === 'rejected')
                            <span class="badge badge-red">✗ Rejeté</span>
                        @elseif($hasDiff)
                            <span class="badge badge-gray">En attente</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>GestStockDigit — Rapport d'inventaire {{ $inventory->reference }}</p>
        <p>Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
