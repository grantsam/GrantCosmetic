<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Cosmetic;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\BookingTransaction;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use Filament\Notifications\Notification;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Customer';

    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedCosmetics = collect($get('transactionDetails'))->filter(fn($item)
        => !empty($item['cosmetic_id']) && !empty($item['quantity']));

        $prices = Cosmetic::find($selectedCosmetics->pluck('cosmetic_id'))->pluck('price', 'id');

        $subtotal = $selectedCosmetics->reduce(function ($subtotal, $item) use ($prices) {
            return $subtotal + ($prices[$item['cosmetic_id']] * $item['quantity']);
        }, 0);

        $total_tax_amount = round($subtotal * 0.12);

        $total_amount = round($subtotal + $total_tax_amount);

        $total_quantity = $selectedCosmetics->sum('quantity');

        $set('total_amount', number_format($total_amount, 0, ',', '.'));
        $set('sub_total_amount', number_format($subtotal, 0, ',', '.'));
        $set('total_tax_amount', number_format($total_tax_amount, 0, ',', '.'));
        $set('quantity', $total_quantity);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Wizard::make([

                    Forms\Components\Wizard\Step::make('Product and Price')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('add your product')
                        ->schema([
                            Grid::make(2)
                                ->schema([

                                    Forms\Components\Repeater::make('transactionDetails')
                                        ->relationship('transactionDetails')
                                        ->schema([

                                            Forms\Components\Select::make('cosmetic_id')
                                                ->relationship('cosmetic', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->label('select product')
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $cosmetic = Cosmetic::find($state);
                                                    $set('price', $cosmetic ? $cosmetic->price : null);
                                                }),

                                            Forms\Components\TextInput::make('price')
                                                ->required()
                                                ->numeric()
                                                ->readOnly()
                                                ->label('price')
                                                ->hint('price of the product (Read Only)'),

                                            Forms\Components\TextInput::make('quantity')
                                                ->required()
                                                ->integer()
                                                ->default(1)

                                        ])
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::updateTotals($get, $set);
                                        })
                                        ->minItems(1)
                                        ->columnSpan('full')
                                        ->label('choose products')
                                ]),

                            Grid::make(4)
                                ->schema([
                                    Forms\Components\TextInput::make('quantity')
                                        ->readOnly()
                                        ->label('total quantity')
                                        ->integer()
                                        ->default(1)
                                        ->required(),

                                    Forms\Components\TextInput::make('sub_total_amount')
                                        ->readOnly()
                                        ->label('sub total amount')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('total_amount')
                                        ->readOnly()
                                        ->label('total amount')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('total_tax_amount')
                                        ->label('total tax (12%)')
                                        ->numeric()
                                        ->readOnly(),
                                ])

                        ]),

                    Forms\Components\Wizard\Step::make('Customer Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('add your customer information')
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->label('name')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('phone')
                                        ->required()
                                        ->label('phone')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('email')
                                        ->required()
                                        ->label('email')
                                        ->maxLength(255),

                                ]),


                        ]),

                    Forms\Components\Wizard\Step::make('Shipping Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('add your shipping information')
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    Forms\Components\TextInput::make('address')
                                        ->required()
                                        ->label('address')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('post_code')
                                        ->required()
                                        ->label('post code')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('city')
                                        ->required()
                                        ->label('city')
                                        ->maxLength(255),

                                ]),

                        ]),

                    Forms\Components\Wizard\Step::make('Payment Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('add your payment information')
                        ->schema([

                            Grid::make()
                                ->schema([

                                    Forms\Components\TextInput::make('booking_trx_id')
                                        ->required()
                                        ->maxLength(255),

                                    ToggleButtons::make('is_paid')
                                        ->icons([
                                            true => 'heroicon-o-check-circle',
                                            false => 'heroicon-o-x-circle',
                                        ])
                                        ->label('Apakah sudah membayar?')
                                        ->boolean()
                                        ->grouped()
                                        ->required(),

                                    Forms\Components\FileUpload::make('proof')
                                        ->image()
                                        ->required(),

                                ]),

                        ]),

                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->truecolor('success')
                    ->falsecolor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('terverifikasi'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('approve')
                    ->action(function (BookingTransaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('Order has been approved')
                            ->send();
                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (bookingTransaction $record) => !$record->is_paid),
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
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
