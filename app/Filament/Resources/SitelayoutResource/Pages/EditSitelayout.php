<?php

namespace App\Filament\Resources\SitelayoutResource\Pages;

use App\Filament\Resources\SitelayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSitelayout extends EditRecord
{
    protected static string $resource = SitelayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
