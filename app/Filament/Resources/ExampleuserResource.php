<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExampleuserResource\Pages;
use App\Filament\Resources\ExampleuserResource\RelationManagers;
use App\Models\Exampleuser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn; 
 

class ExampleuserResource extends Resource
{
    protected static ?string $model = Exampleuser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

 
                TextInput::make('country')->label("Manshu "),
                TextInput::make('top_airports')


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country'),
                TextColumn::make('top_airports')

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
            'index' => Pages\ListExampleusers::route('/'),
            'create' => Pages\CreateExampleuser::route('/create'),
            'edit' => Pages\EditExampleuser::route('/{record}/edit'),
        ];
    }
}
