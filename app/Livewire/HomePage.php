<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Home Page')]

class HomePage extends Component
{
    public function render()
    {
        $brands = Brand::where('is_Active', true)->get();
        $categories = Category::where('is_Active', true)->get();
        // dd($brands);
        return view('livewire.home-page', [
            'brands' => $brands,
            'categories' => $categories
        ]);
    }
}
