<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use Illuminate\Routing\Route;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                ->description('Add the client information here!')
                ->schema([
                    Forms\Components\Select::make('username')
                        ->native(false)
                        ->label('Client')
                        ->options(User::query()->pluck('fullname', 'name'))
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                            $set('client_fullname', User::query()->where('name', $state)->pluck('fullname')[0] ?? $state . " -> Invalid Plug") &&
                            $set('client_email', User::query()->where('name', $state)->pluck('email')[0] ?? $state . " -> Invalid Plug") &&
                            $set('client_dob', User::query()->where('name', $state)->pluck('date_of_birth')[0] ?? $state . " -> Invalid Plug"))
                        ->distinct()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('client_fullname')
                        ->label('Client Name')
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('client_email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('client_dob')
                        ->label('Date of Birth')
                        ->disabled()
                        ->dehydrated(),
                ]),
                Forms\Components\Section::make('Order Information')
                ->description('Add the order information here!')
                ->schema([
                    Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                        ->default('PURPS/'.date('m').'/'.strtoupper(Str::random(5)))
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('product')
                        ->label('Select Product')
                        ->native(false)
                        ->options(Product::query()->pluck('label', 'name'))
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                            $set('product_label', Product::query()->where('name', $get('product'))->pluck('label')[0] ?? 'No Label') &&
                            $set('product_price', (int)Product::query()->where('name', $get('product'))->pluck('price')[0] ?? 0)
                        )
                        ->distinct()
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('product_label')
                        ->label('Product Name')
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\TextInput::make('product_price')
                        ->label('Product Price')
                        ->prefix('Rp.')
                        ->default(0)
                        ->disabled()
                        ->dehydrated()
                        ->numeric(),
                    Forms\Components\TextInput::make('quantity')
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                            $set('price_total', (int)Product::query()->where('name', $get('product'))->pluck('price')[0]*$state ?? 0)
                        )
                        ->distinct()
                        ->required()
                        ->live()
                        ->numeric(),
                    Forms\Components\TextInput::make('price_total')
                        ->label('Price Total')
                        ->prefix('Rp.')
                        ->default(0)
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->numeric(),
                    Forms\Components\Select::make('payment_method')
                        ->native(false)
                        ->label('Payment Method')
                        ->options([
                            'GoPay' => 'GoPay',
                            'OVO' => 'OVO',
                            'Bank' => 'Bank',
                            'QRIS' => 'QRIS',
                            'Saldo' => 'Saldo',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->native(false)
                        ->options([
                            'Payment Receive' => 'Payment Receive',
                            'On Process' => 'On Process',
                            'Pending' => 'Pending',
                            'Completed' => 'Completed',
                            'Canceled' => 'Canceled',
                        ])
                        ->required()
                        ->live(),
                ])
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return Order::query()->where('status', 'Payment Receive')->count() . ' / ' . Order::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Order::query()->where('status', 'Payment Receive')->count() > 0 ? 'warning' : 'primary';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_fullname')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_label')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_price')
                    ->label('Product Price')
                    ->prefix('Rp.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_total')
                    ->label('Price Total')
                    ->prefix('Rp.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Payment Receive' => 'info',
                        'On Process' => 'process',
                        'Pending' => 'warning',
                        'Completed' => 'success',
                        'Canceled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                ->successRedirectUrl(env('APP_URL').'/admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
