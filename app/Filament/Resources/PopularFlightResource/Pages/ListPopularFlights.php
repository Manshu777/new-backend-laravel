<?php

namespace App\Filament\Resources\PopularFlightResource\Pages;

use App\Filament\Resources\PopularFlightResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopularFlights extends ListRecords
{
    protected static string $resource = PopularFlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
