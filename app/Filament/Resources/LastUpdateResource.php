<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LastUpdateResource\Pages;
use App\Filament\Resources\LastUpdateResource\RelationManagers;
use App\Models\LastUpdate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class LastUpdateResource extends Resource
{
    protected static ?string $model = LastUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               TextInput::make('title'),
                FileUpload::make('image'),
                RichEditor::make('des') ]);
                
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make("image"),
                TextColumn::make('title'),
                TextColumn::make('des')
            ])
            ->filters([
                
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
            'index' => Pages\ListLastUpdates::route('/'),
            'create' => Pages\CreateLastUpdate::route('/create'),
            'edit' => Pages\EditLastUpdate::route('/{record}/edit'),
        ];
    }
}
