<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookedhotelsResource\Pages;
use App\Filament\Resources\BookedhotelsResource\RelationManagers;
use App\Models\Bookedhotels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookedhotelsResource extends Resource
{
    protected static ?string $model = Bookedhotels::class;
    protected static ?string $navigationIcon = 'ri-hotel-line';

    protected static ?string $navigationLabel = 'Hotels Bookings';
    // protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Booking Managment';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\TextInput::make('hotel_name')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('check_in_date')
                    ->required(),
                Forms\Components\DatePicker::make('check_out_date')
                    ->required(),
                Forms\Components\TextInput::make('room_type')
                    ->required()
                    ->maxLength(255),
          


             Forms\Components\TextInput::make('bookingId')
                    ->required()
          ,
                    
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('date_of_booking')
                    ->required(),
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
                Tables\Columns\TextColumn::make('hotel_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pnr')
                    ->date()
                    ->sortable(),

                       // 'bookingId',
        //'pnr',
                Tables\Columns\TextColumn::make('bookingId')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_booking')
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
            'index' => Pages\ListBookedhotels::route('/'),
            'create' => Pages\CreateBookedhotels::route('/create'),
            'view' => Pages\ViewBookedhotels::route('/{record}'),
            'edit' => Pages\EditBookedhotels::route('/{record}/edit'),
        ];
    }
}
