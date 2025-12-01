<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use Modules\Discount\Models\Discount;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Customer\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationGroup = 'Sales & Discounts';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Discount Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                                'buy_x_get_y' => 'Buy X Get Y',
                            ])
                            ->default('percentage')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('value', 0)),
                        
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix(fn (Get $get) => $get('type') === 'fixed' ? '$' : null)
                            ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : null)
                            ->minValue(0)
                            ->maxValue(fn (Get $get) => $get('type') === 'percentage' ? 100 : null)
                            ->hidden(fn (Get $get) => $get('type') === 'buy_x_get_y'),
                        
                        Forms\Components\TextInput::make('buy_quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(fn (Get $get) => $get('type') === 'buy_x_get_y')
                            ->visible(fn (Get $get) => $get('type') === 'buy_x_get_y')
                            ->label('Buy Quantity'),
                        
                        Forms\Components\TextInput::make('get_quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(fn (Get $get) => $get('type') === 'buy_x_get_y')
                            ->visible(fn (Get $get) => $get('type') === 'buy_x_get_y')
                            ->label('Get Quantity (Free)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Application Rules')
                    ->schema([
                        Forms\Components\Select::make('applies_to')
                            ->required()
                            ->options([
                                'all' => 'All Products',
                                'specific_products' => 'Specific Products',
                                'specific_categories' => 'Specific Categories',
                            ])
                            ->default('all')
                            ->live(),
                        
                        Forms\Components\Select::make('products')
                            ->label('Select Products')
                            ->multiple()
                            ->relationship('products', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('applies_to') === 'specific_products')
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('categories')
                            ->label('Select Categories')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('applies_to') === 'specific_categories')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Conditions')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_purchase')
                            ->numeric()
                            ->prefix('$')
                            ->label('Minimum Purchase Amount')
                            ->helperText('Leave empty for no minimum'),
                        
                        Forms\Components\TextInput::make('minimum_quantity')
                            ->numeric()
                            ->minValue(1)
                            ->label('Minimum Items Quantity')
                            ->helperText('Leave empty for no minimum'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Customer Eligibility')
                    ->schema([
                        Forms\Components\Select::make('customer_eligibility')
                            ->required()
                            ->options([
                                'all' => 'All Customers',
                                'customer_tier' => 'Specific Customer Tiers',
                                'specific_customers' => 'Specific Customers',
                            ])
                            ->default('all')
                            ->live(),
                        
                        Forms\Components\CheckboxList::make('eligible_customer_tiers')
                            ->options([
                                'bronze' => 'Bronze',
                                'silver' => 'Silver',
                                'gold' => 'Gold',
                                'platinum' => 'Platinum',
                            ])
                            ->columns(4)
                            ->required(fn (Get $get) => $get('customer_eligibility') === 'customer_tier')
                            ->visible(fn (Get $get) => $get('customer_eligibility') === 'customer_tier')
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('customers')
                            ->label('Select Customers')
                            ->multiple()
                            ->relationship('customers', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('customer_eligibility') === 'specific_customers')
                            ->visible(fn (Get $get) => $get('customer_eligibility') === 'specific_customers')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Validity Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date & Time')
                            ->helperText('Leave empty to start immediately'),
                        
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date & Time')
                            ->helperText('Leave empty for no end date')
                            ->after('starts_at'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(1)
                            ->label('Total Usage Limit')
                            ->helperText('Leave empty for unlimited uses'),
                        
                        Forms\Components\TextInput::make('usage_limit_per_customer')
                            ->numeric()
                            ->minValue(1)
                            ->label('Per Customer Usage Limit')
                            ->helperText('Leave empty for unlimited per customer'),
                        
                        Forms\Components\TextInput::make('times_used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                    ])->columns(3),
                
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Higher priority discounts apply first'),
                        
                        Forms\Components\Toggle::make('is_combinable')
                            ->label('Combinable with Other Discounts')
                            ->default(false)
                            ->helperText('Allow this discount to be combined with others'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                    })
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'fixed',
                        'info' => 'buy_x_get_y',
                    ]),
                
                Tables\Columns\TextColumn::make('value')
                    ->label('Discount Value')
                    ->formatStateUsing(function ($record) {
                        if ($record->type === 'percentage') {
                            return $record->value . '%';
                        } elseif ($record->type === 'fixed') {
                            return '$' . number_format($record->value, 2);
                        } else {
                            return "Buy {$record->buy_quantity} Get {$record->get_quantity}";
                        }
                    })
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('applies_to')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'All Products',
                        'specific_products' => 'Specific Products',
                        'specific_categories' => 'Categories',
                    }),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('times_used')
                    ->label('Used')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->usage_limit) {
                            return "{$record->times_used} / {$record->usage_limit}";
                        }
                        return $record->times_used;
                    })
                    ->color(function ($record) {
                        if (!$record->usage_limit) return 'gray';
                        $percentage = ($record->times_used / $record->usage_limit) * 100;
                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 70) return 'warning';
                        return 'success';
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                    ]),
                
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                
                Tables\Filters\SelectFilter::make('applies_to')
                    ->options([
                        'all' => 'All Products',
                        'specific_products' => 'Specific Products',
                        'specific_categories' => 'Specific Categories',
                    ]),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            ->defaultSort('priority', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
