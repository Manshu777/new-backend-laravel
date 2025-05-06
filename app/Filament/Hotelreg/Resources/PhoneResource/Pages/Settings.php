<?php

namespace App\Filament\Hotelreg\Resources\PhoneResource\Pages;

use App\Filament\Hotelreg\Resources\PhoneResource;
use Filament\Resources\Pages\Page;

class Settings extends Page
{
    protected static string $resource = PhoneResource::class;

    protected static string $view = 'filament.hotelreg.resources.phone-resource.pages.settings';
}
