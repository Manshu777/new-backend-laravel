<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomregResource\Pages;
use App\Filament\Resources\RoomregResource\RelationManagers;
use App\Models\Roomreg;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
class RoomregResource extends Resource
{
    protected static ?string $model = Roomreg::class;

    protected static ?string $navigationLabel = 'Hotel Rooms Available';
    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';
    protected static ?string $navigationGroup = 'Hotel Management';

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
                TextColumn::make('hotel_details_id')
                ->label('Hotel Details ID')
                ->sortable(),

            TextColumn::make('room_type')
                ->label('Room Type')
                ->searchable(),

            TextColumn::make('size')
                ->label('Size')
                ->sortable(),

            TextColumn::make('bed_type')
                ->label('Bed Type')
                ->searchable(),

            TextColumn::make('price')
                ->label('Price')
                ->sortable()
                ->money('USD'), // Displays price in USD format

            TextColumn::make('max_occupancy')
                ->label('Max Occupancy')
                ->sortable(),

            TextColumn::make('room_ava')
                ->label('Room Availability')
                ->sortable(),

            TextColumn::make('features')
                ->label('Features')
                ->limit(50),
                 // Limits long text and wraps

            TextColumn::make('room_des')
                ->label('Room Description')
                ->limit(50)
                ->wrap(),

            TextColumn::make('additional_serv')
                ->label('Additional Services')
                ->limit(50),
               

            TextColumn::make('image')
                ->label('Image')
                ->limit(30), // Limits display of the image string

            TextColumn::make('hotel_reg_new_id')
                ->label('Hotel Reg ID')
                ->sortable(),
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
            'index' => Pages\ListRoomregs::route('/'),
            'create' => Pages\CreateRoomreg::route('/create'),
            'edit' => Pages\EditRoomreg::route('/{record}/edit'),
        ];
    }
}
