<?php

namespace App\Filament\Resources\NetflixStokResource\Pages;

use App\Filament\Resources\NetflixStokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNetflixStok extends EditRecord
{
    protected static string $resource = NetflixStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
