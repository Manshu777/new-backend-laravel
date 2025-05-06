<?php

namespace App\Filament\Resources\HoteldetailsResource\Pages;

use App\Filament\Resources\HoteldetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHoteldetails extends EditRecord
{
    protected static string $resource = HoteldetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
