<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderDetails';

    // Definir el atributo del título del registro para identificación
    protected static ?string $recordTitleAttribute = 'product.name';

    // Personalizar título del RelationManager
    protected static ?string $title = 'Detalles de la orden';

    // Método para permitir la traducción del título
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Detalles de la orden';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del producto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->label('Producto')
                                    ->required()
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (!$state) return;

                                        $product = \App\Models\Product::find($state);
                                        if (!$product) return;

                                        // Usar el método del modelo para obtener el precio efectivo
                                        $price = $product->getEffectivePrice();
                                        $set('price', $price);

                                        // Actualizar stock disponible
                                        $set('available_stock', $product->stock_quantity);

                                        // Actualizar subtotal
                                        $quantity = $get('quantity') ?: 1;
                                        $set('subtotal', $price * $quantity);
                                    }),

                                Forms\Components\Placeholder::make('available_stock')
                                    ->label('Stock disponible')
                                    ->content(function ($get) {
                                        $stock = $get('available_stock');
                                        if ($stock === null) return 'Seleccione un producto';

                                        $colorClass = $stock > 10 ? 'text-success-500' : ($stock > 0 ? 'text-warning-500' : 'text-danger-500');

                                        return view('components.stock-indicator', [
                                            'stock' => $stock,
                                            'colorClass' => $colorClass
                                        ]);
                                    })
                                    ->hidden(fn ($get) => !$get('product_id')),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($get('price')) {
                                            $subtotal = $state * $get('price');
                                            $set('subtotal', $subtotal);
                                        }
                                    })
                                    ->hint(function ($get) {
                                        $stock = $get('available_stock');
                                        if ($stock === null || $stock === '') return null;

                                        $quantity = $get('quantity') ?: 0;
                                        if ($quantity > $stock) {
                                            return "Advertencia: La cantidad excede el stock disponible.";
                                        }
                                        return null;
                                    })
                                    ->hintColor('danger'),

                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('0.00')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($get('quantity')) {
                                            $subtotal = $state * $get('quantity');
                                            $set('subtotal', $subtotal);
                                        }
                                    })
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('reloadPrice')
                                            ->icon('heroicon-m-arrow-path')
                                            ->tooltip('Restablecer precio original')
                                            ->action(function ($get, $set) {
                                                $productId = $get('product_id');
                                                if (!$productId) return;

                                                $product = \App\Models\Product::find($productId);
                                                if (!$product) return;

                                                // Usar el método del modelo
                                                $price = $product->getEffectivePrice();
                                                $set('price', $price);

                                                // Actualizar subtotal
                                                $quantity = $get('quantity') ?: 1;
                                                $set('subtotal', $price * $quantity);
                                            })
                                    ),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->reactive(),

                                // Campo oculto para mantener temporalmente el stock disponible
                                Forms\Components\Hidden::make('available_stock'),

                                Forms\Components\Placeholder::make('product_details')
                                    ->label('Detalles adicionales')
                                    ->content(function ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return 'Seleccione un producto para ver detalles';

                                        $product = \App\Models\Product::find($productId);
                                        if (!$product) return 'Producto no encontrado';

                                        return view('components.product-details', [
                                            'product' => $product
                                        ]);
                                    })
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_path')
                    ->label('')
                    ->size(40)
                    ->square(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Model $record): string => $record->product->sku ?? ''),

                Tables\Columns\TextColumn::make('product.stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string =>
                        $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->summarize([
                        Sum::make()->label('Bultos'),
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio unitario')
                    ->money('USD')
                    ->sortable()
                    ->description(function (Model $record): ?string {
                        // Mostrar precio normal cuando el precio usado es oferta
                        $product = $record->product;
                        if (!$product) return null;

                        if ($product->hasValidOffer() && abs($record->price - $product->offer_price) < 0.01) {
                            return "Normal: $" . number_format($product->price, 2);
                        }

                        return null;
                    }),

                Tables\Columns\IconColumn::make('has_offer')
                    ->label('Oferta')
                    ->getStateUsing(function (Model $record): bool {
                        $product = $record->product;
                        if (!$product) return false;

                        return $product->hasValidOffer();
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->falseIcon('')
                    ->trueColor('warning'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('USD')
                    ->summarize([
                        Sum::make()->money()->label('Precio Total'),
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Agregado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar producto')
                    ->modalHeading('Agregar producto a la orden')
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->modalIcon('heroicon-o-shopping-bag')
                    ->slideOver()
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->mutateFormDataUsing(function (array $data) {
                        // Asegurar que el subtotal esté correcto
                        if (!isset($data['subtotal']) || !$data['subtotal']) {
                            $data['subtotal'] = $data['price'] * $data['quantity'];
                        }

                        return $data;
                    })
                    ->after(function () {
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->modalHeading('Editar detalle')
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->modalIcon('heroicon-o-pencil')
                    ->slideOver()
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->after(function () {
                        $this->getOwnerRecord()->recalculateTotal();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar detalle')
                    ->modalIcon('heroicon-o-trash')
                    ->modalIconColor('danger')
                    ->after(function () {
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar detalles seleccionados')
                        ->modalIcon('heroicon-o-trash')
                        ->modalIconColor('danger')
                        ->after(function () {
                            $this->getOwnerRecord()->recalculateTotal();
                        }),
                ]),
            ])
            ->emptyStateHeading('No hay productos en esta orden')
            ->emptyStateDescription('Agrega productos a esta orden usando el botón de arriba.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
