<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\DocumentationResource\Pages;
use App\Models\Documentation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                if ($context === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('URL-friendly version of the title'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Brief description for search and navigation'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'overview' => 'Overview',
                                'user' => 'User Guide',
                                'admin' => 'Administration',
                                'api' => 'API & Technical',
                                'deployment' => 'Deployment',
                                'development' => 'Development',
                            ])
                            ->native(false),

                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(999)
                            ->helperText('Order within category (0 = first)'),

                        Forms\Components\TextInput::make('version')
                            ->default('1.0')
                            ->maxLength(10),

                        Forms\Components\Toggle::make('is_published')
                            ->default(true)
                            ->helperText('Unpublished documents are only visible to admins'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('File Management')
                    ->schema([
                        Forms\Components\TextInput::make('file_path')
                            ->label('File Path')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Path to markdown file (e.g., user/quick-start.md)')
                            ->suffixIcon('heroicon-o-document'),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Content')
                    ->schema([
                        MarkdownEditor::make('content')
                            ->required()
                            ->showPreview()
                            ->helperText('Markdown content with front matter')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'overview' => 'gray',
                        'user' => 'info',
                        'admin' => 'warning',
                        'api' => 'success',
                        'deployment' => 'danger',
                        'development' => 'purple',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published')
                    ->sortable(),

                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Last Updated By')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'overview' => 'Overview',
                        'user' => 'User Guide',
                        'admin' => 'Administration',
                        'api' => 'API & Technical',
                        'deployment' => 'Deployment',
                        'development' => 'Development',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->url(fn (Documentation $record): string => route('documentation.show', $record)),

                Tables\Actions\EditAction::make()
                    ->label(''),

                Tables\Actions\Action::make('sync_from_file')
                    ->label('Sync from File')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Documentation $record) {
                        $filePath = base_path('docs/'.$record->file_path);
                        if (file_exists($filePath)) {
                            $content = file_get_contents($filePath);
                            $record->update(['content' => $content]);

                            // Parse front matter for meta data
                            if (str_starts_with($content, '---')) {
                                $parts = explode('---', $content, 3);
                                if (count($parts) >= 3) {
                                    $frontMatter = trim($parts[1]);
                                    $meta = [];
                                    foreach (explode("\n", $frontMatter) as $line) {
                                        if (str_contains($line, ':')) {
                                            [$key, $value] = explode(':', $line, 2);
                                            $meta[trim($key)] = trim($value, ' "\'');
                                        }
                                    }

                                    // Update model fields from front matter
                                    if (isset($meta['title'])) {
                                        $record->title = $meta['title'];
                                    }
                                    if (isset($meta['description'])) {
                                        $record->description = $meta['description'];
                                    }
                                    if (isset($meta['category'])) {
                                        $record->category = $meta['category'];
                                    }
                                    if (isset($meta['order'])) {
                                        $record->order = (int) $meta['order'];
                                    }
                                    if (isset($meta['version'])) {
                                        $record->version = $meta['version'];
                                    }

                                    $record->save();
                                }
                            }
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Documentation $record) => ! empty($record->file_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('category')
            ->defaultSort('order')
            ->recordUrl(fn (Documentation $record): string => route('documentation.show', $record));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentations::route('/'),
            'create' => Pages\CreateDocumentation::route('/create'),
            'edit' => Pages\EditDocumentation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['createdBy', 'updatedBy']);
    }
}
