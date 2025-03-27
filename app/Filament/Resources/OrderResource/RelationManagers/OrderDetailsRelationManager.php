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
                                        // Obtener el precio del producto si está disponible
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product && $product->price) {
                                                $set('price', $product->price);

                                                // Actualizar también el subtotal
                                                $quantity = $get('quantity') ?: 1;
                                                $set('subtotal', $product->price * $quantity);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Calcular subtotal al cambiar cantidad si hay precio
                                        if ($get('price')) {
                                            $subtotal = $state * $get('price');
                                            $set('subtotal', $subtotal);
                                        }
                                    }),

                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('0.00')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Calcular subtotal al cambiar precio si hay cantidad
                                        if ($get('quantity')) {
                                            $subtotal = $state * $get('quantity');
                                            $set('subtotal', $subtotal);
                                        }
                                    }),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

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
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->summarize([
                        Sum::make()->label('Bultos'),
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio unitario')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    // ->getStateUsing(function ($record) {
                    //     return $record->quantity * $record->price;
                    // })
                    ->summarize([
                        Sum::make()->label('Precio Total'),
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
