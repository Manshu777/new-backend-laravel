<?php 

namespace App\Filament\Auth;
 
use Filament\Pages\Auth\Login;
use Illuminate\Contracts\Support\Htmlable;
 
class HotelLogin extends Login
{
    public function getHeading(): string|Htmlable
    {
        return __('Hotel Login');
    }
}