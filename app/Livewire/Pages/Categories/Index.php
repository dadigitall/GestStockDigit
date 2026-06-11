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
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'parent_id' => $this->parent_id ?: null,
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
        $this->editingCategory = null;
        $this->showForm = false;
    }

    public function cancel()
    {
        $this->resetForm();
    }
}
