<?php

namespace App\Filament\Resources\BookedhotelsResource\Pages;

use App\Filament\Resources\BookedhotelsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookedhotels extends ListRecords
{
    protected static string $resource = BookedhotelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
