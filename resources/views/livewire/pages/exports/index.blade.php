<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Export de données</h1>
        <p class="mt-1 text-sm text-gray-500">
            Exportez vos données aux formats CSV ou Excel.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($exportTypes as $key => $type)
            <div class="rounded-xl border border-gray-200 bg-white p-5 transition hover:shadow-md">
                <div class="mb-3 flex items-center gap-3">
                    <span class="text-2xl">{{ $type['icon'] }}</span>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $type['label'] }}</h3>
                        <p class="text-xs text-gray-400">{{ $type['description'] }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($key === 'reports')
                        <a
                            href="{{ route('reports.index') }}"
                            class="inline-flex items-center rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            <span class="mr-1">📈</span> Voir les rapports
                        </a>
                    @else
                        <a
                            href="{{ route('exports.download', ['type' => $key, 'format' => 'csv']) }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <span class="mr-1">📄</span> CSV
                        </a>
                        <a
                            href="{{ route('exports.download', ['type' => $key, 'format' => 'xls']) }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <span class="mr-1">📊</span> Excel
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
