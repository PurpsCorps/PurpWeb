<?php

namespace App\Filament\Resources\NetflixStokResource\Pages;

use App\Filament\Resources\NetflixStokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNetflixStoks extends ListRecords
{
    protected static string $resource = NetflixStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
