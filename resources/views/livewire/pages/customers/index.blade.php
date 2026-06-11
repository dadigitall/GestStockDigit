<div>
    <div class="flex justify-end mb-6">
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Nouveau client</button>
    </div>
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border mb-6">
            <h3 class="text-lg font-semibold mb-4">{{ $editingCustomer ? 'Modifier' : 'Nouveau' }} client</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium mb-1">Nom *</label><input wire:model="name" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800"></div>
                <div><label class="block text-sm font-medium mb-1">Téléphone</label><input wire:model="phone" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800"></div>
                <div><label class="block text-sm font-medium mb-1">Email</label><input wire:model="email" type="email" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800"></div>
                <div class="md:col-span-3"><label class="block text-sm font-medium mb-1">Adresse</label><input wire:model="address" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800"></div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border rounded-lg">Annuler</button>
                </div>
            </form>
        </div>
    @endif
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 dark:bg-gray-700/50 border-b"><th class="px-6 py-3 text-left font-medium">Nom</th><th class="px-6 py-3 text-left font-medium">Contact</th><th class="px-6 py-3 text-left font-medium">Type</th><th class="px-6 py-3 text-right font-medium">Actions</th></tr></thead>
            <tbody class="divide-y">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4 font-medium">{{ $customer->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $customer->phone ?? $customer->email ?? '-' }}</td>
                        <td class="px-6 py-4 capitalize">{{ $customer->type }}</td>
                        <td class="px-6 py-4 text-right"><button wire:click="edit({{ $customer->id }})" class="text-indigo-600 hover:text-indigo-800">Modifier</button></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Aucun client</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $customers->links() }}</div>
    </div>
</div>
