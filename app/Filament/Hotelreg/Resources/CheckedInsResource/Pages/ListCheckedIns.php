<?php

namespace App\Filament\Hotelreg\Resources\CheckedInsResource\Pages;

use App\Filament\Hotelreg\Resources\CheckedInsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckedIns extends ListRecords
{
    protected static string $resource = CheckedInsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
