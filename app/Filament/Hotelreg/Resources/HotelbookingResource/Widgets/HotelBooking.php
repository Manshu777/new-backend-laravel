<?php

namespace App\Filament\Hotelreg\Resources\HotelbookingResource\Widgets;

use Filament\Widgets\ChartWidget;

class HotelBooking extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
