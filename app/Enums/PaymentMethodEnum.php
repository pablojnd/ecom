<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodEnum: string implements HasLabel, HasColor, HasIcon
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case PAYPAL = 'paypal';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Tarjeta de Crédito',
            self::DEBIT_CARD => 'Tarjeta de Débito',
            self::CASH => 'Efectivo',
            self::TRANSFER => 'Transferencia',
            self::PAYPAL => 'PayPal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREDIT_CARD => 'success',
            self::DEBIT_CARD => 'info',
            self::CASH => 'warning',
            self::TRANSFER => 'primary',
            self::PAYPAL => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::DEBIT_CARD => 'heroicon-o-credit-card',
            self::CASH => 'heroicon-o-banknotes',
            self::TRANSFER => 'heroicon-o-arrow-path',
            self::PAYPAL => 'heroicon-o-currency-dollar',
        };
    }
}
