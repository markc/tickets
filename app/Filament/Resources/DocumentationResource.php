<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentationResource\Pages;
use App\Models\Documentation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static ?string $slug = 'docs';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Docs';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'secondary' => 'overview',
                        'primary' => 'user',
                        'warning' => 'admin',
                        'success' => 'api',
                        'danger' => 'deployment',
                        'info' => 'development',
                    ]),
                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('category')
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label('Category')
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListDocumentations::route('/'),
            'create' => Pages\CreateDocumentation::route('/create'),
            'view' => Pages\ViewDocumentation::route('/{record}'),
            'edit' => Pages\EditDocumentation::route('/{record}/edit'),
        ];
    }
}
