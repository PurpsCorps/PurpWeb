<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        Log::info('CreateProduct: Handling record creation', ['data' => $data]);
        return ProductResource::handleRecordCreation($data);
    }
}
