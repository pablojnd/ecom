<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Exports\ProductExporter;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

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

    /**
     * Obtiene el esquema del formulario básico del producto
     */
    public static function getBasicFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->label('URL amigable')
                        ->readOnly(),

                    Forms\Components\Select::make('brand_id')
                        ->label('Marca')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required(),
                        ])
                        ->required(),

                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required(),
                            Forms\Components\Select::make('parent_id')
                                ->label('Categoría padre')
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Ninguna (categoría principal)'),
                        ])
                        ->required(),
                ]),

            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Precio')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->step(0.01),

                    Forms\Components\DateTimePicker::make('offer_expires_at')
                        ->label('Fecha de vencimiento de oferta')
                        ->helperText('Fecha en que expira el precio de oferta'),

                    Forms\Components\TextInput::make('offer_price')
                        ->label('Precio de oferta')
                        ->numeric()
                        ->prefix('$')
                        ->step(0.01),
                ]),
        ];
    }

    /**
     * Obtiene el esquema del formulario para la descripción
     */
    public static function getDescriptionFormSchema(): array
    {
        return [
            Forms\Components\RichEditor::make('description')
                ->label('Descripción')
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
        ];
    }

    /**
     * Obtiene el esquema del formulario para las imágenes
     */
    public static function getImagesFormSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('image_path')
                ->label('Imágenes del producto')
                ->multiple()
                ->image()
                ->maxFiles(5)
                ->directory(function ($record) {
                    if ($record) {
                        return $record->getImagesDirectory();
                    }
                    return 'products/temp-' . Str::random(10);
                })
                ->afterStateUpdated(function ($state, $set, callable $get, ?Model $record, Forms\Components\FileUpload $component) {
                    if (!$record && $state && !empty($state) && $get('name')) {
                        $slug = Str::slug($get('name'));
                        $newDirectory = "products/{$slug}";

                        $newState = [];
                        foreach ($state as $path) {
                            $filename = basename($path);
                            $newPath = "{$newDirectory}/{$filename}";

                            if ($path !== $newPath) {
                                if (!file_exists(public_path($newDirectory))) {
                                    mkdir(public_path($newDirectory), 0755, true);
                                }

                                if (file_exists(public_path($path))) {
                                    rename(
                                        public_path($path),
                                        public_path($newPath)
                                    );

                                    $newState[] = $newPath;
                                } else {
                                    $newState[] = $path;
                                }
                            } else {
                                $newState[] = $path;
                            }
                        }

                        if (!empty($newState)) {
                            $set('image_path', $newState);
                        }
                    }
                })
                ->imagePreviewHeight('100')
                ->loadingIndicatorPosition('left')
                ->panelAspectRatio('4:3')
                ->imageEditor()
                ->openable()
                ->downloadable()
                ->reorderable()
                ->removeUploadedFileButtonPosition('right')
                ->uploadButtonPosition('left')
                ->uploadProgressIndicatorPosition('left')
                ->hint('Recomendado: 800x800px, máximo 5 imágenes')
                ->panelLayout('grid')
                ->columnSpanFull(),
        ];
    }

    /**
     * Obtiene el esquema del formulario para los atributos
     */
    public static function getAttributesFormSchema(): array
    {
        return [
            Forms\Components\Repeater::make('product_attributes')
                ->label('Valores de atributos')
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
                        ->live(onBlur: true)
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
                ),
        ];
    }

    /**
     * Obtiene el esquema del formulario para la información de estado
     */
    public static function getStatusFormSchema(): array
    {
        return [
            Forms\Components\Toggle::make('is_active')
                ->label('Producto activo')
                ->default(true)
                ->helperText('¿Este producto está disponible para la venta?'),
        ];
    }

    /**
     * Obtiene el esquema del formulario para la información de inventario
     */
    public static function getInventoryFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->readOnly(),

            Forms\Components\TextInput::make('stock_quantity')
                ->label('Existencias')
                ->numeric()
                ->minValue(0)
                ->default(0),
        ];
    }


    /**
     * Obtiene el esquema del formulario para la información del sistema
     */
    public static function getSystemInfoFormSchema(): array
    {
        return [
            Forms\Components\Placeholder::make('created_at')
                ->label('Creado')
                ->content(fn (Product $record): string => $record->created_at ? $record->created_at->format('d/m/Y H:i') : 'Sin registrar'),

            Forms\Components\Placeholder::make('updated_at')
                ->label('Última actualización')
                ->content(fn (Product $record): string => $record->updated_at ? $record->updated_at->format('d/m/Y H:i') : 'Sin registrar'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // Sección principal (2/3)
                        Forms\Components\Section::make('Información del producto')
                            ->schema([
                                ...self::getBasicFormSchema(),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs::make('Detalles del producto')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Descripción')
                                    ->schema(self::getDescriptionFormSchema()),

                                Forms\Components\Tabs\Tab::make('Imágenes')
                                    ->schema(self::getImagesFormSchema()),

                                Forms\Components\Tabs\Tab::make('Atributos')
                                    ->schema([
                                        Forms\Components\Section::make('Atributos del producto')
                                            ->schema(self::getAttributesFormSchema())
                                            ->collapsible(),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Sección lateral (1/3)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Estado y Ofertas')
                            ->schema(self::getStatusFormSchema()),

                        Forms\Components\Section::make('Inventario')
                            ->schema(self::getInventoryFormSchema()),

                        Forms\Components\Section::make('Información del Sistema')
                            ->schema(self::getSystemInfoFormSchema())
                            ->hidden(fn (?Product $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
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
                Tables\Columns\TextColumn::make('offer_price')
                    ->label('Oferta')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
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
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
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
