<?php

namespace App\Filament\Hotelreg\Resources\HotelmeeResource\Widgets;

use Filament\Widgets\ChartWidget;

class HotelBoo extends ChartWidget
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
