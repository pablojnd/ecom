<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('brand.name')
                ->label('Marca'),
            ExportColumn::make('category.name')
                ->label('Categoría'),
            ExportColumn::make('slug'),
            ExportColumn::make('price')
                ->prefix('$')
                ->label('Precio'),
            ExportColumn::make('description')
                ->label('Descripción')
                ->limit(fn (array $options): int => $options['descriptionLimit'] ?? 100),
            ExportColumn::make('image_path')
                ->label('Imagen'),
            ExportColumn::make('created_at')
                ->label('Fecha creación')
                ->formatStateUsing(fn (string $state): string => date('Y-m-d H:i:s', strtotime($state))),
            ExportColumn::make('updated_at')
                ->label('Última actualización')
                ->formatStateUsing(fn (string $state): string => date('Y-m-d H:i:s', strtotime($state))),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('descriptionLimit')
                ->label('Límite de caracteres para la descripción')
                ->integer()
                ->default(100),
        ];
    }

    public function getFileName(Export $export): string
    {
        return "productos-export-{$export->getKey()}.csv";
    }
}
