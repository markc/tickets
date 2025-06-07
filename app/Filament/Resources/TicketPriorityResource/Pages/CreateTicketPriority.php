<?php

namespace App\Filament\Resources\TicketPriorityResource\Pages;

use App\Filament\Resources\TicketPriorityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketPriority extends CreateRecord
{
    protected static string $resource = TicketPriorityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
