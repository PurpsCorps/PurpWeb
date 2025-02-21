<?php

namespace App\Filament\Resources\SpotifyStokResource\Pages;

use App\Filament\Resources\SpotifyStokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpotifyStok extends EditRecord
{
    protected static string $resource = SpotifyStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
