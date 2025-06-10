<?php

namespace App\Filament\Resources\DocumentationResource\Pages;

use App\Filament\Resources\DocumentationResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewDocumentation extends ViewRecord
{
    protected static string $resource = DocumentationResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('title')
                            ->hiddenLabel()
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->size('md')
                            ->color('gray')
                            ->visible(fn ($record) => $record->description),

                        TextEntry::make('metadata')
                            ->hiddenLabel()
                            ->formatStateUsing(function ($record) {
                                $parts = [];
                                if ($record->category) {
                                    $parts[] = ucfirst($record->category);
                                }
                                if ($record->version) {
                                    $parts[] = "v{$record->version}";
                                }
                                if ($record->last_updated) {
                                    $parts[] = 'Updated '.$record->last_updated->format('M j, Y');
                                } else {
                                    $parts[] = 'Updated '.$record->updated_at->format('M j, Y');
                                }
                                if ($record->updatedBy) {
                                    $parts[] = "by {$record->updatedBy->name}";
                                }

                                return implode(' • ', $parts);
                            })
                            ->color('gray')
                            ->size('sm'),
                    ])
                    ->columnSpan('full'),

                Section::make('Content')
                    ->schema([
                        TextEntry::make('rendered_content')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
