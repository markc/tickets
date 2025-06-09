<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the creator
        $data['created_by_id'] = auth()->id();
        $data['updated_by_id'] = auth()->id();

        // Ensure only one default template per name/language combination
        if ($data['is_default'] ?? false) {
            EmailTemplate::where('name', $data['name'])
                ->where('language', $data['language'])
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
