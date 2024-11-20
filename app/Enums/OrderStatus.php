<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum OrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case New = 'new';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';

    public static function toArray(): array
    {
        return array_column(OrderStatus::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => 'Nuevo',
            self::Processing => 'Procesando',
            self::Shipped => 'Enviado',
            self::Delivered => 'Reparto',
            self::Canceled => 'Cancelado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info',
            self::Processing => 'warning',
            self::Shipped => 'success',
            self::Delivered => 'success',
            self::Canceled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Processing => 'heroicon-m-arrow-path',
            self::Shipped => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-check-badge',
            self::Canceled => 'heroicon-m-x-circle',
        };
    }
}
