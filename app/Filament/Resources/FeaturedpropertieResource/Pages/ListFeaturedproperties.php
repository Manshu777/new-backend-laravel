<?php

namespace App\Filament\Resources\FeaturedpropertieResource\Pages;

use App\Filament\Resources\FeaturedpropertieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedproperties extends ListRecords
{
    protected static string $resource = FeaturedpropertieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
