<?php

namespace App\Filament\Resources\BookflightsResource\Pages;

use App\Filament\Resources\BookflightsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookflights extends ViewRecord
{
    protected static string $resource = BookflightsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
