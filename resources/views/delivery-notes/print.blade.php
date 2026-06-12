<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de livraison {{ $deliveryNote->reference }}</title>
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
                @if($company?->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="h-16 mb-3">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ $company?->name ?? '' }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $company?->address ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $company?->phone ?? '' }}</p>
                <p class="text-sm text-gray-600">{{ $company?->email ?? '' }}</p>
                @if($company?->tax_number)
                    <p class="text-sm text-gray-600">NIF: {{ $company->tax_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-indigo-600 uppercase tracking-wider">BON DE LIVRAISON</h2>
                <p class="text-lg font-mono font-semibold text-gray-800 mt-1">{{ $deliveryNote->reference }}</p>
                <p class="text-sm text-gray-500 mt-2">Date de livraison: {{ $deliveryNote->delivery_date?->format('d/m/Y') ?? '—' }}</p>
                @if($deliveryNote->received_date)
                    <p class="text-sm text-gray-500">Reçu le: {{ $deliveryNote->received_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Client --}}
        <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Client</h3>
            <p class="font-medium text-gray-900">{{ $deliveryNote->customer?->name ?? 'Client inconnu' }}</p>
            @if($deliveryNote->customer?->address)
                <p class="text-sm text-gray-600">{{ $deliveryNote->customer->address }}</p>
            @endif
            @if($deliveryNote->customer?->phone)
                <p class="text-sm text-gray-600">{{ $deliveryNote->customer->phone }}</p>
            @endif
            @if($deliveryNote->customer?->email)
                <p class="text-sm text-gray-600">{{ $deliveryNote->customer->email }}</p>
            @endif
            @if($deliveryNote->customer?->tax_number)
                <p class="text-sm text-gray-600">NIF: {{ $deliveryNote->customer->tax_number }}</p>
            @endif
        </div>

        {{-- Tableau des articles --}}
        <table class="w-full mb-6 border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 w-16">Qté</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Désignation</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Unité</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2.5 text-sm text-gray-900">{{ number_format($item->quantity_delivered, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2.5 text-sm text-gray-900">
                            {{ $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id) }}
                        </td>
                        <td class="px-3 py-2.5 text-sm text-gray-600">{{ $item->unit ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-sm text-gray-600">{{ $item->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Notes --}}
        @if($deliveryNote->notes)
            <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Notes</h3>
                <p class="text-sm text-gray-700">{{ $deliveryNote->notes }}</p>
            </div>
        @endif

        {{-- Signature --}}
        <div class="mt-12 pt-6 border-t border-gray-300">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Pour le client,</p>
                    <p class="text-sm text-gray-600">Nom et prénom: ___________________________</p>
                    <div class="h-12"></div>
                    <p class="text-sm font-medium text-gray-900">Signature</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-1">Pour l'entreprise,</p>
                    <p class="text-sm text-gray-600">Nom et prénom: ___________________________</p>
                    <div class="h-12"></div>
                    <p class="text-sm font-medium text-gray-900">Signature</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-xs text-gray-400">
            <p>{{ $company?->name ?? '' }} · {{ $company?->address ?? '' }} · {{ $company?->phone ?? '' }}</p>
            <p>Généré le {{ now()->format('d/m/Y H:i') }} par {{ $deliveryNote->user?->name ?? auth()->user()->name }}</p>
        </div>
    </div>
</body>
</html>
