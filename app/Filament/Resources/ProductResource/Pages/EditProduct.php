<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Log::info('EditProduct: Handling record update', ['data' => $data]);
        return ProductResource::handleRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
