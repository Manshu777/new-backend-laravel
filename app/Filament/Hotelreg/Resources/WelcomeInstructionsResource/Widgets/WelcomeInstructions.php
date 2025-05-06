<?php
namespace App\Filament\Hotelreg\Resources\WelcomeInstructionsResource\Widgets;


use Filament\Widgets\Widget;

class WelcomeInstructions extends Widget
{
    protected static string $view = 'filament.widgets.welcome-instructions'; // Custom blade file

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true; // You can customize visibility here
    }
}