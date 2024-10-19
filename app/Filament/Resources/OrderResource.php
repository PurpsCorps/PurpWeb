<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\Meja;
use App\Models\User;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Order Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make('Client Information')
                //     ->schema([
                //         Forms\Components\Select::make('username')
                //             ->label('Client')
                //             ->options(User::pluck('fullname', 'id'))
                //             ->searchable()
                //             ->required()
                //             ->live()
                //             ->afterStateUpdated(function ($state, Set $set) {
                //                 $user = User::find($state);
                //                 if ($user) {
                //                     $set('client_fullname', $user->fullname);
                //                     $set('client_email', $user->email);
                //                     $set('client_dob', $user->date_of_birth);
                //                 }
                //             }),
                //         Forms\Components\TextInput::make('client_fullname')
                //             ->label('Client Name')
                //             ->disabled()
                //             ->dehydrated(),
                //         Forms\Components\TextInput::make('client_email')
                //             ->label('Email')
                //             ->disabled()
                //             ->dehydrated(),
                //         Forms\Components\DatePicker::make('client_dob')
                //             ->label('Date of Birth')
                //             ->disabled()
                //             ->dehydrated(),
                //     ]),
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label('Order ID')
                            ->default(fn () => 'SERUIT/' . date('m') . '/' . strtoupper(Str::random(3)) . '/' . Order::query()->count()+1)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('meja')
                            ->native(false)
                            ->label('Meja')
                            ->options(Meja::query()->where('status', 'available')->pluck('name', 'name'))
                            ->required(),
                        Forms\Components\Repeater::make('order_items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::pluck('label', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_price', $product->price);
                                            $set('available_stock', $product->stock);
                                        }
                                    }),
                                Forms\Components\TextInput::make('product_price')
                                    ->label('Price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->prefix('Rp.'),
                                Forms\Components\TextInput::make('available_stock')
                                    ->label('Available Stock')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $availableStock = $get('available_stock');
                                        if ($state > $availableStock) {
                                            Notification::make()
                                                ->title('Insufficient stock')
                                                ->body("Only {$availableStock} items available.")
                                                ->danger()
                                                ->send();
                                            $set('quantity', $availableStock);
                                        }
                                    }),
                                Forms\Components\Placeholder::make('subtotal')
                                    ->label('Subtotal')
                                    ->content(function (Get $get) {
                                        return 'Rp. ' . number_format($get('product_price') * $get('quantity'), 0, ',', '.');
                                    }),
                            ])
                            ->columns(4)
                            ->dehydrated(true)
                            ->defaultItems(1)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = collect($get('order_items'))->sum(fn ($item) => ($item['product_price'] ?? 0) * ($item['quantity'] ?? 0));
                                $set('price_total', $total);
                            }),
                        Forms\Components\TextInput::make('price_total')
                            ->label('Total Price')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->prefix('Rp.'),
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
                                'Payment Received' => 'Payment Received',
                                'On Process' => 'On Process',
                                'Pending' => 'Pending',
                                'Completed' => 'Completed',
                                'Canceled' => 'Canceled',
                            ])
                            ->required()
                            ->live(),
                    ]),
            ])
            ->statePath('data')
            ->model(Order::class);
    }

    public static function getNavigationBadge(): ?string
    {
        return Order::query()->where('status', 'Payment Received')->count() . ' / ' . Order::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Order::query()->where('status', 'Payment Received')->count() > 0 ? 'warning' : 'primary';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meja')
                    ->label('Meja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_items')
                    ->label('Order Items')
                    ->getStateUsing(function (Order $record): string {
                        $items = $record->order_items;

                        if (!is_array($items)) {
                            return 'Invalid data';
                        }

                        $formattedItems = array_map(function ($item) {
                            $productId = $item['product_id'] ?? null;
                            $quantity = $item['quantity'] ?? 0;
                            $product = $productId ? Product::find($productId) : null;

                            if (!$product) {
                                return "Unknown product (x{$quantity})";
                            }
                            return "({$quantity}) {$product->label}";
                        }, $items);

                        return implode("\n", $formattedItems);
                    })
                    ->html()
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('products', function ($query) use ($search) {
                            $query->where('label', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('price_total')
                    ->label('Total Price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Payment Received' => 'info',
                        'On Process' => 'warning',
                        'Pending' => 'danger',
                        'Completed' => 'success',
                        'Canceled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('process')
                    ->button()
                    ->label('Process')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'On Process']);
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (Order $record): bool => $record->status === 'On Process' || $record->status === 'Completed' || $record->status === 'Canceled')
                    ->successNotificationTitle('Order marked as On Process'),
                Action::make('complete')
                    ->button()
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'Completed']);
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (Order $record): bool => $record->status === 'Completed' || $record->status === 'Canceled')
                    ->successNotificationTitle('Order marked as completed'),
                Action::make('cancel')
                    ->button()
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'Canceled']);
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (Order $record): bool => $record->status === 'Canceled' || $record->status === 'Completed')
                    ->successNotificationTitle('Order marked as Canceled'),
                Action::make('print_receipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('receipt', ['order' => $record]);
                        return response($pdf->output())
                            ->header('Content-Type', 'application/pdf')
                            ->header('Content-Disposition', 'inline; filename="receipt.pdf"');
                        })
                        ->openUrlInNewTab()
                    ->hidden(fn (Order $record): bool => $record->status === 'Payment Received' || $record->status === 'On Process' || $record->status === 'Pending'),
                Tables\Actions\ViewAction::make(),
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

class CreateOrder extends Pages\CreateOrder
{
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Order created successfully')
            ->success()
            ->send();
    }

    // Override the create() method to handle exceptions
    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error creating order')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}