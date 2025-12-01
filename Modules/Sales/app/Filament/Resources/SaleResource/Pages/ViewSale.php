<?php

namespace Modules\Sales\Filament\Resources\SaleResource\Pages;

use Modules\Sales\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Modules\Sales\Services\SaleService;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('void')
                ->label('Void Sale')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'completed')
                ->form([
                    \Filament\Forms\Components\Textarea::make('void_reason')
                        ->label('Void Reason')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data, SaleService $saleService) {
                    $saleService->voidSale(
                        $this->record->id,
                        auth()->id(),
                        $data['void_reason']
                    );

                    $this->redirect(self::getUrl(['record' => $this->record]));
                }),
        ];
    }
}
