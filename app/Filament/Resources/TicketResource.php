<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Information')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->label('Ticket ID')
                            ->disabled()
                            ->visible(fn (?Ticket $record) => $record !== null),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('office_id')
                            ->label('Office')
                            ->relationship('office', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('ticket_priority_id')
                            ->label('Priority')
                            ->relationship('priority', 'name')
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('ticket_status_id')
                            ->label('Status')
                            ->relationship('status', 'name')
                            ->required()
                            ->preload()
                            ->default(fn () => TicketStatus::where('name', 'Open')->first()?->id),
                        Forms\Components\Select::make('assigned_to_id')
                            ->label('Assigned To')
                            ->relationship('assignedTo', 'name', fn (Builder $query, ?Ticket $record) => $query->whereHas('offices', function ($q) use ($record) {
                                if ($record && $record->office_id) {
                                    $q->where('offices.id', $record->office_id);
                                }
                            })->where('role', 'agent')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('office.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('priority.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Low' => 'gray',
                        'Medium' => 'info',
                        'High' => 'warning',
                        'Urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Open' => 'info',
                        'In Progress' => 'warning',
                        'On Hold' => 'warning',
                        'Closed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->relationship('status', 'name'),
                Tables\Filters\SelectFilter::make('ticket_priority_id')
                    ->label('Priority')
                    ->relationship('priority', 'name'),
                Tables\Filters\SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name'),
                Tables\Filters\SelectFilter::make('assigned_to_id')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('ticket_status_id')
                                ->label('New Status')
                                ->options(TicketStatus::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['ticket_status_id' => $data['ticket_status_id']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('assignTickets')
                        ->label('Assign Tickets')
                        ->icon('heroicon-o-user')
                        ->form([
                            Forms\Components\Select::make('assigned_to_id')
                                ->label('Assign To')
                                ->options(User::where('role', 'agent')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['assigned_to_id' => $data['assigned_to_id']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->role === 'agent') {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return $query;
    }
}
