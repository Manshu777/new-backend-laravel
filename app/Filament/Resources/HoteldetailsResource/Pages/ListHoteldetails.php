<?php

namespace App\Filament\Resources\HoteldetailsResource\Pages;

use App\Filament\Resources\HoteldetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHoteldetails extends ListRecords
{
    protected static string $resource = HoteldetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
