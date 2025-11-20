<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use Modules\Inventory\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Stock Movement';

    protected static ?string $pluralLabel = 'Stock Movements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->disabled(),
                
                Forms\Components\Select::make('type')
                    ->options([
                        'purchase' => 'Purchase',
                        'sale' => 'Sale',
                        'adjustment' => 'Adjustment',
                        'return' => 'Return',
                        'transfer' => 'Transfer',
                        'initial' => 'Initial Stock',
                    ])
                    ->required()
                    ->disabled(),
                
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->disabled(),
                
                Forms\Components\TextInput::make('quantity_before')
                    ->label('Quantity Before')
                    ->numeric()
                    ->disabled(),
                
                Forms\Components\TextInput::make('quantity_after')
                    ->label('Quantity After')
                    ->numeric()
                    ->disabled(),
                
                Forms\Components\TextInput::make('reference_type')
                    ->label('Reference Type')
                    ->disabled(),
                
                Forms\Components\TextInput::make('reference_id')
                    ->label('Reference ID')
                    ->disabled(),
                
                Forms\Components\Textarea::make('notes')
                    ->disabled()
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'purchase',
                        'danger' => 'sale',
                        'warning' => 'adjustment',
                        'info' => 'return',
                        'secondary' => 'transfer',
                        'primary' => 'initial',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->quantity > 0 ? 'success' : 'danger'),
                
                Tables\Columns\TextColumn::make('quantity_before')
                    ->label('Before')
                    ->numeric()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('quantity_after')
                    ->label('After')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Reference')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'purchase' => 'Purchase',
                        'sale' => 'Sale',
                        'adjustment' => 'Adjustment',
                        'return' => 'Return',
                        'transfer' => 'Transfer',
                        'initial' => 'Initial Stock',
                    ]),
                
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Stock movements are created automatically
    }

    public static function canEdit($record): bool
    {
        return false; // Stock movements cannot be edited
    }

    public static function canDelete($record): bool
    {
        return false; // Stock movements cannot be deleted
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }
}
