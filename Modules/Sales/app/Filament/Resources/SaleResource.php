<?php

namespace Modules\Sales\Filament\Resources;

use Modules\Sales\Filament\Resources\SaleResource\Pages;
use Modules\Sales\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Information')
                    ->schema([
                        Forms\Components\TextInput::make('sale_number')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(auth()->id()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'voided' => 'Voided',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('completed'),
                    ])->columns(2),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('discount_amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        Forms\Components\TextInput::make('change_amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                    ])->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->default('Walk-in Customer'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'voided',
                        'info' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'voided' => 'Voided',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Sale Information')
                    ->schema([
                        Components\TextEntry::make('sale_number'),
                        Components\TextEntry::make('customer.name')
                            ->default('Walk-in Customer'),
                        Components\TextEntry::make('user.name')
                            ->label('Cashier'),
                        Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'voided' => 'danger',
                                'refunded' => 'info',
                            }),
                        Components\TextEntry::make('completed_at')
                            ->dateTime(),
                    ])->columns(2),

                Components\Section::make('Sale Items')
                    ->schema([
                        Components\RepeatableEntry::make('items')
                            ->schema([
                                Components\TextEntry::make('product_name'),
                                Components\TextEntry::make('product_sku'),
                                Components\TextEntry::make('quantity'),
                                Components\TextEntry::make('unit_price')
                                    ->money('USD'),
                                Components\TextEntry::make('total')
                                    ->money('USD'),
                            ])
                            ->columns(5),
                    ]),

                Components\Section::make('Amounts')
                    ->schema([
                        Components\TextEntry::make('subtotal')
                            ->money('USD'),
                        Components\TextEntry::make('discount_amount')
                            ->money('USD'),
                        Components\TextEntry::make('tax_amount')
                            ->money('USD'),
                        Components\TextEntry::make('total_amount')
                            ->money('USD')
                            ->weight('bold'),
                        Components\TextEntry::make('paid_amount')
                            ->money('USD'),
                        Components\TextEntry::make('change_amount')
                            ->money('USD'),
                    ])->columns(3),

                Components\Section::make('Payments')
                    ->schema([
                        Components\RepeatableEntry::make('payments')
                            ->schema([
                                Components\TextEntry::make('payment_method')
                                    ->badge(),
                                Components\TextEntry::make('amount')
                                    ->money('USD'),
                                Components\TextEntry::make('reference'),
                            ])
                            ->columns(3),
                    ]),

                Components\Section::make('Additional Information')
                    ->schema([
                        Components\TextEntry::make('notes')
                            ->columnSpanFull(),
                        Components\TextEntry::make('discount_code')
                            ->visible(fn ($record) => !empty($record->discount_code)),
                    ]),
            ]);
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
            'index' => Pages\ListSales::route('/'),
            'view' => Pages\ViewSale::route('/{record}'),
        ];
    }
}
