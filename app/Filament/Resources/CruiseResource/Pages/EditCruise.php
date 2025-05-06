<?php

namespace App\Filament\Resources\CruiseResource\Pages;

use App\Filament\Resources\CruiseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCruise extends EditRecord
{
    protected static string $resource = CruiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
