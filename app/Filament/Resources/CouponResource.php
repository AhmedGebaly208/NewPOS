<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use Modules\Discount\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'Sales & Discounts';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->uppercase()
                            ->placeholder('e.g., SUMMER2025')
                            ->helperText('Coupon code will be automatically converted to uppercase')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('code', strtoupper(Str::random(8)));
                                    })
                            ),
                        
                        Forms\Components\Select::make('discount_id')
                            ->relationship('discount', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Linked Discount')
                            ->helperText('Select which discount this coupon applies'),
                    ]),
                
                Forms\Components\Section::make('Validity Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date & Time')
                            ->helperText('Leave empty to start immediately'),
                        
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date & Time')
                            ->helperText('Leave empty for no expiration')
                            ->after('starts_at'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(1)
                            ->label('Total Usage Limit')
                            ->helperText('Maximum number of times this coupon can be used'),
                        
                        Forms\Components\TextInput::make('usage_limit_per_customer')
                            ->numeric()
                            ->minValue(1)
                            ->label('Per Customer Limit')
                            ->helperText('Times each customer can use this coupon'),
                        
                        Forms\Components\TextInput::make('times_used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Times Used')
                            ->visible(fn ($record) => $record !== null),
                    ])->columns(3),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable or disable this coupon'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Coupon code copied!')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('discount.type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                        'buy_x_get_y' => 'Buy X Get Y',
                        default => 'N/A',
                    })
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'fixed',
                        'info' => 'buy_x_get_y',
                    ]),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Immediate')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('No expiration')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('times_used')
                    ->label('Usage')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->usage_limit) {
                            return "{$record->times_used} / {$record->usage_limit}";
                        }
                        return (string) $record->times_used;
                    })
                    ->color(function ($record) {
                        if (!$record->usage_limit) return 'gray';
                        $percentage = ($record->times_used / $record->usage_limit) * 100;
                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 70) return 'warning';
                        return 'success';
                    })
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
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
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                
                Tables\Filters\Filter::make('valid_now')
                    ->label('Currently Valid')
                    ->query(fn (Builder $query): Builder => $query->valid()),
                
                Tables\Filters\Filter::make('has_usage_remaining')
                    ->label('Has Usage Remaining')
                    ->query(fn (Builder $query): Builder => $query->withinLimit()),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('copy_code')
                        ->label('Copy Code')
                        ->icon('heroicon-o-clipboard')
                        ->action(fn () => null)
                        ->color('gray'),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
