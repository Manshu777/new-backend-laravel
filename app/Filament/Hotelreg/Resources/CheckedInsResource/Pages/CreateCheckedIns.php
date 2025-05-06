<?php

namespace App\Filament\Hotelreg\Resources\CheckedInsResource\Pages;

use App\Filament\Hotelreg\Resources\CheckedInsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckedIns extends CreateRecord
{
    protected static string $resource = CheckedInsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['hotel_id'] = auth()->user()->id;
        return $data;
    }
}
