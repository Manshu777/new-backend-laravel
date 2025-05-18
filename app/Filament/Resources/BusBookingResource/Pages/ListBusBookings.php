<?php

namespace App\Filament\Resources\BusBookingResource\Pages;

use App\Filament\Resources\BusBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusBookings extends ListRecords
{
    protected static string $resource = BusBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
