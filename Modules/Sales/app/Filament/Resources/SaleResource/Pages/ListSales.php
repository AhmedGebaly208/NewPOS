<?php

namespace Modules\Sales\Filament\Resources\SaleResource\Pages;

use Modules\Sales\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sales are created through POS, not through admin panel
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SaleResource\Widgets\SalesStatsWidget::class,
        ];
    }
}
