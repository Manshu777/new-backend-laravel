<?php

namespace App\Filament\Hotelreg\Resources\HoteldetailsResource\Pages;

use App\Filament\Hotelreg\Resources\HoteldetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHoteldetails extends CreateRecord
{
    protected static string $resource = HoteldetailsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['hotel_id'] = auth()->user()->id;
        return $data;
        
    }
}
