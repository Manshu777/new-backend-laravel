<?php

namespace App\Filament\Resources\RevirwsResource\Pages;

use App\Filament\Resources\RevirwsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevirws extends EditRecord
{
    protected static string $resource = RevirwsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
