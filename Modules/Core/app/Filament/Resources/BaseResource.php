<?php

namespace Modules\Core\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;

abstract class BaseResource extends Resource
{
    /**
     * Get default table columns.
     */
    public static function getDefaultTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            
            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Updated At')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Get default table actions.
     */
    public static function getDefaultTableActions(): array
    {
        return [
            ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
        ];
    }

    /**
     * Get default table bulk actions.
     */
    public static function getDefaultTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    /**
     * Get default table filters.
     */
    public static function getDefaultTableFilters(): array
    {
        return [
            Tables\Filters\TrashedFilter::make(),
        ];
    }

    /**
     * Should register navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Get navigation badge.
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
