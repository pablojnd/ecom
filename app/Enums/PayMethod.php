<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum PayMethod: string implements HasLabel, HasColor, HasIcon
{
    case Cash = 'cash';
    case Debit = 'debit';
    case Credit = 'credit';
    case Transfer = 'transfer';

    public static function toArray(): array
    {
        return array_column(PayMethod::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => 'cash',
            self::Debit => 'debit',
            self::Credit => 'credit',
            self::Transfer => 'transfer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::Debit => 'primary',
            self::Credit => 'secondary',
            self::Transfer => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-o-banknotes',
            self::Debit => 'heroicon-o-credit-card',
            self::Credit => 'heroicon-o-credit-card',
            self::Transfer => 'heroicon-o-switch-horizontal',
        };
    }
}
