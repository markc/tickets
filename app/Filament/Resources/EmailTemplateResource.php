<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Email Templates';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Unique identifier for the template (e.g., ticket_created, ticket_reply)')
                                    ->placeholder('e.g., ticket_created'),

                                Forms\Components\Select::make('category')
                                    ->required()
                                    ->options(EmailTemplate::getCategories())
                                    ->default('general'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options(EmailTemplate::getTypes())
                                    ->default('markdown'),

                                Forms\Components\Select::make('language')
                                    ->required()
                                    ->options([
                                        'en' => 'English',
                                        'es' => 'Spanish',
                                        'fr' => 'French',
                                        'de' => 'German',
                                    ])
                                    ->default('en'),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_default')
                                            ->label('Default Template')
                                            ->helperText('Only one template per name/language can be default'),
                                    ]),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(2)
                            ->helperText('Describe when this template is used'),
                    ]),

                Forms\Components\Section::make('Email Content')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Use {{variable_name}} for dynamic content')
                            ->placeholder('e.g., [Ticket #{{ticket_id}}] {{ticket_subject}}'),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(15)
                            ->helperText('Use {{variable_name}} for dynamic content. Supports Markdown if type is set to Markdown.')
                            ->placeholder('Template content with {{variables}}...'),
                    ]),

                Forms\Components\Section::make('Available Variables')
                    ->schema([
                        Forms\Components\Placeholder::make('variables_help')
                            ->label('')
                            ->content(function () {
                                $variables = EmailTemplate::getAvailableVariables();
                                $content = "Available variables for use in subject and content:\n\n";

                                foreach ($variables as $var => $description) {
                                    $content .= '• {{'.$var.'}} - '.$description."\n";
                                }

                                return $content;
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'ticket',
                        'secondary' => 'general',
                        'warning' => 'account',
                        'danger' => 'system',
                        'success' => 'marketing',
                    ]),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'markdown',
                        'warning' => 'html',
                        'secondary' => 'plain',
                    ]),

                Tables\Columns\TextColumn::make('language')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(EmailTemplate::getCategories()),

                Tables\Filters\SelectFilter::make('type')
                    ->options(EmailTemplate::getTypes()),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),

                Tables\Filters\Filter::make('is_default')
                    ->query(fn (Builder $query): Builder => $query->where('is_default', true))
                    ->label('Default Templates'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Template Preview')
                    ->modalContent(function (EmailTemplate $record) {
                        $preview = $record->getPreview();

                        return view('filament.email-template-preview', [
                            'subject' => $preview['subject'],
                            'content' => $preview['content'],
                            'type' => $record->type,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function (EmailTemplate $record) {
                        $newTemplate = $record->replicate();
                        $newTemplate->name = $record->name.'_copy';
                        $newTemplate->is_default = false;
                        $newTemplate->save();

                        Notification::make()
                            ->title('Template duplicated')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->before(function (EmailTemplate $record) {
                        if ($record->is_default) {
                            Notification::make()
                                ->title('Cannot delete default template')
                                ->body('Default templates cannot be deleted. Please set another template as default first.')
                                ->danger()
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title('Templates activated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title('Templates deactivated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $defaultTemplates = $records->filter(fn ($record) => $record->is_default);
                            if ($defaultTemplates->count() > 0) {
                                Notification::make()
                                    ->title('Cannot delete default templates')
                                    ->body('Some selected templates are marked as default and cannot be deleted.')
                                    ->danger()
                                    ->send();

                                return false;
                            }
                        }),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
