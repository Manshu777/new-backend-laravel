<?php

namespace App\Filament\Resources\ExampleuserResource\Pages;

use App\Filament\Resources\ExampleuserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExampleuser extends CreateRecord
{
    protected static string $resource = ExampleuserResource::class;
}
