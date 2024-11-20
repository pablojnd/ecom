<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Nueva Orden', Order::query()->where('order_status', 'new')->count()),
            Stat::make('prcesando', Order::query()->where('order_status', 'processing')->count()),
            Stat::make('Cancelado', Order::query()->where('order_status', 'canceled')->count()),
            Stat::make('Ventas', number_format(Order::query()->avg('grand_total'), 0, ',', '.')),
        ];
    }
}
