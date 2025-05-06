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
                    ->numeric(),
                Forms\Components\TextInput::make('user_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('flight_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('flight_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('departure_from')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('arrival_to')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('flight_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('date_of_booking')
                    ->required(),
                Forms\Components\DateTimePicker::make('return_date'),
                Forms\Components\Textarea::make('initial_response')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('refund')
                    ->required(),
                Forms\Components\Textarea::make('response')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departure_from')
                    ->searchable(),
                Tables\Columns\TextColumn::make('arrival_to')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_booking')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('refund')
                    ->boolean(),
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
