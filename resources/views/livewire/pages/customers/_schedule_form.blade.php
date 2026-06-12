<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="closeScheduleForm">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-md m-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Planifier une échéance</h3>
        <form wire:submit="saveSchedule" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant *</label>
                    <input wire:model="schedule_amount" type="number" step="0.01" min="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('schedule_amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date d'échéance *</label>
                    <input wire:model="schedule_due_date" type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('schedule_due_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vente associée *</label>
                <select wire:model="schedule_sale_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Sélectionner une vente...</option>
                    @foreach($detailCustomer?->sales ?? [] as $s)
                        @if($s->paid_amount < $s->total)
                            <option value="{{ $s->id }}">{{ $s->reference }} — {{ number_format(max(0, $s->total - $s->paid_amount), 0, ',', ' ') }} F</option>
                        @endif
                    @endforeach
                </select>
                @error('schedule_sale_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="schedule_notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Créer l'échéance</button>
                <button type="button" wire:click="closeScheduleForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
            </div>
        </form>
    </div>
</div>
