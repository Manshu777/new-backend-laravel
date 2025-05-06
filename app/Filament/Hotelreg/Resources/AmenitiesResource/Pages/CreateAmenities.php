<?php

namespace App\Filament\Hotelreg\Resources\AmenitiesResource\Pages;

use App\Filament\Hotelreg\Resources\AmenitiesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAmenities extends CreateRecord
{
    protected static string $resource = AmenitiesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['hotel_details_id'] = auth()->user()->id;
        return $data;
    }
}
