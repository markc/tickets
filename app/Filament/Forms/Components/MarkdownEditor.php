<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MarkdownEditor extends Field
{
    protected string $view = 'filament.forms.components.markdown-editor';

    protected bool $showPreview = true;

    public function showPreview(bool $show = true): static
    {
        $this->showPreview = $show;

        return $this;
    }

    public function getShowPreview(): bool
    {
        return $this->showPreview;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();
    }
}
