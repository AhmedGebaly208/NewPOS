<?php

namespace Modules\Sales\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Sales\Models\Sale;

class SalesStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Sale::whereDate('created_at', today())
            ->where('status', 'completed');

        $thisMonth = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed');

        return [
            Stat::make('Today\'s Sales', $today->count())
                ->description('Total transactions today')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Today\'s Revenue', '$' . number_format($today->sum('total_amount'), 2))
                ->description('Total revenue today')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('This Month\'s Sales', $thisMonth->count())
                ->description('Total transactions this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('This Month\'s Revenue', '$' . number_format($thisMonth->sum('total_amount'), 2))
                ->description('Total revenue this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
