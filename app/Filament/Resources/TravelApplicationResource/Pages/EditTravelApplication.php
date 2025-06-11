<?php

namespace App\Filament\Resources\TravelApplicationResource\Pages;

use App\Filament\Resources\TravelApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelApplication extends EditRecord
{
    protected static string $resource = TravelApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
