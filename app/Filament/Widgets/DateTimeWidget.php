<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DateTimeWidget extends Widget
{
    protected static string $view = 'filament.widgets.date-time-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function getViewData(): array
    {
        $now = now();

        return [
            'time' => $now->format('g:i A'), // 12-hour format with AM/PM
            'day' => $now->format('l'), // Full day name (Monday, Tuesday, etc.)
            'date' => $now->format('F j, Y'), // Month Day, Year (January 15, 2024)
        ];
    }
}
