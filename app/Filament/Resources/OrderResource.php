<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // Personalizar icono de navegación
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    // Definir rótulo de navegación personalizado
    protected static ?string $navigationLabel = 'Órdenes';

    // Definir posición en la navegación
    protected static ?int $navigationSort = 3;

    // Definir grupo de navegación
    public static function getNavigationGroup(): ?string
    {
        return 'Ventas';
    }

    // Personalizar etiqueta del modelo
    public static function getModelLabel(): string
    {
        return 'Orden';
    }

    // Personalizar etiqueta plural del modelo
    public static function getPluralModelLabel(): string
    {
        return 'Órdenes';
    }

    // Personalizar rótulo de registro para identificación
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        // Sección principal (2/3 del ancho)
                        Forms\Components\Section::make('Información de la orden')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Cliente')
                                    ->relationship('user', 'name')
                                    ->searchable(['name', 'email'])
                                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                                    ->preload()
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(),
                                        Forms\Components\TextInput::make('password')
                                            ->label('Contraseña')
                                            ->password()
                                            ->required()
                                            ->minLength(8),
                                    ])
                                    ->createOptionAction(
                                        fn (Forms\Components\Actions\Action $action) => $action
                                            ->modalHeading('Crear nuevo cliente')
                                            ->modalWidth('md')
                                    ),

                                Forms\Components\TextInput::make('total')
                                    ->label('Total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Placeholder::make('items_count')
                                    ->label('Productos')
                                    ->content(function ($record) {
                                        if (!$record || !$record->exists) return 'Sin productos';
                                        return $record->orderDetails()->count() . ' productos';
                                    }),

                                Forms\Components\Placeholder::make('payments_status')
                                    ->label('Estado de pagos')
                                    ->content(function ($record) {
                                        if (!$record || !$record->exists) return 'Sin pagos';

                                        $paidAmount = $record->payments()
                                            ->where('status', PaymentStatusEnum::PAID->value)
                                            ->sum('amount');

                                        return "Pagado: $" . number_format($paidAmount, 2) .
                                               " de $" . number_format($record->total, 2) .
                                               " (" . round(($paidAmount / max(1, $record->total)) * 100) . "%)";
                                    }),
                            ])
                            ->columnSpan(['lg' => 2]),

                        // Sección lateral (1/3 del ancho)
                        Forms\Components\Section::make('Estado e información')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado de la orden')
                                    ->options(OrderStatusEnum::class)
                                    ->default(OrderStatusEnum::PENDING)
                                    ->required(),

                                Forms\Components\Select::make('payment_status')
                                    ->label('Estado del pago')
                                    ->options(PaymentStatusEnum::class)
                                    ->default(PaymentStatusEnum::PENDING)
                                    ->required(),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Creada')
                                    ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Última actualización')
                                    ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Estado de pago')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado de orden')
                    ->options(OrderStatusEnum::class),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Estado de pago')
                    ->options(PaymentStatusEnum::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear orden'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderDetailsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    // Personalizar consulta Eloquent (útil para filtros globales)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }
}
