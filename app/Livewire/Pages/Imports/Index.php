<?php

namespace App\Livewire\Pages\Imports;

use App\Services\ImportService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Sessionable;
use Livewire\Attributes\Title;

#[Title('Import de données')]
class Index extends Component
{
    use WithFileUploads;

    public string $step = 'select';
    public string $entity = '';
    public $file = null;
    public array $entityOptions = [];
    public array $preview = [];
    public array $report = [];
    public ?array $validationResult = null;

    #[Sessionable(key: 'import_entity')]
    public string $sessionEntity = '';

    public function boot(ImportService $importService)
    {
        $this->entityOptions = $importService->supportedEntities;
    }

    public function selectEntity(string $entity)
    {
        $this->entity = $entity;
        $this->sessionEntity = $entity;
        $this->step = 'upload';
        $this->file = null;
        $this->preview = [];
        $this->validationResult = null;
        $this->report = [];
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => 'file|mimes:csv,xls,xlsx,txt|max:10240',
        ]);
    }

    public function preview(ImportService $importService)
    {
        $this->validate([
            'file' => 'file|mimes:csv,xls,xlsx,txt|max:10240',
        ]);

        $path = $this->file->getRealPath();
        $rows = $importService->parseFile($path, $this->entity);
        $companyId = auth()->user()->company_id;

        $this->validationResult = $importService->validate($rows, $this->entity, $companyId);
        $this->preview = $this->validationResult;
        $this->step = 'preview';
    }

    public function confirm(ImportService $importService)
    {
        if (!$this->validationResult) {
            session()->flash('error', 'Aucune donnée à importer.');
            return;
        }

        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        $this->report = $importService->import($this->validationResult, $this->entity, $companyId, $userId);
        $this->step = 'report';
        $this->file = null;
        $this->validationResult = null;
        $this->preview = [];
    }

    public function resetImport()
    {
        $this->step = 'select';
        $this->entity = '';
        $this->sessionEntity = '';
        $this->file = null;
        $this->preview = [];
        $this->validationResult = null;
        $this->report = [];
    }

    public function downloadTemplate(ImportService $importService)
    {
        return response()->streamDownload(
            fn() => print($importService->generateTemplateCsv($this->entity)),
            "modele_{$this->entity}.csv",
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function render()
    {
        return view('livewire.pages.imports.index')
            ->layout('components.layouts.app');
    }
}
