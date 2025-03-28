<div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
    <div class="space-y-4">
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

            @if($product->hasValidOffer())
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-300">Oferta:</span>
                    <span class="text-green-600 dark:text-green-400 font-bold">${{ number_format($product->offer_price, 2) }}</span>

                    @if($product->offer_expires_at)
                        <span class="text-xs text-gray-500 ml-2">
                            (Hasta: {{ $product->offer_expires_at->format('d/m/Y') }})
                        </span>
                    @endif
                </div>
            @endif
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

        {{-- Imagen del producto (ahora después de la descripción y clickeable) --}}
        @if($product->first_image)
            <div class="mt-4 border-t border-gray-200 dark:border-gray-600 pt-3">
                <h4 class="font-medium text-gray-700 dark:text-gray-200 pb-2">Imagen</h4>
                <div class="flex justify-center">
                    <a href="{{ asset($product->first_image) }}" target="_blank" title="Ver imagen completa">
                        <img
                            src="{{ asset($product->first_image) }}"
                            alt="{{ $product->name }}"
                            class="max-h-32 w-auto object-contain rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        />
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
