<?php

namespace App\Filament\Resources\BookflightsResource\Pages;

use App\Filament\Resources\BookflightsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookflights extends EditRecord
{
    protected static string $resource = BookflightsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
