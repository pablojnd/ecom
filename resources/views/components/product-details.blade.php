<div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
    <div class="space-y-2">
        @if($product->image)
            <div class="flex justify-center">
                <img
                    src="{{ asset($product->image) }}"
                    alt="{{ $product->name }}"
                    class="h-36 w-auto object-contain rounded-lg"
                />
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2">
            @if($product->sku)
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">SKU:</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $product->sku }}</span>
                </div>
            @endif

            @if($product->stock_quantity !== null)
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">Existencias:</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $product->stock_quantity }}</span>
                </div>
            @endif

            @if($product->category)
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">Categoría:</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $product->category->name }}</span>
                </div>
            @endif
        </div>

        @if($product->description)
            <div>
                <span class="font-medium text-gray-600 dark:text-gray-300">Descripción:</span>
                <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">{{ $product->description }}</p>
            </div>
        @endif
    </div>
</div>
