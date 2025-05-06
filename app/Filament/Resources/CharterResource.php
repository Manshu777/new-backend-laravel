<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharterResource\Pages;
use App\Filament\Resources\CharterResource\RelationManagers;
use App\Models\Charter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class CharterResource extends Resource
{
    protected static ?string $model = Charter::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationIcon = 'clarity-plane-solid';

    protected static ?string $navigationLabel = 'Charter Bookings';
    // protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Booking Managment';

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
                //
                TextColumn::make('name'),
                TextColumn::make('email'),

                TextColumn::make('pickup_des'),
                TextColumn::make('drop_des'),
                TextColumn::make('additional_notes'),
                TextColumn::make('booking_date')
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
            'index' => Pages\ListCharters::route('/'),
            'create' => Pages\CreateCharter::route('/create'),
            'edit' => Pages\EditCharter::route('/{record}/edit'),
        ];
    }
}
