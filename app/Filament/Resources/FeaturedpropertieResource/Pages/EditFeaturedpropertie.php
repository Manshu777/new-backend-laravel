<?php

namespace App\Filament\Resources\FeaturedpropertieResource\Pages;

use App\Filament\Resources\FeaturedpropertieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedpropertie extends EditRecord
{
    protected static string $resource = FeaturedpropertieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
