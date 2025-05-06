<?php

namespace App\Filament\Resources\PopularHotelsResource\Pages;

use App\Filament\Resources\PopularHotelsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPopularHotels extends EditRecord
{
    protected static string $resource = PopularHotelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
