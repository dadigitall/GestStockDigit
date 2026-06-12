<?php

namespace App\Livewire\Pages\AuditLogs;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title("Journal d'audit")]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'action')]
    public string $filterAction = '';

    #[Url(as: 'module')]
    public string $filterModule = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'date_from')]
    public string $dateFrom = '';

    #[Url(as: 'date_to')]
    public string $dateTo = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterAction()
    {
        $this->resetPage();
    }

    public function updatingFilterModule()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function render()
    {
        $query = AuditLog::forCompany(auth()->user()->company_id);

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterModule) {
            $query->where('module', $this->filterModule);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(30);

        return view('livewire.pages.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::actionsList(),
            'modules' => AuditLog::modulesList(),
        ])->layout('components.layouts.app');
    }
}
