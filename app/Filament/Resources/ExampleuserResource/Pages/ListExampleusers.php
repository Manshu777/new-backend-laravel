<?php

namespace App\Filament\Resources\ExampleuserResource\Pages;

use App\Filament\Resources\ExampleuserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExampleusers extends ListRecords
{
    protected static string $resource = ExampleuserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
