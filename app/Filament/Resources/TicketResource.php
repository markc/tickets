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

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isAgent() ?? false;
    }

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
                            ->relationship('office', 'name', fn (Builder $query) => 
                                auth()->user()->role === 'agent' 
                                    ? $query->whereIn('offices.id', auth()->user()->offices()->pluck('offices.id'))
                                    : $query
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('ticket_priority_id')
                            ->label('Priority')
                            ->options(function () {
                                return \App\Models\TicketPriority::all()
                                    ->sortBy(fn ($priority) => match($priority->name) {
                                        'Low' => 1,
                                        'Medium' => 2,
                                        'High' => 3,
                                        'Urgent' => 4,
                                        default => 5
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->preload()
                            ->default(fn () => \App\Models\TicketPriority::where('name', 'Low')->first()?->id),
                        Forms\Components\Select::make('ticket_status_id')
                            ->label('Status')
                            ->relationship('status', 'name')
                            ->required()
                            ->preload()
                            ->default(fn () => TicketStatus::where('name', 'Open')->first()?->id),
                        Forms\Components\Select::make('assigned_to_id')
                            ->label('Assigned To')
                            ->options(function (?Ticket $record, $get) {
                                $officeId = $get('office_id') ?? ($record?->office_id);
                                
                                $query = \App\Models\User::where('role', 'agent');
                                
                                // If office is selected, filter agents by that office
                                if ($officeId) {
                                    $query->whereHas('offices', function ($q) use ($officeId) {
                                        $q->where('offices.id', $officeId);
                                    });
                                }
                                
                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->default(fn () => auth()->user()->isAgent() ? auth()->user()->id : null),
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
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(24)
                    ->tooltip(fn ($record) => $record->subject)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Unassigned')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('priority.name')
                    ->label('Priority')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->color(fn (string $state): string => match ($state) {
                        'Low' => 'gray',
                        'Medium' => 'info',
                        'High' => 'warning',
                        'Urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->color(fn (string $state): string => match ($state) {
                        'Open' => 'info',
                        'In Progress' => 'warning',
                        'On Hold' => 'warning',
                        'Closed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->relationship('status', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('ticket_priority_id')
                    ->label('Priority')
                    ->relationship('priority', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('assigned_to_id')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('creator_id')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(''),
                Tables\Actions\EditAction::make()
                    ->label(''),
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
            ->defaultSort('updated_at', 'desc');
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
