<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Template Preview')
                ->modalContent(function () {
                    $preview = $this->record->getPreview();

                    return view('filament.email-template-preview', [
                        'subject' => $preview['subject'],
                        'content' => $preview['content'],
                        'type' => $this->record->type,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->is_default) {
                        $this->halt();
                        $this->notify('danger', 'Cannot delete default template', 'Default templates cannot be deleted. Please set another template as default first.');

                        return false;
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set the updater
        $data['updated_by_id'] = auth()->id();

        // Ensure only one default template per name/language combination
        if ($data['is_default'] ?? false) {
            EmailTemplate::where('name', $data['name'])
                ->where('language', $data['language'])
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
