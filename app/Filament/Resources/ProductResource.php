<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Productos';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel  = 'Productos';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Producto')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información básica')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('brand_id')
                                            ->relationship('brand', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required(),
                                                Forms\Components\TextInput::make('slug')
                                                    ->required(),
                                            ])
                                            ->required(),
                                        Forms\Components\Select::make('category_id')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->unique(ignorable: fn ($record) => $record)
                                            ->required(),
                                        Forms\Components\TextInput::make('stock_quantity')
                                            ->label('Existencias')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                    ]),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ]),
                        Forms\Components\Tabs\Tab::make('Descripción')
                            ->schema([
                                Forms\Components\RichEditor::make('description')
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'heading',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Imagen')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->image()
                                    ->imageEditor()
                                    ->imageCropAspectRatio('1:1')
                                    ->directory('products')
                                    ->hint('Recomendado: 800x800px')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Atributos')
                            ->schema([
                                Forms\Components\Section::make('Atributos del producto')
                                    ->description('Asigna atributos como color, tamaño, etc. a este producto')
                                    ->schema([
                                        Forms\Components\Repeater::make('product_attributes')
                                            ->label('')
                                            ->relationship('attributes')
                                            ->schema([
                                                Forms\Components\Select::make('attribute_id')
                                                    ->label('Atributo')
                                                    ->options(function () {
                                                        return \App\Models\Attribute::pluck('name', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(fn ($state, $set) => $set('attribute_value_id', null)),

                                                Forms\Components\Select::make('attribute_value_id')
                                                    ->label('Valor')
                                                    ->options(function (callable $get) {
                                                        $attributeId = $get('attribute_id');
                                                        if (!$attributeId) return [];

                                                        return \App\Models\Attributevalue::where('attribute_id', $attributeId)
                                                            ->pluck('value', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('value')
                                                            ->label('Valor')
                                                            ->required(),
                                                    ])
                                                    ->createOptionAction(function (Forms\Components\Actions\Action $action, callable $get) {
                                                        $attributeId = $get('attribute_id');
                                                        if (!$attributeId) {
                                                            return $action->hidden();
                                                        }

                                                        return $action
                                                            ->modalHeading('Crear nuevo valor')
                                                            ->modalWidth('md')
                                                            ->modalSubmitActionLabel('Crear y seleccionar')
                                                            ->mutateFormDataUsing(function (array $data) use ($attributeId) {
                                                                $data['attribute_id'] = $attributeId;
                                                                return $data;
                                                            });
                                                    }),
                                            ])
                                            ->itemLabel(function (array $state): ?string {
                                                $attributeName = \App\Models\Attribute::find($state['attribute_id'] ?? null)?->name;
                                                $valueName = \App\Models\Attributevalue::find($state['attribute_value_id'] ?? null)?->value;

                                                if ($attributeName && $valueName) {
                                                    return "{$attributeName}: {$valueName}";
                                                }

                                                return 'Nuevo atributo';
                                            })
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Agregar atributo')
                                            ->reorderable()
                                            ->collapsible()
                                            ->collapseAllAction(
                                                fn (Forms\Components\Actions\Action $action) => $action->label('Colapsar todos'),
                                            )
                                            ->deleteAction(
                                                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                            )
                                    ])
                                    ->collapsible()
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('attributes_summary')
                    ->label('Atributos')
                    ->getStateUsing(function (Product $record): string {
                        $attributesWithValues = $record->attributeValues()
                            ->with('attribute')
                            ->get()
                            ->map(function ($attributeValue) {
                                return $attributeValue->attribute->name . ': ' . $attributeValue->value;
                            })
                            ->take(3) // Mostrar solo los primeros 3 atributos
                            ->join(', ');

                        $totalAttributes = $record->attributeValues()->count();

                        if ($totalAttributes > 3) {
                            $attributesWithValues .= ' (+' . ($totalAttributes - 3) . ' más)';
                        }

                        return $attributesWithValues ?: 'Sin atributos';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('attributeValues', function (Builder $query) use ($search) {
                            $query->where('value', 'like', "%{$search}%")
                                  ->orWhereHas('attribute', function (Builder $query) use ($search) {
                                      $query->where('name', 'like', "%{$search}%");
                                  });
                        });
                    })
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Marca')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_from')
                                    ->label('Precio')
                                    ->numeric()
                                    ->placeholder('Desde')
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('price_to')
                                    ->label('Precio')
                                    ->numeric()
                                    ->placeholder('Hasta')
                                    ->prefix('$'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ProductExporter::class)
                    ->label('Exportar productos')
                    ->icon('heroicon-o-arrow-down-tray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ProductExporter::class)
                        ->label('Exportar seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\AttributesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
