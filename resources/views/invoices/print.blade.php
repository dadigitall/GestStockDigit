<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .company-info { max-width: 50%; }
        .company-name { font-size: 16px; font-weight: 700; color: #4f46e5; margin-bottom: 4px; }
        .company-detail { font-size: 9px; color: #6b7280; line-height: 1.5; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 24px; color: #4f46e5; margin: 0 0 4px; }
        .invoice-title .ref { font-size: 12px; color: #6b7280; }
        .divider { border: none; border-top: 2px solid #4f46e5; margin: 16px 0; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .meta-box { }
        .meta-box .label { font-size: 8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 2px; }
        .meta-box .value { font-size: 11px; font-weight: 600; color: #1f2937; }
        .meta-box .value.sub { font-size: 9px; font-weight: 400; color: #6b7280; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 9px; }
        table.items th { background: #f3f4f6; text-align: left; padding: 6px 4px; border-bottom: 2px solid #e5e7eb; font-size: 8px; text-transform: uppercase; color: #6b7280; }
        table.items td { padding: 5px 4px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { margin-left: auto; width: 280px; }
        .totals table { width: 100%; }
        .totals td { padding: 3px 0; font-size: 10px; }
        .totals .label { color: #6b7280; }
        .totals .value { text-align: right; font-weight: 600; }
        .totals .grand td { padding-top: 6px; border-top: 2px solid #1f2937; font-size: 14px; font-weight: 700; }
        .totals .paid td { color: #059669; }
        .totals .due td { color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .footer .block { font-size: 9px; color: #6b7280; margin-bottom: 8px; }
        .footer .block strong { color: #374151; }
        .signature { margin-top: 48px; font-size: 10px; }
        .signature-line { width: 200px; border-top: 1px solid #1f2937; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #6b7280; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 8px; font-weight: 600; }
        .badge-draft { background: #f3f4f6; color: #6b7280; }
        .badge-sent { background: #dbeafe; color: #2563eb; }
        .badge-paid { background: #d1fae5; color: #059669; }
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
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <div class="ref">{{ $invoice->reference }}</div>
            <div style="margin-top:8px;">
                <span class="badge {{ 'badge-' . $invoice->status }}">
                    {{ ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'partially_paid' => 'Partielle', 'overdue' => 'En retard', 'cancelled' => 'Annulée'][$invoice->status] ?? $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <hr class="divider">

    <div class="meta">
        <div class="meta-box">
            <div class="label">Facturé à</div>
            <div class="value">{{ $invoice->customer?->name ?? 'Client inconnu' }}</div>
            @if($invoice->customer?->address)<div class="value sub">{{ $invoice->customer->address }}</div>@endif
            @if($invoice->customer?->phone)<div class="value sub">{{ $invoice->customer->phone }}</div>@endif
            @if($invoice->customer?->email)<div class="value sub">{{ $invoice->customer->email }}</div>@endif
            @if($invoice->customer?->tax_number)<div class="value sub">NIF : {{ $invoice->customer->tax_number }}</div>@endif
        </div>
        <div class="meta-box" style="text-align:right">
            <div class="label">Date d'émission</div>
            <div class="value">{{ $invoice->issue_date->format('d/m/Y') }}</div>
            @if($invoice->due_date)
                <div class="label" style="margin-top:8px;">Date d'échéance</div>
                <div class="value">{{ $invoice->due_date->format('d/m/Y') }}</div>
            @endif
            @if($invoice->user)
                <div class="label" style="margin-top:8px;">Éditée par</div>
                <div class="value sub">{{ $invoice->user->name }}</div>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50px;">Qté</th>
                <th>Désignation</th>
                <th style="width:80px;" class="text-right">PU HT</th>
                <th style="width:60px;" class="text-right">Remise</th>
                <th style="width:80px;" class="text-right">Total HT</th>
                <th style="width:50px;" class="text-right">TVA</th>
                <th style="width:90px;" class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="text-center">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->product_reference)
                            <br><span style="font-size:8px;color:#9ca3af;">Réf : {{ $item->product_reference }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ $item->discount > 0 ? number_format($item->discount, 0, ',', ' ') . ' F' : '-' }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="text-right">{{ number_format($item->tax_rate, 0) }}%</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td class="label">Sous-total HT</td><td class="value">{{ number_format($invoice->subtotal, 0, ',', ' ') }} F</td></tr>
            @if($invoice->discount > 0)
                <tr><td class="label">Remise</td><td class="value" style="color:#dc2626;">-{{ number_format($invoice->discount, 0, ',', ' ') }} F</td></tr>
            @endif
            <tr><td class="label">TVA</td><td class="value">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} F</td></tr>
            <tr class="grand"><td class="label">Total TTC</td><td class="value">{{ number_format($invoice->total, 0, ',', ' ') }} F</td></tr>
            @if($invoice->paid_amount > 0)
                <tr class="paid"><td class="label">Montant payé</td><td class="value">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} F</td></tr>
                <tr class="due"><td class="label">Reste dû</td><td class="value">{{ number_format($invoice->amount_due, 0, ',', ' ') }} F</td></tr>
            @endif
        </table>
    </div>

    <div class="footer">
        @if($invoice->payment_terms)
            <div class="block"><strong>Conditions de paiement :</strong><br>{{ $invoice->payment_terms }}</div>
        @endif
        @if($invoice->notes)
            <div class="block"><strong>Notes :</strong><br>{{ $invoice->notes }}</div>
        @endif
        @if($company->invoice_footer)
            <div class="block" style="font-style:italic;">{{ $company->invoice_footer }}</div>
        @endif
    </div>

    <div class="signature">
        <div style="display:flex; justify-content:space-between;">
            <div>
                <div class="label" style="font-size:8px;text-transform:uppercase;color:#9ca3af;">Cachet et signature du client</div>
                <div class="signature-line">&nbsp;</div>
            </div>
            <div style="text-align:right;">
                <div class="label" style="font-size:8px;text-transform:uppercase;color:#9ca3af;">Cachet et signature de l'entreprise</div>
                <div class="signature-line" style="margin-left:auto;">&nbsp;</div>
            </div>
        </div>
    </div>

    <div style="margin-top:32px;padding-top:12px;border-top:1px solid #e5e7eb;font-size:8px;color:#9ca3af;text-align:center;">
        Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }} — {{ $company->name }}
    </div>
</body>
</html>
