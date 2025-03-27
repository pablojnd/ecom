<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\PaymentStatusEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    // Definir el atributo del título del registro para identificación
    protected static ?string $recordTitleAttribute = 'id';

    // Personalizar título del RelationManager
    protected static ?string $title = 'Pagos';

    // Método para permitir la traducción del título
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Pagos';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del pago')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(PaymentStatusEnum::class)
                            ->default(PaymentStatusEnum::PENDING)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PaymentStatusEnum::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar pago')
                    ->modalHeading('Registrar nuevo pago')
                    ->after(function () {
                        $this->getOwnerRecord()->updatePaymentStatus();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->after(function () {
                        $this->getOwnerRecord()->updatePaymentStatus();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->after(function () {
                        $this->getOwnerRecord()->updatePaymentStatus();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->after(function () {
                            $this->getOwnerRecord()->updatePaymentStatus();
                        }),
                ]),
            ])
            ->emptyStateHeading('No hay pagos registrados')
            ->emptyStateDescription('Registra pagos para esta orden usando el botón de arriba.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
