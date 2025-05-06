<?php

namespace App\Filament\Hotelreg\Resources\AmenitiesResource\Pages;

use App\Filament\Hotelreg\Resources\AmenitiesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAmenities extends EditRecord
{
    protected static string $resource = AmenitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
