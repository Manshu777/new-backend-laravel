<?php

namespace App\Filament\Hotelreg\Resources\AmenitiesResource\Pages;

use App\Filament\Hotelreg\Resources\AmenitiesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAmenities extends ListRecords
{
    protected static string $resource = AmenitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
