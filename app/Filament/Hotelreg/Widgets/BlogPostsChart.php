<?php

namespace App\Filament\Hotelreg\Widgets;

use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June'],
            'datasets' => [
                [
                    'label' => 'User Registrations',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'data' => [10, 15, 8, 20, 25, 18],
                    'fill' => true, 
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
