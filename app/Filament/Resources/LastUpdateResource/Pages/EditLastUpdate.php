<?php

namespace App\Filament\Resources\LastUpdateResource\Pages;

use App\Filament\Resources\LastUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLastUpdate extends EditRecord
{
    protected static string $resource = LastUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
