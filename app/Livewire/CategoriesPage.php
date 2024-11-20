<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Category Page')]

class CategoriesPage extends Component
{
    public function render()
    {

        $categories = Category::where('is_active', true)->get();
        return view('livewire.categories-page', [
            'categories' => $categories,
        ]);
    }
}
