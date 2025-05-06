<?php

namespace App\Filament\Resources\PopularHotelsResource\Pages;

use App\Filament\Resources\PopularHotelsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopularHotels extends ListRecords
{
    protected static string $resource = PopularHotelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
