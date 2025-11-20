<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use Modules\Customer\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone_secondary')
                            ->label('Secondary Phone')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Customer Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('total_spent')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('total_orders')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        
                        Forms\Components\DatePicker::make('last_order_date')
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\TextInput::make('loyalty_points')
                            ->numeric()
                            ->suffix('pts')
                            ->default(0),
                    ])
                    ->columns(4)
                    ->hidden(fn ($operation) => $operation === 'create'),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(1),
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
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('tier')
                    ->badge()
                    ->colors([
                        'secondary' => 'bronze',
                        'warning' => 'silver',
                        'success' => 'gold',
                        'primary' => 'platinum',
                    ])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_spent', $direction);
                    }),
                
                Tables\Columns\TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'platinum' => 'Platinum',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        
                        if (!$value) {
                            return $query;
                        }
                        
                        return match($value) {
                            'platinum' => $query->where('total_spent', '>=', 5000),
                            'gold' => $query->whereBetween('total_spent', [2000, 4999.99]),
                            'silver' => $query->whereBetween('total_spent', [500, 1999.99]),
                            'bronze' => $query->where('total_spent', '<', 500),
                            default => $query,
                        };
                    }),
                
                Tables\Filters\Filter::make('recently_active')
                    ->label('Recently Active (30 days)')
                    ->query(fn (Builder $query): Builder => $query->recentlyActive()),
                
                Tables\Filters\Filter::make('vip')
                    ->label('VIP Customers ($1000+)')
                    ->query(fn (Builder $query): Builder => $query->vip()),
                
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
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
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getWidgets(): array
    {
        return [
            // CustomerStatsWidget::class,
        ];
    }
}
