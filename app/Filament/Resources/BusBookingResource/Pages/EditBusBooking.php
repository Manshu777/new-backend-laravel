<?php

namespace App\Filament\Resources\BusBookingResource\Pages;

use App\Filament\Resources\BusBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusBooking extends EditRecord
{
    protected static string $resource = BusBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
