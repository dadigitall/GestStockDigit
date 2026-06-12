<?php

namespace App\Livewire\Pages\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $name = '';

    public $description = '';

    public $color = '#6366f1';

    public $parent_id = '';

    public $margin_rate = null;

    public $min_margin = null;

    public $stock_threshold = null;

    public $editingCategory = null;

    public $showForm = false;

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $categories = Category::where('company_id', $companyId)
            ->with('parent')
            ->orderBy('name')
            ->paginate(20);

        $parentCategories = Category::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->where('id', '!=', $this->editingCategory?->id)
            ->get();

        return view('livewire.pages.categories.index', compact('categories', 'parentCategories'))
            ->layout('layouts.app', ['header' => 'Catégories']);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Category $category)
    {
        $this->editingCategory = $category;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->color = $category->color ?? '#6366f1';
        $this->parent_id = $category->parent_id;
        $this->margin_rate = $category->margin_rate;
        $this->min_margin = $category->min_margin;
        $this->stock_threshold = $category->stock_threshold;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:categories,id',
            'margin_rate' => 'nullable|numeric|min:0|max:100',
            'min_margin' => 'nullable|numeric|min:0|max:100',
            'stock_threshold' => 'nullable|integer|min:0',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'parent_id' => $this->parent_id ?: null,
            'margin_rate' => $this->margin_rate ?: null,
            'min_margin' => $this->min_margin ?: null,
            'stock_threshold' => $this->stock_threshold ?: null,
        ];

        if ($this->editingCategory) {
            $this->editingCategory->update($data);
        } else {
            $data['slug'] = Str::slug($this->name);
            Category::create($data);
        }

        $this->resetForm();
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#6366f1';
        $this->parent_id = '';
        $this->margin_rate = null;
        $this->min_margin = null;
        $this->stock_threshold = null;
        $this->editingCategory = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
