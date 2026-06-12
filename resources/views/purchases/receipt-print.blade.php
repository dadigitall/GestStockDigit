<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de réception — {{ $receipt->reference }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { font-size: 22px; margin: 0 0 5px; text-transform: uppercase; }
        .header h2 { font-size: 16px; margin: 0; color: #555; font-weight: normal; }
        .meta { width: 100%; margin-bottom: 25px; }
        .meta td { padding: 4px 10px; vertical-align: top; }
        .meta td:first-child { font-weight: 600; width: 180px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table.items th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; border: 1px solid #d1d5db; }
        table.items td { padding: 6px 10px; border: 1px solid #d1d5db; font-size: 11px; }
        table.items .qty { text-align: center; }
        table.items .price { text-align: right; }
        .footer { text-align: center; color: #888; font-size: 10px; border-top: 1px solid #d1d5db; padding-top: 15px; margin-top: 30px; }
        .signatures { margin-top: 40px; }
        .signatures td { width: 33%; text-align: center; padding-top: 40px; font-size: 11px; }
        .signatures td .line { border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 24px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
        .no-print button:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨 Imprimer</button>
        <button onclick="window.close()" style="background:#6b7280;margin-left:8px">Fermer</button>
    </div>

    <div class="header">
        <h1>Bon de réception</h1>
        <h2>N° {{ $receipt->reference }}</h2>
    </div>

    <table class="meta">
        <tr>
            <td>Date de réception</td>
            <td>{{ $receipt->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Société</td>
            <td>{{ $receipt->company?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Fournisseur</td>
            <td><strong>{{ $receipt->supplier?->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Commande associée</td>
            <td>{{ $receipt->purchaseOrder?->reference ?? '-' }}</td>
        </tr>
        <tr>
            <td>Magasin / Entrepôt</td>
            <td>{{ $receipt->store?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Réceptionné par</td>
            <td>{{ $receipt->user?->name ?? '-' }}</td>
        </tr>
        @if($receipt->notes)
            <tr>
                <td>Notes</td>
                <td>{{ $receipt->notes }}</td>
            </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:40%">Produit</th>
                <th class="qty" style="width:12%">Qté commandée</th>
                <th class="qty" style="width:12%">Qté acceptée</th>
                <th class="qty" style="width:12%">Qté rejetée</th>
                <th class="price" style="width:12%">P.U.</th>
                <th class="price" style="width:12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'Produit #'.$item->product_id }}</td>
                    <td class="qty">{{ number_format($item->quantity_ordered, 0, ',', ' ') }}</td>
                    <td class="qty"><strong>{{ number_format($item->quantity_accepted, 0, ',', ' ') }}</strong></td>
                    <td class="qty">{{ $item->quantity_rejected > 0 ? number_format($item->quantity_rejected, 0, ',', ' ') : '-' }}</td>
                    <td class="price">{{ number_format($item->unit_cost, 0, ',', ' ') }} F</td>
                    <td class="price">{{ number_format($item->unit_cost * $item->quantity_accepted, 0, ',', ' ') }} F</td>
                </tr>
                @if($item->lot_number || $item->expiry_date)
                    <tr style="background:#f9fafb">
                        <td colspan="6" style="font-size:10px;color:#666;padding:2px 10px">
                            @if($item->lot_number) Lot: <strong>{{ $item->lot_number }}</strong> @endif
                            @if($item->expiry_date) @if($item->lot_number) | @endif Exp: <strong>{{ $item->expiry_date->format('d/m/Y') }}</strong> @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td><div class="line">Réceptionné par</div></td>
            <td><div class="line">Vérifié par</div></td>
            <td><div class="line">Approuvé par</div></td>
        </tr>
    </table>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y H:i') }} · {{ $receipt->reference }} · GestStock Digit
    </div>
</body>
</html>
