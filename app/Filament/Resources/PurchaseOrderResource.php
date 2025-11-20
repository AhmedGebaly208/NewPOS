<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use Modules\Inventory\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('PO Number')
                            ->default(fn () => PurchaseOrder::generateReferenceNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email(),
                                Forms\Components\TextInput::make('phone'),
                            ]),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DatePicker::make('order_date')
                            ->default(now())
                            ->required(),
                        
                        Forms\Components\DatePicker::make('expected_date')
                            ->label('Expected Delivery'),
                        
                        Forms\Components\DatePicker::make('received_date')
                            ->label('Received Date')
                            ->disabled(fn ($record) => $record && $record->status !== 'received'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Purchase Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $product = \Modules\Product\Models\Product::find($state);
                                            if ($product) {
                                                $set('unit_cost', $product->cost);
                                                $set('tax_rate', $product->tax_rate);
                                            }
                                        }
                                    })
                                    ->columnSpan(3),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    )
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('unit_cost')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    )
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('tax_rate')
                                    ->label('Tax %')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    )
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Tax')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                self::calculateOrderTotals($state, $set, $get)
                            ),
                    ]),

                Forms\Components\Section::make('Order Totals')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('shipping')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                self::updateTotal($set, $get)
                            ),
                        
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                self::updateTotal($set, $get)
                            ),
                        
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->extraAttributes(['class' => 'text-lg font-bold']),
                    ])
                    ->columns(5),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function calculateItemTotals(Forms\Set $set, Forms\Get $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitCost = (float) ($get('unit_cost') ?? 0);
        $taxRate = (float) ($get('tax_rate') ?? 0);

        $subtotal = $quantity * $unitCost;
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        $set('subtotal', number_format($subtotal, 2, '.', ''));
        $set('tax_amount', number_format($taxAmount, 2, '.', ''));
        $set('total', number_format($total, 2, '.', ''));
    }

    protected static function calculateOrderTotals($state, Forms\Set $set, Forms\Get $get = null): void
    {
        $items = $state ?? [];
        
        $subtotal = 0;
        $tax = 0;

        foreach ($items as $item) {
            $subtotal += (float) ($item['subtotal'] ?? 0);
            $tax += (float) ($item['tax_amount'] ?? 0);
        }

        $set('subtotal', number_format($subtotal, 2, '.', ''));
        $set('tax', number_format($tax, 2, '.', ''));
        
        // Also trigger total calculation with current shipping/discount
        if ($get) {
            $shipping = (float) ($get('shipping') ?? 0);
            $discount = (float) ($get('discount') ?? 0);
        } else {
            $shipping = 0;
            $discount = 0;
        }
        
        $total = $subtotal + $tax + $shipping - $discount;
        $set('total', number_format($total, 2, '.', ''));
    }

    protected static function updateTotal(Forms\Set $set, Forms\Get $get): void
    {
        $subtotal = (float) ($get('subtotal') ?? 0);
        $tax = (float) ($get('tax') ?? 0);
        $shipping = (float) ($get('shipping') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $total = $subtotal + $tax + $shipping - $discount;

        $set('total', number_format($total, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expected_date')
                    ->label('Expected')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name'),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
}
