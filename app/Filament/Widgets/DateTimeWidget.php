<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DateTimeWidget extends Widget
{
    protected static string $view = 'filament.widgets.date-time-widget';

    protected int | string | array $columnSpan = [
        'md' => 1,
        'lg' => 1,
    ];

    protected static ?int $sort = -2;

    public function getViewData(): array
    {
        $now = now();

        return [
            'time' => $now->format('g:i A'),
            'day' => $now->format('l'),
            'date' => $now->format('F j, Y'),
        ];
    }
}