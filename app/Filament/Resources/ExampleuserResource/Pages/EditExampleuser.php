<?php

namespace App\Filament\Resources\ExampleuserResource\Pages;

use App\Filament\Resources\ExampleuserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExampleuser extends EditRecord
{
    protected static string $resource = ExampleuserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
