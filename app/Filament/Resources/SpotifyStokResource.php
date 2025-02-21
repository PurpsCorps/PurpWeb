<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpotifyStokResource\Pages;
use App\Filament\Resources\SpotifyStokResource\RelationManagers;
use App\Models\SpotifyStok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpotifyStokResource extends Resource
{
    protected static ?string $model = SpotifyStok::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Stok Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stok Information')
                ->schema([
                    Forms\Components\TextInput::make('link')
                        ->required(),
                    Forms\Components\TextInput::make('alamat')
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
                Tables\Columns\TextColumn::make('link')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
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
            'index' => Pages\ListSpotifyStoks::route('/'),
            'create' => Pages\CreateSpotifyStok::route('/create'),
            'edit' => Pages\EditSpotifyStok::route('/{record}/edit'),
        ];
    }
}
