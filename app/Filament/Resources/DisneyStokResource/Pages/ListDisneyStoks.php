<?php

namespace App\Filament\Resources\DisneyStokResource\Pages;

use App\Filament\Resources\DisneyStokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisneyStoks extends ListRecords
{
    protected static string $resource = DisneyStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
