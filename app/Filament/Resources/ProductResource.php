<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use Modules\Product\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            )
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name'),
                        
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                            ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => Product::generateSKU())
                            ->helperText('Auto-generated if left empty'),
                        
                        Forms\Components\TextInput::make('barcode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('stock_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(10),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Product Settings')
                    ->schema([
                        Forms\Components\Select::make('unit')
                            ->required()
                            ->options([
                                'piece' => 'Piece',
                                'kg' => 'Kilogram',
                                'g' => 'Gram',
                                'liter' => 'Liter',
                                'ml' => 'Milliliter',
                                'box' => 'Box',
                                'pack' => 'Pack',
                            ])
                            ->default('piece'),
                        
                        Forms\Components\TextInput::make('tax_rate')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                        
                        Forms\Components\Toggle::make('track_stock')
                            ->label('Track Stock')
                            ->default(true)
                            ->required(),
                        
                        Forms\Components\Toggle::make('allow_backorder')
                            ->label('Allow Backorder')
                            ->default(false)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Primary Image')
                            ->image()
                            ->directory('products')
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('images')
                            ->label('Additional Images')
                            ->image()
                            ->directory('products')
                            ->multiple()
                            ->maxFiles(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->isOutOfStock() ? 'danger' : 
                        ($record->isLowStock() ? 'warning' : 'success')
                    )
                    ->badge(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active products')
                    ->falseLabel('Inactive products')
                    ->native(false),
                
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn (Builder $query) => $query->lowStock())
                    ->label('Low Stock'),
                
                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn (Builder $query) => $query->outOfStock())
                    ->label('Out of Stock'),
                
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
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
        return static::getModel()::count();
    }
}
