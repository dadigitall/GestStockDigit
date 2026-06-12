<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $sale->reference }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .item { display: flex; justify-content: space-between; font-size: 11px; }
        .item .left { flex: 1; }
        .item .right { text-align: right; white-space: nowrap; }
        .totals { margin-top: 8px; }
        .totals .line { display: flex; justify-content: space-between; font-size: 11px; }
        .totals .total { font-size: 14px; font-weight: bold; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
        .payment { margin-top: 5px; font-size: 11px; }
        @media print {
            body { width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ auth()->user()->company?->name ?? 'Magasin' }}</h2>
        <p>{{ auth()->user()->company?->address ?? '' }}</p>
        <p>Tél: {{ auth()->user()->company?->phone ?? '' }}</p>
        <div class="divider"></div>
        <p><strong>{{ $sale->reference }}</strong></p>
        <p>{{ $sale->sold_at?->format('d/m/Y H:i') ?? $sale->created_at->format('d/m/Y H:i') }}</p>
        <p>Vendeur: {{ $sale->user?->name ?? '—' }}</p>
        @if($sale->customer)
            <p>Client: {{ $sale->customer->name }}</p>
        @endif
        @if($sale->store)
            <p>Magasin: {{ $sale->store->name }}</p>
        @endif
    </div>

    <div class="divider"></div>

    @foreach($sale->items as $item)
        <div class="item">
            <span class="left">{{ $item->product_name }} x{{ number_format($item->quantity, 0) }}</span>
            <span class="right">{{ number_format($item->subtotal, 0, ',', ' ') }} F</span>
        </div>
        @if($item->unit_price > 0)
            <div style="font-size:10px;color:#666;margin-bottom:4px;">
                {{ number_format($item->unit_price, 0, ',', ' ') }} F x {{ number_format($item->quantity, 0) }}
            </div>
        @endif
    @endforeach

    <div class="divider"></div>

    <div class="totals">
        <div class="line"><span>Sous-total</span><span>{{ number_format($sale->subtotal, 0, ',', ' ') }} F</span></div>
        @if($sale->tax_amount > 0)
            <div class="line"><span>Taxes</span><span>{{ number_format($sale->tax_amount, 0, ',', ' ') }} F</span></div>
        @endif
        @if($sale->discount > 0)
            <div class="line"><span>Remise</span><span>-{{ number_format($sale->discount, 0, ',', ' ') }} F</span></div>
        @endif
        <div class="line total"><span>Total</span><span>{{ number_format($sale->total, 0, ',', ' ') }} F</span></div>
    </div>

    <div class="payment">
        <div class="line"><span>Payé</span><span>{{ number_format($sale->paid_amount, 0, ',', ' ') }} F</span></div>
        @if($sale->change_amount > 0)
            <div class="line"><span>Monnaie</span><span>{{ number_format($sale->change_amount, 0, ',', ' ') }} F</span></div>
        @endif
        <div class="line">
            <span>Paiement</span>
            <span>{{ str_replace(['_', 'cash', 'mobile_money', 'card', 'transfer', 'check', 'credit', 'gift_card', 'wallet'], [' ', 'Espèces', 'Mobile Money', 'Carte', 'Virement', 'Chèque', 'Crédit', 'Bon d\'achat', 'Portefeuille'], $sale->payment_method) }}</span>
        </div>
        @if($sale->payment_method_secondary)
            <div class="line">
                <span>Paiement 2</span>
                <span>{{ str_replace(['_', 'cash', 'mobile_money', 'card', 'transfer', 'check', 'credit', 'gift_card', 'wallet'], [' ', 'Espèces', 'Mobile Money', 'Carte', 'Virement', 'Chèque', 'Crédit', 'Bon d\'achat', 'Portefeuille'], $sale->payment_method_secondary) }}: {{ number_format($sale->payment_secondary_amount, 0, ',', ' ') }} F</span>
            </div>
        @endif
    </div>

    @if(auth()->user()->company?->ticket_footer)
        <div class="divider"></div>
        <div class="footer">
            {!! nl2br(e(auth()->user()->company->ticket_footer)) !!}
        </div>
    @endif

    <div class="divider"></div>
    <div class="footer">
        <p>Merci de votre visite !</p>
        <p style="font-size:9px;margin-top:10px;">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <div style="text-align:center;margin-top:20px;" class="no-print">
        <button onclick="window.print()" style="padding:10px 30px;font-size:14px;cursor:pointer;">Imprimer</button>
        <button onclick="window.close()" style="padding:10px 30px;font-size:14px;cursor:pointer;">Fermer</button>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
