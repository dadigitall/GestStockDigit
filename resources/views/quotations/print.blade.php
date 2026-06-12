<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quotation->reference }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { margin: 20mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 12px; }
    </style>
</head>
<body class="bg-white text-gray-900">
    <div class="max-w-[210mm] mx-auto p-8">

        {{-- Bouton imprimer --}}
        <div class="no-print text-center mb-6">
            <button onclick="window.print()" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm">Imprimer ce document</button>
            <a href="{{ url()->previous() }}" class="ml-2 px-6 py-2.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 shadow-sm">Retour</a>
        </div>

        {{-- En-tête --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                @if($quotation->company?->logo)
                    <img src="{{ asset('storage/' . $quotation->company->logo) }}" alt="Logo" class="h-16 mb-3">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ $quotation->company?->name ?? '' }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $quotation->company?->address ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $quotation->company?->phone ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $quotation->company?->email ?? '' }}</p>
                @if($quotation->company?->tax_number)
                    <p class="text-sm text-gray-600">NIF: {{ $quotation->company->tax_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-indigo-600 uppercase tracking-wider">DEVIS</h2>
                <p class="text-lg font-mono font-semibold text-gray-800 mt-1">{{ $quotation->reference }}</p>
                <p class="text-sm text-gray-500 mt-2">Date: {{ $quotation->created_at?->format('d/m/Y') ?? '—' }}</p>
                @if($quotation->validity_date)
                    <p class="text-sm text-gray-500">Valable jusqu'au: {{ $quotation->validity_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Client --}}
        <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Client</h3>
            <p class="font-medium text-gray-900">{{ $quotation->customer?->name ?? 'Client inconnu' }}</p>
            @if($quotation->customer?->address)
                <p class="text-sm text-gray-600">{{ $quotation->customer->address }}</p>
            @endif
            @if($quotation->customer?->phone)
                <p class="text-sm text-gray-600">{{ $quotation->customer->phone }}</p>
            @endif
            @if($quotation->customer?->email)
                <p class="text-sm text-gray-600">{{ $quotation->customer->email }}</p>
            @endif
            @if($quotation->customer?->tax_number)
                <p class="text-sm text-gray-600">NIF: {{ $quotation->customer->tax_number }}</p>
            @endif
        </div>

        {{-- Tableau des articles --}}
        <table class="w-full mb-6 border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 w-16">Qté</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Désignation</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 w-28">Prix unitaire</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 w-20">Remise</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 w-28">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2.5 text-sm text-gray-900">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2.5 text-sm text-gray-900">
                            {{ $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id) }}
                            @if($item->product_reference)
                                <span class="text-gray-400 text-xs ml-1">({{ $item->product_reference }})</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-sm text-right text-gray-900">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                        <td class="px-3 py-2.5 text-sm text-right text-gray-600">{{ $item->discount > 0 ? $item->discount.'%' : '—' }}</td>
                        <td class="px-3 py-2.5 text-sm text-right font-medium text-gray-900">{{ number_format($item->subtotal, 0, ',', ' ') }} F</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totaux --}}
        <div class="flex justify-end mb-8">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Sous-total</span>
                    <span>{{ number_format($quotation->subtotal, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>TVA</span>
                    <span>{{ number_format($quotation->tax_amount, 0, ',', ' ') }} F</span>
                </div>
                @if($quotation->discount > 0)
                    <div class="flex justify-between text-sm text-red-600">
                        <span>Remise</span>
                        <span>-{{ number_format($quotation->discount, 0, ',', ' ') }} F</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-bold text-gray-900 border-t-2 border-gray-300 pt-2 mt-2">
                    <span>Total</span>
                    <span>{{ number_format($quotation->total, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        {{-- Conditions --}}
        @if($quotation->commercial_terms)
            <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Conditions commerciales</h3>
                <p class="text-sm text-gray-700">{{ $quotation->commercial_terms }}</p>
            </div>
        @endif

        @if($quotation->validity_date)
            <p class="text-sm text-gray-500 mb-8">Ce devis est valable jusqu'au {{ $quotation->validity_date->format('d/m/Y') }}.</p>
        @endif

        {{-- Signature --}}
        <div class="mt-12 pt-6 border-t border-gray-300">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Pour le client,</p>
                    <div class="h-12"></div>
                    <p class="text-sm font-medium text-gray-900">Signature</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-1">Pour l'entreprise,</p>
                    <div class="h-12"></div>
                    <p class="text-sm font-medium text-gray-900">Signature</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-xs text-gray-400">
            <p>{{ $quotation->company?->name ?? '' }} · {{ $quotation->company?->address ?? '' }} · {{ $quotation->company?->phone ?? '' }}</p>
            <p>Généré le {{ now()->format('d/m/Y H:i') }} par {{ $quotation->user?->name ?? auth()->user()->name }}</p>
        </div>
    </div>
</body>
</html>
