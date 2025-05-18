<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusBookingResource\Pages;
use App\Filament\Resources\BusBookingResource\RelationManagers;
use App\Models\BusBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusBookingResource extends Resource
{
    protected static ?string $model = BusBooking::class;
    protected static ?string $navigationLabel = 'Bus Bookings';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
     protected static ?string $navigationGroup = 'Booking Managment';
    public static function form(Form $form): Form
    {
        return $form
           ->schema([
                Forms\Components\TextInput::make('trace_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('booking_status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('invoice_amount')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bus_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ticket_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('travel_operator_pnr')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('passenger_details')
                    ->required()
                    ->json()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
         ->columns([
                Tables\Columns\TextColumn::make('trace_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('invoice_amount')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bus_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('travel_operator_pnr')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('passenger_details')
                    ->formatStateUsing(function ($state) {
                        $details = json_decode($state, true);
                        return collect($details)->map(function ($passenger) {
                            return "Name: {$passenger['name']}, Email: {$passenger['email']}, Phone: {$passenger['phone']}";
                        })->implode('; ');
                    })
                    ->wrap()
                    ->searchable(),
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
                //
            ])
            ->actions([
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
            'index' => Pages\ListBusBookings::route('/'),
            'create' => Pages\CreateBusBooking::route('/create'),
            'edit' => Pages\EditBusBooking::route('/{record}/edit'),
        ];
    }
}
