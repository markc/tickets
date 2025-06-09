<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FAQResource\Pages;
use App\Models\FAQ;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FAQResource extends Resource
{
    protected static ?string $model = FAQ::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('answer')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('office_id')
                            ->label('Department')
                            ->options(Office::pluck('name', 'id'))
                            ->placeholder('Leave empty for general FAQs'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->sortable()
                    ->limit(32)
                    ->tooltip(fn ($record) => $record->question)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('answer')
                    ->searchable()
                    ->sortable()
                    ->limit(32)
                    ->tooltip(fn ($record) => $record->answer)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('office.name')
                    ->label('Department')
                    ->badge()
                    ->default('General')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office_id')
                    ->label('Department')
                    ->options(Office::pluck('name', 'id'))
                    ->placeholder('All Departments'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\Filter::make('general_faqs')
                    ->query(fn ($query) => $query->whereNull('office_id'))
                    ->label('General FAQs'),

                Tables\Filters\Filter::make('department_faqs')
                    ->query(fn ($query) => $query->whereNotNull('office_id'))
                    ->label('Department FAQs'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFAQS::route('/'),
            'create' => Pages\CreateFAQ::route('/create'),
            'edit' => Pages\EditFAQ::route('/{record}/edit'),
        ];
    }
}
