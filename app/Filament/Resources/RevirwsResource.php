<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevirwsResource\Pages;
use App\Filament\Resources\RevirwsResource\RelationManagers;
use App\Models\Ourempolyes;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RevirwsResource extends Resource
{
    protected static ?string $model = Ourempolyes::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

     protected static?string  $navigationLabel ="Reviews";
    public static function form(Form $form): Form 
    {
        return $form
            ->schema([
                TextInput::make("name")->label("Name"),
                TextInput::make("location")->label("Location"),
                Textarea::make("review")->label("Review"),
                TextInput::make("rating")->label("Rating"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->label("Name"),
                TextColumn::make("location")->label("Location"),
                TextColumn::make("review")->label("Review"),
                TextColumn::make("rating")->label("Rating"),
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
            'index' => Pages\ListRevirws::route('/'),
            'create' => Pages\CreateRevirws::route('/create'),
            'edit' => Pages\EditRevirws::route('/{record}/edit'),
        ];
    }
}
