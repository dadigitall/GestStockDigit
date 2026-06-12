<?php

namespace App\Livewire\Pages\Alerts;

use App\Models\Alert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public ?string $typeFilter = null;

    public ?string $severity = null;

    public function markAsRead(int $id): void
    {
        $alert = Alert::forUser(auth()->id(), auth()->user()->company_id)->findOrFail($id);
        $alert->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Alert::forUser(auth()->id(), auth()->user()->company_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete(int $id): void
    {
        Alert::forUser(auth()->id(), auth()->user()->company_id)
            ->findOrFail($id)
            ->delete();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        $query = Alert::forUser($userId, $companyId);

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->severity) {
            $query->where('severity', $this->severity);
        }

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        $alerts = $query->orderByDesc('created_at')->paginate(20);

        $unreadCount = Alert::forUser($userId, $companyId)->whereNull('read_at')->count();

        $typeCounts = Alert::forUser($userId, $companyId)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return view('livewire.pages.alerts.index', [
            'alerts' => $alerts,
            'unreadCount' => $unreadCount,
            'typeCounts' => $typeCounts,
        ])->layout('layouts.app', ['header' => 'Notifications et alertes']);
    }
}
