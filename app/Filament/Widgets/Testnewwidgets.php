<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class Testnewwidgets extends ChartWidget
{
    protected static ?string $heading = 'Charts';
    protected static ?string $height = '23rem;';

    protected function getData(): array
    {
        return [
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June'],
            'datasets' => [
                [
                    'label' => 'User Registrations',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => [12, 19, 3, 5, 2, 3],
                ],
            ],
        ];

    }

    protected function getType(): string
    {
        return 'bar';
    }
}
