<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CosmeticResource\Pages;
use App\Filament\Resources\CosmeticResource\RelationManagers;
use App\Models\Cosmetic;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CosmeticResource extends Resource
{
    protected static ?string $model = Cosmetic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('details')
                ->schema ([
                    Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required(),

                    forms\Components\FileUpload::make('thumbnail')
                    ->required()
                    ->image(),

                    Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),

                    Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->prefix('qty'),
                ]),

                Fieldset::make('additional')
                ->schema([

                    Forms\Components\Repeater::make('benefits')
                    ->relationship('benefits')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->required(),
                    ]),

                    Forms\Components\Repeater::make('photos')
                    ->relationship('photos')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                        ->image()
                        ->required(),
                    ]),

                    Forms\Components\Textarea::make('description')
                    ->required(),

                    Forms\Components\Select::make('is_popular')
                    ->options([
                        true => 'Popular',
                        false => 'Not popular',
                    ])
                    ->required(),

                    Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                    Forms\Components\Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name'),

                Tables\Columns\TextColumn::make('brand.name'),

                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Popular'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListCosmetics::route('/'),
            'create' => Pages\CreateCosmetic::route('/create'),
            'edit' => Pages\EditCosmetic::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
