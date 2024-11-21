<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum PayMethod: string implements HasLabel, HasColor, HasIcon
{
    case CASH = 'cash';
    case DEBIT = 'debit';
    case CREDIT = 'credit';
    case TRANSFER = 'transfer';
    case WEBPAY = 'webpay';

    public static function toArray(): array
    {
        return array_column(PayMethod::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CASH => 'EFECTIVO',
            self::DEBIT => 'DEBITOO',
            self::CREDIT => 'CREDITO',
            self::TRANSFER => 'TRANSFERENCIA',
            self::WEBPAY => 'WEBPAY',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CASH => 'success',
            self::DEBIT => 'primary',
            self::CREDIT => 'secondary',
            self::TRANSFER => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CASH => 'heroicon-o-banknotes',
            self::DEBIT => 'heroicon-o-credit-card',
            self::CREDIT => 'heroicon-o-credit-card',
            self::TRANSFER => 'heroicon-o-switch-horizontal',
        };
    }
}
