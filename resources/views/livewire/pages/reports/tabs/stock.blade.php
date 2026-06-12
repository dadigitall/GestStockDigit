<div class="space-y-6">
    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Valeur du stock</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">@if(isset($stockState['_summary'])){{ number_format($stockState['_summary']['total_value'], 0, ',', ' ') }} F @else 0 F @endif</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quantité totale</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">@if(isset($stockState['_summary'])){{ number_format($stockState['_summary']['total_qty'], 2, ',', ' ') }} @else 0 @endif</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Produits</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">@if(isset($stockState['_summary'])){{ $stockState['_summary']['total_products'] }} @else 0 @endif</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-red-500 dark:text-red-400 uppercase">En rupture</p>
            <p class="text-lg font-bold text-red-600 mt-1">{{ count($stockOut) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-amber-500 dark:text-amber-400 uppercase">Stock bas</p>
            <p class="text-lg font-bold text-amber-600 mt-1">{{ count($stockMinReached) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- État du stock -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">État du stock</h3>
            @if(count($stockState) > 0)
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium text-right">Stock</th><th class="pb-2 font-medium text-right">Min</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($stockState as $p)
                                @if(!isset($p['_summary']))
                                    <tr>
                                        <td class="py-1 text-gray-900 dark:text-white">{{ $p['name'] ?? $p['product_name'] ?? 'N/A' }}</td>
                                        <td class="py-1 text-right {{ ($p['stock_quantity'] ?? 0) <= 0 ? 'text-red-600 font-medium' : 'text-gray-700 dark:text-gray-300' }}">{{ number_format($p['stock_quantity'] ?? 0, 2, ',', ' ') }}</td>
                                        <td class="py-1 text-right text-gray-500">{{ number_format($p['min_stock'] ?? 0, 2, ',', ' ') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun produit</p>
            @endif
        </div>

        <!-- Stock par emplacement -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Stock par magasin</h3>
            @if(count($stockByLocation) > 0)
                <div class="space-y-3">
                    @foreach($stockByLocation as $loc)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $loc->store_name ?? 'N/A' }}</span>
                            <span class="font-medium">{{ number_format($loc->total_value ?? 0, 0, ',', ' ') }} F <span class="text-gray-400 text-xs">({{ number_format($loc->total_qty ?? 0, 0, ',', ' ') }} unités)</span></span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stock minimum atteint -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Stock minimum atteint</h3>
            @if(count($stockMinReached) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium text-right">Stock</th><th class="pb-2 font-medium text-right">Min</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($stockMinReached as $p)
                                <tr><td class="py-1 text-gray-900 dark:text-white">{{ $p['name'] }}</td><td class="py-1 text-right text-amber-600 font-medium">{{ number_format($p['stock_quantity'], 2, ',', ' ') }}</td><td class="py-1 text-right text-gray-500">{{ number_format($p['min_stock'], 2, ',', ' ') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun stock bas</p>
            @endif
        </div>

        <!-- Rupture -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Produits en rupture</h3>
            @if(count($stockOut) > 0)
                <ul class="space-y-1.5">
                    @foreach($stockOut as $p)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $p['name'] }}</span>
                            <span class="font-medium text-red-600">0</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune rupture</p>
            @endif
        </div>

        <!-- Stock dormant -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Stock dormant (pas de vente depuis 3 mois)</h3>
            @if(count($stockDormant) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium text-right">Stock</th><th class="pb-2 font-medium text-right">Valeur</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($stockDormant as $p)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $p['name'] }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ number_format($p['stock_quantity'], 2, ',', ' ') }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ number_format(($p['stock_quantity'] ?? 0) * ($p['purchase_price'] ?? 0), 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun stock dormant</p>
            @endif
        </div>
    </div>

    <!-- Expiration -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Produits expirés</h3>
            @if(count($expiredProducts) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium">Lot</th><th class="pb-2 font-medium text-right">Qté restante</th><th class="pb-2 font-medium text-right">Expiré le</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($expiredProducts as $lot)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $lot->product_name ?? 'N/A' }}</td>
                                    <td class="py-1 text-gray-600 dark:text-gray-400">{{ $lot->lot_number ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-red-600 font-medium">{{ number_format($lot->remaining_quantity ?? 0, 2, ',', ' ') }}</td>
                                    <td class="py-1 text-right text-gray-500">{{ $lot->expiry_date ? \Carbon\Carbon::parse($lot->expiry_date)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun produit expiré</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Expiration proche (30 jours)</h3>
            @if(count($expiringProducts) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium">Lot</th><th class="pb-2 font-medium text-right">Qté</th><th class="pb-2 font-medium text-right">Expire le</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($expiringProducts as $lot)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $lot->product_name ?? 'N/A' }}</td>
                                    <td class="py-1 text-gray-600 dark:text-gray-400">{{ $lot->lot_number ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-amber-600 font-medium">{{ number_format($lot->remaining_quantity ?? 0, 2, ',', ' ') }}</td>
                                    <td class="py-1 text-right text-gray-500">{{ $lot->expiry_date ? \Carbon\Carbon::parse($lot->expiry_date)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune expiration proche</p>
            @endif
        </div>
    </div>

    <!-- Mouvements de stock -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Historique des mouvements</h3>
        @if(count($stockMovements) > 0)
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                        <tr><th class="pb-2 font-medium">Date</th><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium">Type</th><th class="pb-2 font-medium">Magasin</th><th class="pb-2 font-medium text-right">Qté</th><th class="pb-2 font-medium">Vendeur</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($stockMovements as $m)
                            <tr>
                                <td class="py-1 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($m['created_at'] ?? $m->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="py-1 text-gray-900 dark:text-white">{{ $m['product']['name'] ?? $m->product->name ?? 'N/A' }}</td>
                                <td class="py-1"><span class="px-2 py-0.5 text-xs rounded-full {{ $m['type'] ?? $m->type === 'sale_out' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $m['type'] ?? $m->type ?? 'N/A' }}</span></td>
                                <td class="py-1 text-gray-600 dark:text-gray-400">{{ $m['store']['name'] ?? $m->store->name ?? 'N/A' }}</td>
                                <td class="py-1 text-right font-medium">{{ number_format($m['quantity'] ?? $m->quantity ?? 0, 2, ',', ' ') }}</td>
                                <td class="py-1 text-gray-600 dark:text-gray-400">{{ $m['user']['first_name'] ?? $m->user->first_name ?? '' }} {{ $m['user']['last_name'] ?? $m->user->last_name ?? $m['user']['name'] ?? $m->user->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Aucun mouvement</p>
        @endif
    </div>

    <!-- Inventaires & Rotation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Inventaires</h3>
            @if(count($inventoryReport) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Réf.</th><th class="pb-2 font-medium">Magasin</th><th class="pb-2 font-medium">Statut</th><th class="pb-2 font-medium text-right">Écarts</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($inventoryReport as $inv)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $inv['reference'] ?? $inv->reference ?? 'N/A' }}</td>
                                    <td class="py-1 text-gray-600 dark:text-gray-400">{{ $inv['store']['name'] ?? $inv->store->name ?? 'N/A' }}</td>
                                    <td class="py-1"><span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">{{ $inv['status'] ?? $inv->status ?? 'N/A' }}</span></td>
                                    <td class="py-1 text-right font-medium {{ ($inv['total_discrepancies'] ?? $inv->total_discrepancies ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $inv['total_discrepancies'] ?? $inv->total_discrepancies ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun inventaire</p>
            @endif
            @if(($inventoryDiscrepancies['total_items'] ?? 0) > 0)
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm">Total des écarts : <span class="font-bold text-red-600">{{ $inventoryDiscrepancies['total_items'] }} articles</span> ({{ number_format($inventoryDiscrepancies['total_value'] ?? 0, 0, ',', ' ') }} F)</p>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Rotation du stock (ventes 3 mois)</h3>
            @if(count($stockRotation) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Produit</th><th class="pb-2 font-medium text-right">Stock</th><th class="pb-2 font-medium text-right">Vendu (3m)</th><th class="pb-2 font-medium text-right">Ratio</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($stockRotation as $r)
                                @php $ratio = (($r['stock_quantity'] ?? 0) > 0 && ($r['sold_qty_3m'] ?? 0) > 0) ? round(($r['sold_qty_3m'] ?? 0) / ($r['stock_quantity'] ?? 1), 1) : 0; @endphp
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $r['name'] ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ number_format($r['stock_quantity'] ?? 0, 2, ',', ' ') }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ number_format($r['sold_qty_3m'] ?? 0, 2, ',', ' ') }}</td>
                                    <td class="py-1 text-right font-medium">{{ $ratio }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>
</div>
