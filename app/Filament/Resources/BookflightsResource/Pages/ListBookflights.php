<?php

namespace App\Filament\Resources\BookflightsResource\Pages;

use App\Filament\Resources\BookflightsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookflights extends ListRecords
{
    protected static string $resource = BookflightsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
