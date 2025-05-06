<?php

namespace App\Filament\Resources\CruiseResource\Pages;

use App\Filament\Resources\CruiseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCruises extends ListRecords
{
    protected static string $resource = CruiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
