<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Import de données</h1>
        <p class="mt-1 text-sm text-gray-500">
            Importez vos données en masse à partir de fichiers CSV ou Excel.
        </p>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if ($step === 'select')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($entityOptions as $key => $config)
                <button
                    wire:click="selectEntity('{{ $key }}')"
                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center transition hover:border-indigo-400 hover:shadow-md"
                >
                    <div class="mb-2 text-3xl">
                        @switch($key)
                            @case('products') 📦 @break
                            @case('categories') 📂 @break
                            @case('customers') 👥 @break
                            @case('suppliers') 🏭 @break
                            @case('stock') 📊 @break
                            @case('prices') 💰 @break
                            @case('users') 👤 @break
                            @default 📄
                        @endswitch
                    </div>
                    <span class="font-medium text-gray-800">{{ $config['label'] }}</span>
                    <span class="mt-1 text-xs text-gray-400">{{ count($config['fields']) }} champs</span>
                </button>
            @endforeach
        </div>

    @elseif ($step === 'upload')
        <div class="mx-auto max-w-xl">
            <div class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Importer {{ $entityOptions[$entity]['label'] ?? $entity }}
                    </h2>
                    <button wire:click="resetImport" class="text-sm text-gray-500 hover:text-gray-700">← Changer</button>
                </div>

                <div class="mb-4">
                    <a
                        wire:click.prevent="downloadTemplate"
                        wire:target="downloadTemplate"
                        wire:loading.attr="disabled"
                        href="#"
                        class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Télécharger le modèle CSV
                    </a>
                </div>

                <div
                    x-data="{ isUploading: false, progress: 0 }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8"
                >
                    <svg class="mb-3 h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="mb-2 text-sm text-gray-500">
                        <span class="font-medium text-indigo-600">Cliquez</span> ou glissez votre fichier ici
                    </p>
                    <p class="text-xs text-gray-400">CSV ou Excel (max 10 Mo)</p>
                    <input
                        type="file"
                        wire:model="file"
                        accept=".csv,.xls,.xlsx,.txt"
                        class="mt-4 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                    >
                    <div x-show="isUploading" class="mt-3 w-full max-w-xs">
                        <div class="h-2 rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-indigo-600 transition-all" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="mt-1 text-center text-xs text-gray-500" x-text="`${progress}%`"></p>
                    </div>
                </div>

                @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 flex justify-end">
                    <button
                        wire:click="preview"
                        wire:target="preview"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="preview">Aperçu</span>
                        <span wire:loading wire:target="preview">Analyse...</span>
                    </button>
                </div>
            </div>
        </div>

    @elseif ($step === 'preview' && $preview)
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Aperçu — {{ $entityOptions[$entity]['label'] ?? $entity }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ $preview['total'] }} lignes détectées
                        · <span class="text-green-600 font-medium">{{ $preview['valid'] }} valides</span>
                        · <span class="text-red-600 font-medium">{{ $preview['invalid'] }} avec erreurs</span>
                        @if($preview['duplicates'] > 0)
                            · <span class="text-amber-600 font-medium">{{ $preview['duplicates'] }} doublons</span>
                        @endif
                    </p>
                </div>
                <button wire:click="resetImport" class="text-sm text-gray-500 hover:text-gray-700">← Retour</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">#</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Statut</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Détails</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($preview['results'] as $item)
                            <tr class="{{ $item['is_valid'] ? '' : 'bg-red-50' }} {{ $item['is_duplicate'] ? 'bg-amber-50' : '' }}">
                                <td class="px-4 py-2 text-gray-500">{{ $item['row'] }}</td>
                                <td class="px-4 py-2">
                                    @if (!$item['is_valid'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Erreur</span>
                                    @elseif ($item['is_duplicate'])
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Doublon</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Valide</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if ($item['errors'])
                                        <ul class="list-inside list-disc text-red-600">
                                            @foreach ($item['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($item['warnings'])
                                        <ul class="list-inside list-disc text-amber-600">
                                            @foreach ($item['warnings'] as $warning)
                                                <li>{{ $warning }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($item['is_valid'] && !$item['warnings'])
                                        <span class="text-gray-400">Aucun problème</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                <p class="text-sm text-gray-500">
                    {{ $preview['valid'] }} ligne(s) seront importées
                </p>
                <div class="flex gap-3">
                    <button
                        wire:click="resetImport"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >Annuler</button>
                    <button
                        wire:click="confirm"
                        wire:target="confirm"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="confirm">Confirmer l'import</span>
                        <span wire:loading wire:target="confirm">Import en cours...</span>
                    </button>
                </div>
            </div>
        </div>

    @elseif ($step === 'report')
        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-center">
                <div class="mb-4">
                    @if ($report['failed'] === 0)
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @elseif ($report['imported'] > 0)
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    @else
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <h2 class="text-xl font-semibold text-gray-800">Rapport d'import</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $entityOptions[$entity]['label'] ?? $entity }}</p>

                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg bg-gray-50 p-3">
                        <p class="text-2xl font-bold text-gray-800">{{ $report['total'] }}</p>
                        <p class="text-xs text-gray-500">Total lignes</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-3">
                        <p class="text-2xl font-bold text-green-600">{{ $report['imported'] }}</p>
                        <p class="text-xs text-green-600">Importées</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3">
                        <p class="text-2xl font-bold text-red-600">{{ $report['failed'] }}</p>
                        <p class="text-xs text-red-600">Échouées</p>
                    </div>
                </div>

                @if ($report['log'])
                    <div class="mt-6 text-left">
                        <h3 class="mb-2 text-sm font-medium text-gray-700">Détail :</h3>
                        <div class="max-h-48 overflow-y-auto rounded-lg bg-gray-50 p-3 text-xs text-gray-600">
                            @foreach ($report['log'] as $entry)
                                <div class="py-0.5">{{ $entry }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6">
                    <button
                        wire:click="resetImport"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >Nouvel import</button>
                </div>
            </div>
        </div>
    @endif
</div>
