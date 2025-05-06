<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToppackageResource\Pages;
use App\Filament\Resources\ToppackageResource\RelationManagers;
use App\Models\Toppackage;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ToppackageResource extends Resource
{
    protected static ?string $model = Toppackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            TextInput::make("url")->label("URL"),
            TextInput::make("title")->label("Title"),
            TextInput::make("des")->label("Des..."),
            FileUpload::make("img")->label("Image"),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make("img")->label("Image"),
                TextColumn::make("url")->label("URL"),
            TextColumn::make("title")->label("Title"),
            TextColumn::make("des")->label("Des..."),

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
            'index' => Pages\ListToppackages::route('/'),
            'create' => Pages\CreateToppackage::route('/create'),
            'edit' => Pages\EditToppackage::route('/{record}/edit'),
        ];
    }
}
