<?php

namespace App\Filament\Resources\ToppackageResource\Pages;

use App\Filament\Resources\ToppackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListToppackages extends ListRecords
{
    protected static string $resource = ToppackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
