<div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
    <div class="space-y-4">
        {{-- Imagen del producto --}}
        @if($product->image_path)
            <div class="flex justify-center">
                <img
                    src="{{ asset($product->image_path) }}"
                    alt="{{ $product->name }}"
                    class="h-36 w-auto object-contain rounded-lg"
                />
            </div>
        @endif

        {{-- Información básica del producto --}}
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

            @if($product->brand)
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">Marca:</span>
                    <span class="text-gray-800 dark:text-gray-200 bg-blue-100 dark:bg-blue-800 px-2 py-0.5 rounded-full text-xs">
                        {{ $product->brand->name }}
                    </span>
                </div>
            @endif

            @if($product->category)
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">Categoría:</span>
                    <span class="text-gray-800 dark:text-gray-200 bg-green-100 dark:bg-green-800 px-2 py-0.5 rounded-full text-xs">
                        {{ $product->category->name }}
                    </span>
                </div>
            @endif

            <div>
                <span class="font-medium text-gray-600 dark:text-gray-300">Precio:</span>
                <span class="text-gray-800 dark:text-gray-200 font-bold">${{ number_format($product->price, 2) }}</span>
            </div>
        </div>

        {{-- Atributos del producto --}}
        @if($product->attributeValues && $product->attributeValues->count() > 0)
            <div class="mt-3">
                <h4 class="font-medium text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">
                    Atributos
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($product->attributeValues()->with('attribute')->get() as $attributeValue)
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">{{ $attributeValue->attribute->name }}:</span>
                            <span class="text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full text-xs">
                                {{ $attributeValue->value }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Descripción del producto --}}
        @if($product->description)
            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">
                    Descripción
                </h4>
                <div class="text-gray-700 dark:text-gray-300 text-sm mt-1 prose prose-sm max-w-none">
                    {!! $product->description !!}
                </div>
            </div>
        @endif
    </div>
</div>
