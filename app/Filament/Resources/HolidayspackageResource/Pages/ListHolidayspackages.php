<?php

namespace App\Filament\Resources\HolidayspackageResource\Pages;

use App\Filament\Resources\HolidayspackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHolidayspackages extends ListRecords
{
    protected static string $resource = HolidayspackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
