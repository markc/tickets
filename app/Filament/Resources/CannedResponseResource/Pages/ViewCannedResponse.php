<?php

namespace App\Filament\Resources\CannedResponseResource\Pages;

use App\Filament\Resources\CannedResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCannedResponse extends ViewRecord
{
    protected static string $resource = CannedResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
