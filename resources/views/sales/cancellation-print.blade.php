<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Annulation {{ $sale->reference }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .company-info { max-width: 50%; }
        .company-name { font-size: 16px; font-weight: 700; color: #dc2626; margin-bottom: 4px; }
        .company-detail { font-size: 9px; color: #6b7280; line-height: 1.5; }
        .title { text-align: right; }
        .title h1 { font-size: 24px; color: #dc2626; margin: 0 0 4px; }
        .title .ref { font-size: 12px; color: #6b7280; }
        .divider { border: none; border-top: 2px solid #dc2626; margin: 16px 0; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .meta-box { }
        .meta-box .label { font-size: 8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 2px; }
        .meta-box .value { font-size: 11px; font-weight: 600; color: #1f2937; }
        .meta-box .value.sub { font-size: 9px; font-weight: 400; color: #6b7280; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 9px; }
        table.items th { background: #fef2f2; text-align: left; padding: 6px 4px; border-bottom: 2px solid #fecaca; font-size: 8px; text-transform: uppercase; color: #dc2626; }
        table.items td { padding: 5px 4px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { margin-left: auto; width: 280px; }
        .totals table { width: 100%; }
        .totals td { padding: 3px 0; font-size: 10px; }
        .totals .label { color: #6b7280; }
        .totals .value { text-align: right; font-weight: 600; }
        .totals .grand td { padding-top: 6px; border-top: 2px solid #1f2937; font-size: 14px; font-weight: 700; }
        .reason-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 12px; margin-bottom: 24px; }
        .reason-box .label { font-size: 8px; text-transform: uppercase; color: #dc2626; margin-bottom: 4px; }
        .reason-box .text { font-size: 10px; color: #991b1b; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .footer .block { font-size: 9px; color: #6b7280; margin-bottom: 8px; }
        .footer .block strong { color: #374151; }
        .signature { margin-top: 48px; font-size: 10px; }
        .signature-line { width: 200px; border-top: 1px solid #1f2937; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            @if($company->logo)
                <img src="{{ storage_path('app/public/' . $company->logo) }}" alt="Logo" style="max-height:60px; margin-bottom:8px;">
            @endif
            <div class="company-name">{{ $company->legal_name ?? $company->name }}</div>
            <div class="company-detail">
                @if($company->address){{ $company->address }}<br>@endif
                @if($company->phone)Tél : {{ $company->phone }}<br>@endif
                @if($company->email)Email : {{ $company->email }}<br>@endif
                @if($company->tax_number)NIF : {{ $company->tax_number }}<br>@endif
                @if($company->registration_number)RCCM : {{ $company->registration_number }}@endif
            </div>
        </div>
        <div class="title">
            <h1>ANNULATION DE VENTE</h1>
            <div class="ref">{{ $sale->reference }}</div>
            <div style="margin-top:8px; font-size:9px; color:#6b7280;">Document : CNC-{{ $sale->reference }}</div>
        </div>
    </div>

    <hr class="divider">

    <div class="meta">
        <div class="meta-box">
            <div class="label">Client</div>
            <div class="value">{{ $sale->customer?->name ?? 'Client inconnu' }}</div>
            @if($sale->customer?->phone)<div class="value sub">{{ $sale->customer->phone }}</div>@endif
        </div>
        <div class="meta-box" style="text-align:right">
            <div class="label">Date d'annulation</div>
            <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
            @if($sale->user)
                <div class="label" style="margin-top:8px;">Annulée par</div>
                <div class="value sub">{{ $sale->user->name }}</div>
            @endif
        </div>
    </div>

    <div class="reason-box">
        <div class="label">Motif d'annulation</div>
        <div class="text">{{ $sale->notes ? (str_contains($sale->notes, 'Annulation:') ? substr($sale->notes, strpos($sale->notes, 'Annulation:') + 11) : $sale->notes) : 'Non spécifié' }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50px;">Qté</th>
                <th>Produit</th>
                <th style="width:80px;" class="text-right">PU</th>
                <th style="width:90px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td class="text-center">{{ number_format(abs($item->quantity), 0, ',', ' ') }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->product_reference)
                            <br><span style="font-size:8px;color:#9ca3af;">Réf : {{ $item->product_reference }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format(abs($item->unit_price), 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format(abs($item->subtotal), 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td class="label">Total vente annulée</td><td class="value">{{ number_format(abs($sale->total), 0, ',', ' ') }} F</td></tr>
            @if($sale->paid_amount > 0)
                <tr class="grand" style="color:#dc2626;"><td class="label">Montant remboursé</td><td class="value">{{ number_format(abs($sale->paid_amount), 0, ',', ' ') }} F</td></tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <div class="block"><strong>Statut :</strong> Vente annulée — Stock réintégré</div>
        @if($sale->store)
            <div class="block"><strong>Point de vente :</strong> {{ $sale->store->name }}</div>
        @endif
    </div>

    <div class="signature">
        <div style="display:flex; justify-content:space-between;">
            <div>
                <div class="label" style="font-size:8px;text-transform:uppercase;color:#9ca3af;">Signature du responsable</div>
                <div class="signature-line">&nbsp;</div>
            </div>
            <div style="text-align:right;">
                <div class="label" style="font-size:8px;text-transform:uppercase;color:#9ca3af;">Cachet de l'entreprise</div>
                <div class="signature-line" style="margin-left:auto;">&nbsp;</div>
            </div>
        </div>
    </div>

    <div style="margin-top:32px;padding-top:12px;border-top:1px solid #e5e7eb;font-size:8px;color:#9ca3af;text-align:center;">
        Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }} — {{ $company->name }}
    </div>
</body>
</html>
