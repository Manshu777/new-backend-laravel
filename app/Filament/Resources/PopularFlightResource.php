<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopularFlightResource\Pages;
use App\Filament\Resources\PopularFlightResource\RelationManagers;
use App\Models\PopularDestination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
 

class PopularFlightResource extends Resource
{
    protected static ?string $model = PopularDestination::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('from')->label('From City'),
                TextInput::make('from_code') ->label('AirPort Code'),
                
                TextInput::make('to')->label('To City'),
                TextInput::make('to_code') ->label('AirPort Code'),
                TextInput::make('dis') ->label('Description'),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('from'),
                TextColumn::make('from_code'),
                TextColumn::make('to'),
                TextColumn::make('to_code'),
                TextColumn::make('dis'),
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
            'index' => Pages\ListPopularFlights::route('/'),
            'create' => Pages\CreatePopularFlight::route('/create'),
            'edit' => Pages\EditPopularFlight::route('/{record}/edit'),
        ];
    }
}
