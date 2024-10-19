<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Ingredient;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Foundation\Http\FormRequest;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required(),
                Forms\Components\Section::make('Ingredients')
                    ->schema([
                        Repeater::make('ingredients')
                        ->schema([
                            Select::make('ingredient_id')
                                ->label('Ingredient')
                                ->options(Ingredient::pluck('name', 'id'))
                                ->required(),
                            TextInput::make('amount')
                                ->numeric()
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->required()
                        ->label('Ingredients')
                        ->defaultItems(1)
                        ->afterStateHydrated(function (Repeater $component, $state, Get $get, Set $set) {
                            $productId = $get('id');
                            Log::info("Hydrating ingredients for product ID: {$productId}");

                            if ($productId && empty($state)) {
                                $product = Product::find($productId);
                                Log::info("Product found: " . ($product ? 'Yes' : 'No'));

                                if ($product) {
                                    $ingredients = $product->ingredients()->get();
                                    Log::info("Ingredients found: " . $ingredients->count());

                                    if ($ingredients->isNotEmpty()) {
                                        $ingredientData = $ingredients->map(function ($ingredient) {
                                            return [
                                                'ingredient_id' => $ingredient->id,
                                                'amount' => $ingredient->pivot->amount,
                                            ];
                                        })->toArray();

                                        Log::info("Setting ingredients: " . json_encode($ingredientData));
                                        $set('ingredients', $ingredientData);
                                    } else {
                                        Log::info("No ingredients found for product");
                                    }
                                } else {
                                    Log::info("Product not found");
                                }
                            } else {
                                Log::info("State is not empty or product ID is missing");
                            }
                        }),
                    ]),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function afterCreate(Model $record, array $data): void
    {
        Log::info('afterCreate called for product: ' . $record->id);
        self::syncIngredients($record, $data);
    }

    public static function afterSave(Model $record, array $data): void
    {
        Log::info('afterSave called for product: ' . $record->id);
        self::syncIngredients($record, $data);
    }

    public static function handleRecordCreation(array $data): Model
    {
        Log::info('ProductResource: Creating product with data: ' . json_encode($data));

        $ingredients = $data['ingredients'] ?? [];
        unset($data['ingredients']);

        $product = static::getModel()::create($data);
        self::syncIngredients($product, $ingredients);

        Log::info('Product created with ID: ' . $product->id);
        return $product;
    }

    public static function handleRecordUpdate(Model $record, array $data): Model
    {
        Log::info('ProductResource: Updating product with ID: ' . $record->id);
        Log::info('Update data: ' . json_encode($data));

        $ingredients = $data['ingredients'] ?? [];
        unset($data['ingredients']);

        $record->update($data);
        self::syncIngredients($record, $ingredients);

        Log::info('Product updated successfully');
        return $record;
    }

    private static function syncIngredients(Model $record, array $ingredients): void
    {
        $ingredientsData = collect($ingredients)->mapWithKeys(function ($item) {
            return [$item['ingredient_id'] => ['amount' => $item['amount']]];
        })->toArray();

        Log::info('Syncing ingredients: ' . json_encode($ingredientsData));
        $record->ingredients()->sync($ingredientsData);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Mutating form data before create:', $data);
        return $data;
    }

    public static function mutateFormDataBeforeUpdate(Model $record, array $data): array
    {
        Log::info('Mutating form data before update:', $data);
        return $data;
    }

    public static function getProductWithIngredients($productId)
    {
        return static::getModel()::with('ingredients')->findOrFail($productId);
    }
}