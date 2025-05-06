<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CruiseResource\Pages;
use App\Filament\Resources\CruiseResource\RelationManagers;
use App\Models\Cruise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
 

class CruiseResource extends Resource
{
    protected static ?string $model = Cruise::class;


    protected static ?string $navigationIcon = 'iconpark-cruise-o';

    protected static ?string $navigationLabel = 'Cruise Bookings';
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
            'index' => Pages\ListCruises::route('/'),
            'create' => Pages\CreateCruise::route('/create'),
            'edit' => Pages\EditCruise::route('/{record}/edit'),
        ];
    }
}
