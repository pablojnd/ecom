<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title('Product Page')]
class ProductPage extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Url]
    public $select_categories = [];

    #[Url]
    public $select_brands = [];

    #[Url]
    public $select_feature = [];

    #[Url]
    public $select_onsale = [];

    #[Url]
    public $sort = 'latest';

    // Constante para definir la cantidad de productos por página
    private const PAGINATION_COUNT = 6;

    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Product Add', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        // Cargar los productos aplicando filtros
        $products = $this->getFilteredProducts();

        // Cargar las marcas y categorías activas
        return view('livewire.product-page', [
            'products' => $this->getFilteredProducts(),
            'brands' => $this->getActiveBrands(5), // Cambia el número para limitar el total mostrado
            'categories' => $this->getActiveCategories(5),
        ]);
    }

    /**
     * Obtener productos filtrados según las categorías y marcas seleccionadas.
     */
    private function getFilteredProducts()
    {
        $productQuery = Product::query()->where('is_active', true);

        // Aplicar filtro de categorías
        if ($this->isValidArray($this->select_categories)) {
            $productQuery->whereIn('category_id', $this->select_categories);
        }

        // Aplicar filtro de marcas
        if ($this->isValidArray($this->select_brands)) {
            $productQuery->whereIn('brand_id', $this->select_brands);
        }

        if ($this->select_feature) {
            $productQuery->where('is_featured', true);
        }

        if ($this->select_onsale) {
            $productQuery->where('on_sale', true);
        }

        if ($this->sort == 'latest') {
            $productQuery->latest();
        }

        if ($this->sort == 'price') {
            $productQuery->orderBy('price');
        }

        return $productQuery->paginate(self::PAGINATION_COUNT);
    }

    /**
     * Obtener marcas activas.
     */
    private function getActiveBrands($limit = 5)
    {
        return Brand::where('is_active', true)->limit($limit)->get(['id', 'brand_name', 'slug']);
    }

    /**
     * Obtener categorías activas.
     */
    private function getActiveCategories($limit = 5)
    {
        return Category::where('is_active', true)->limit($limit)->get(['id', 'category_name', 'slug']);
    }

    /**
     * Validar que el valor sea un array no vacío.
     */
    private function isValidArray($value)
    {
        return is_array($value) && !empty($value);
    }
}
