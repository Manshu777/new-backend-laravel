<?php

namespace App\Filament\Resources\HolidayspackageResource\Pages;

use App\Filament\Resources\HolidayspackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHolidayspackage extends EditRecord
{
    protected static string $resource = HolidayspackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
