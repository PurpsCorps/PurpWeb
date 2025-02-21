<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisneyStokResource\Pages;
use App\Filament\Resources\DisneyStokResource\RelationManagers;
use App\Models\DisneyStok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DisneyStokResource extends Resource
{
    protected static ?string $model = DisneyStok::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Stok Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stok Information')
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->required(),
                    Forms\Components\TextInput::make('password')
                        ->required(),
                    Forms\Components\TextInput::make('profilepin')
                        ->required(),
                    Forms\Components\TextInput::make('usage')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('password')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usage')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDisneyStoks::route('/'),
            'create' => Pages\CreateDisneyStok::route('/create'),
            'edit' => Pages\EditDisneyStok::route('/{record}/edit'),
        ];
    }
}
