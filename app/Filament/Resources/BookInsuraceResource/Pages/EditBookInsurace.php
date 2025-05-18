<?php

namespace App\Filament\Resources\BookInsuraceResource\Pages;

use App\Filament\Resources\BookInsuraceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookInsurace extends EditRecord
{
    protected static string $resource = BookInsuraceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
