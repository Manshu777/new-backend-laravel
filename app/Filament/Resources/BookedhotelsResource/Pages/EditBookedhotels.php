<?php

namespace App\Filament\Resources\BookedhotelsResource\Pages;

use App\Filament\Resources\BookedhotelsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookedhotels extends EditRecord
{
    protected static string $resource = BookedhotelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
