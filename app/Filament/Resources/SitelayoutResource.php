<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SitelayoutResource\Pages;
use App\Filament\Resources\SitelayoutResource\RelationManagers;
use App\Models\Sitelayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;

class SitelayoutResource extends Resource
{
    protected static ?string $model = Sitelayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('banner_image')->label("Slider Images")->image()
                ->multiple(),

                FileUpload::make('image1')->label("Images 1")->image(),


                FileUpload::make('image2')->label("Images 2")->image()
                ->multiple()
    ,


                FileUpload::make('image3')->label("Images 3")->image()
                ->multiple()
                

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('banner_image'),
                ImageColumn::make('image1'),

                ImageColumn::make('image2'),

                ImageColumn::make('image3'),


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
            'index' => Pages\ListSitelayouts::route('/'),
            'create' => Pages\CreateSitelayout::route('/create'),
            'edit' => Pages\EditSitelayout::route('/{record}/edit'),
        ];
    }
}
