<?php

namespace App\Filament\Hotelreg\Resources\RoomregResource\Pages;

use App\Filament\Hotelreg\Resources\RoomregResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRoomreg extends CreateRecord
{
    protected static string $resource = RoomregResource::class;

    /**
     * Mutate form data before creating the record.
     *
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure that `hotel_details_id` is properly assigned to the authenticated user's hotel.
        $data['hotel_details_id'] = auth()->user()->hotel_details_id; // Change `id` to `hotel_details_id` for accuracy.
        return $data;
    }
}
