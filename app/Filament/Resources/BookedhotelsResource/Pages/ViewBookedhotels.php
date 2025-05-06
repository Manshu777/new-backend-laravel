<?php

namespace App\Filament\Resources\BookedhotelsResource\Pages;

use App\Filament\Resources\BookedhotelsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookedhotels extends ViewRecord
{
    protected static string $resource = BookedhotelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
