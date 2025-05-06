<?php

namespace App\Filament\Hotelreg\Resources\CheckedInsResource\Pages;

use App\Filament\Hotelreg\Resources\CheckedInsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckedIns extends EditRecord
{
    protected static string $resource = CheckedInsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
