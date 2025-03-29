<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderDetail;
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
                                    )
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('total')
                                    ->label('Total')
                                    ->content(function ($record) {
                                        if (!$record || !$record->exists) return '$0.00';
                                        return '$' . number_format($record->orderDetails()->sum('subtotal'), 2);
                                    }),

                                Forms\Components\Placeholder::make('items_count')
                                    ->label('Productos')
                                    ->content(function ($record) {
                                        if (!$record || !$record->exists) return 'Sin productos';
                                        return $record->orderDetails()->count() . ' productos';
                                    }),

                                Forms\Components\Placeholder::make('payments_status')
                                    ->label('Estado de pagos')
                                    ->content(function (Order $record) {
                                        if (!$record->exists) return 'Sin pagos';

                                        $paidAmount = $record->payments()
                                            ->where('payment_status', \App\Enums\PaymentStatusEnum::PAID->value)
                                            ->sum('amount');

                                        $percentage = $record->payment_percentage;
                                        $statusColor = $percentage >= 100 ? 'success' : ($percentage > 0 ? 'warning' : 'danger');
                                        $statusText = $record->payment_status_text;

                                        return view('components.payment-status-badge', [
                                            'amount' => $paidAmount,
                                            'total' => $record->total,
                                            'percentage' => $percentage,
                                            'statusText' => $statusText,
                                            'statusColor' => $statusColor
                                        ]);
                                    }),
                            ])
                            ->columns(3)
                            ->columnSpan(['lg' => 2]),

                        // Sección lateral (1/3 del ancho)
                        Forms\Components\Section::make('Estado e información')
                            ->schema([
                                Forms\Components\Select::make('order_status')
                                    ->label('Estado de la orden')
                                    ->options(OrderStatusEnum::class)
                                    ->default(OrderStatusEnum::PENDING)
                                    ->required(),

                                Forms\Components\Placeholder::make('payment_status_display')
                                    ->label('Estado del pago')
                                    ->content(function (Order $record) {
                                        if (!$record->exists) return 'Pendiente';

                                        $percentage = $record->payment_percentage;
                                        $statusColor = $percentage >= 100 ? 'success' : ($percentage > 0 ? 'warning' : 'danger');

                                        return view('components.badge', [
                                            'label' => $record->payment_status_text,
                                            'color' => $statusColor,
                                        ]);
                                    }),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Creada')
                                    ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Última actualización')
                                    ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                            ])
                            ->columns(2)
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
                ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->getStateUsing(function (Order $record) {
                        return $record->orderDetails()->sum('subtotal');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),

                Tables\Columns\TextColumn::make('payment_status_text')
                    ->label('Estado de pago')
                    ->badge()
                    ->color(function (Order $record): string {
                        $percentage = $record->payment_percentage;
                        if ($percentage >= 100) return 'success';
                        if ($percentage > 0) return 'warning';
                        return 'danger';
                    }),

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
            'create-custom' => Pages\CreateOrderCustom::route('/create-custom'),
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
