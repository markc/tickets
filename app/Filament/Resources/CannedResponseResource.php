<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CannedResponseResource\Pages;
use App\Models\CannedResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Support Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Response Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief title for this response'),

                        Forms\Components\Select::make('category')
                            ->options(CannedResponse::getCommonCategories())
                            ->searchable()
                            ->placeholder('Select a category')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Category name'),
                            ])
                            ->createOptionUsing(function (array $data): string {
                                return $data['name'];
                            }),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(8)
                            ->placeholder('Response content with variables like {{customer_name}}, {{ticket_id}}, etc.')
                            ->helperText('Available variables: {{customer_name}}, {{customer_email}}, {{ticket_id}}, {{ticket_subject}}, {{agent_name}}, {{company_name}}, {{current_date}}, {{current_time}}'),
                    ])->columns(1),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public (accessible by all agents)')
                            ->default(false)
                            ->helperText('If disabled, only you can use this response'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created by')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Last used')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(CannedResponse::getCommonCategories()),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public responses'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active responses'),

                SelectFilter::make('user_id')
                    ->label('Created by')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Response Preview')
                    ->modalContent(fn (CannedResponse $record): string => view('filament.canned-response-preview', ['response' => $record])->render()
                    ),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('usage_count', 'desc');
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
            'index' => Pages\ListCannedResponses::route('/'),
            'create' => Pages\CreateCannedResponse::route('/create'),
            'view' => Pages\ViewCannedResponse::route('/{record}'),
            'edit' => Pages\EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
