<?php

namespace App\Livewire\Pages\DocumentTemplates;

use App\Models\DocumentTemplate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $showForm = false;

    public $editId = null;

    public $name = '';

    public $type = 'quotation';

    public $paperFormat = 'A4';

    public $isDefault = false;

    public $colors = [];

    public $headerHtml = '';

    public $footerHtml = '';

    public $legalMentions = '';

    public $terms = '';

    public $logo = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:quotation,invoice,delivery_note,customer_order',
            'paperFormat' => 'required|in:A4,ticket',
        ];
    }

    public function mount() {}

    public function resetForm()
    {
        $this->showForm = false;
        $this->editId = null;
        $this->name = '';
        $this->type = 'quotation';
        $this->paperFormat = 'A4';
        $this->isDefault = false;
        $this->colors = [];
        $this->headerHtml = '';
        $this->footerHtml = '';
        $this->legalMentions = '';
        $this->terms = '';
        $this->logo = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $this->editId = $template->id;
        $this->name = $template->name;
        $this->type = $template->type;
        $this->paperFormat = $template->paper_format;
        $this->isDefault = $template->is_default;
        $this->colors = $template->colors ?? [];
        $this->headerHtml = $template->header_html;
        $this->footerHtml = $template->footer_html;
        $this->legalMentions = $template->legal_mentions;
        $this->terms = $template->terms;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $companyId = auth()->user()->company_id;

        if ($this->isDefault) {
            DocumentTemplate::where('company_id', $companyId)
                ->where('type', $this->type)
                ->update(['is_default' => false]);
        }

        $data = [
            'company_id' => $companyId,
            'name' => $this->name,
            'type' => $this->type,
            'paper_format' => $this->paperFormat,
            'is_default' => $this->isDefault,
            'colors' => $this->colors,
            'header_html' => $this->headerHtml,
            'footer_html' => $this->footerHtml,
            'legal_mentions' => $this->legalMentions,
            'terms' => $this->terms,
        ];

        if ($this->editId) {
            $template = DocumentTemplate::findOrFail($this->editId);
            $template->update($data);
        } else {
            DocumentTemplate::create($data);
        }

        $this->resetForm();
    }

    public function delete($id)
    {
        DocumentTemplate::findOrFail($id)->delete();
    }

    public function setDefault($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $companyId = auth()->user()->company_id;

        DocumentTemplate::where('company_id', $companyId)
            ->where('type', $template->type)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true]);
    }

    public function updatedLogo()
    {
        $this->validate([
            'logo' => 'image|max:2048',
        ]);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $templates = DocumentTemplate::where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.pages.document-templates.index', compact('templates'))
            ->layout('layouts.app', ['header' => 'Modèles de documents']);
    }
}
