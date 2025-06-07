<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DateTimeLocationWidget extends Widget
{
    protected static string $view = 'filament.widgets.date-time-widget';
    
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = -1;
}