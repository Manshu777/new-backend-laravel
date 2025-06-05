<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookflightsResource\Pages;
use App\Filament\Resources\BookflightsResource\RelationManagers;
use App\Models\Bookflights;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
class BookflightsResource extends Resource
{
    protected static ?string $model = Bookflights::class;

    protected static ?string $navigationIcon = 'hugeicons-airplane-take-off-01';

    protected static ?string $navigationLabel = 'Flight Bookings';
    // protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Booking Managment';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('user_name')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('user_number')
                    ->maxLength(255)
                    ->nullable(), // Aligned with database schema
                Forms\Components\TextInput::make('flight_name')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('flight_number')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('departure_from')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('arrival_to')
                    ->maxLength(255)
                    ->nullable(),
               
                Forms\Components\DateTimePicker::make('date_of_booking')
                    ->required(),
                Forms\Components\DateTimePicker::make('return_date')
                    ->nullable(),
                Forms\Components\TextInput::make('token')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('trace_id')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('user_ip')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('pnr')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('booking_id')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('username')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('phone_number')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('pdf_path')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('airline_code')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\DateTimePicker::make('departure_time')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('arrival_time')
                    ->nullable(),
                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('fare')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('currency')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('commission_earned')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Textarea::make('segments')
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\DateTimePicker::make('cancelled_at')
                    ->disabled()
                    ->nullable(),
                Forms\Components\Textarea::make('cancellation_remarks')
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\TextInput::make('refund_amount')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('refund_status')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\DateTimePicker::make('refund_initiated_at')
                    ->disabled()
                    ->nullable(),
                Forms\Components\Toggle::make('refund')
                    ->required(),
                Forms\Components\Textarea::make('initial_response')
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Textarea::make('response')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label('User ID')
                    ->numeric()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_name')
                    ->label('Passenger Name')
                    ->searchable(),
                TextColumn::make('user_number')
                    ->label('Contact Number')
                    ->searchable(),
                TextColumn::make('pnr')
                    ->label('PNR Number')
                    ->searchable(),
                TextColumn::make('booking_id')
                    ->label('Booking ID')
                    ->searchable(),
                TextColumn::make('flight_name')
                    ->label('Flight Name')
                    ->searchable(),
                TextColumn::make('flight_number')
                    ->label('Flight Number')
                    ->searchable(),
                TextColumn::make('airline_code')
                    ->label('Airline Code')
                    ->searchable(),
                TextColumn::make('departure_from')
                    ->label('Departure City')
                    ->searchable(),
                TextColumn::make('arrival_to')
                    ->label('Arrival City')
                    ->searchable(),
                TextColumn::make('flight_date')
                    ->label('Flight Date')
                    ->dateTime('d-M-Y H:i')
                    ->sortable(),
                TextColumn::make('departure_time')
                    ->label('Departure Time')
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('arrival_time')
                    ->label('Arrival Time')
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duration (min)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' min' : '-')
                    ->sortable(),
                TextColumn::make('fare')
                    ->label('Fare')
                    ->money('currency', true)
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable(),
                TextColumn::make('commission_earned')
                    ->label('Commission Earned')
                    ->money('INR', true)
                    ->sortable(),
                TextColumn::make('ticket_status')
                    ->label('Ticket Status')
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? 'unknown'))
                    ->colors([
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->label('Cancelled At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cancellation_remarks')
                    ->label('Cancellation Remarks')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->money('INR', true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('refund_status')
                    ->label('Refund Status')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('refund_initiated_at')
                    ->label('Refund Initiated At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_of_booking')
                    ->label('Booking Date')
                    ->dateTime('d-M-Y H:i')
                    ->sortable(),
                TextColumn::make('initial_response')
                    ->label('Initial Response')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('response')
                    ->label('Response')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('refund')
                    ->label('Refund')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),
                TextColumn::make('pdf_path')
                    ->label('PDF Path')
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : '-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('segments')
                    ->label('Segments')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                    ->limit(50)
                    ->tooltip(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBookflights::route('/'),
            'create' => Pages\CreateBookflights::route('/create'),
            'view' => Pages\ViewBookflights::route('/{record}'),
            'edit' => Pages\EditBookflights::route('/{record}/edit'),
        ];
    }
}
