<?php

namespace App\Filament\Resources\SpotifyStokResource\Pages;

use App\Filament\Resources\SpotifyStokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpotifyStoks extends ListRecords
{
    protected static string $resource = SpotifyStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
