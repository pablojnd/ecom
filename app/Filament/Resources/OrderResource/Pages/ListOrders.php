<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Enums\OrderStatus;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStats::class
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders'),
            'new' => Tab::make('New Orders')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('order_status', OrderStatus::New->value)),
            'processing' => Tab::make('Processing Orders')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('order_status', OrderStatus::Processing->value)),
            'shipped' => Tab::make('Shipped Orders')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('order_status', OrderStatus::Shipped->value)),
            'delivered' => Tab::make('Delivered Orders')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('order_status', OrderStatus::Delivered->value)),
            'canceled' => Tab::make('Canceled Orders')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('order_status', OrderStatus::Canceled->value)),
        ];
    }
}
