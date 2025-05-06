<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedpropertieResource\Pages;
use App\Filament\Resources\FeaturedpropertieResource\RelationManagers;
use App\Models\Featuredpropertie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;



class FeaturedpropertieResource extends Resource
{
    protected static ?string $model = Featuredpropertie::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label("Title")->required(),

                TextInput::make('city')->label("City")->required(),
                FileUpload::make('image')->label("Image")->required(),

 
                Select::make('rating')->options([
        '1' => '1 Star',
        '2' => '2 Star',
        '3' => '3 Star',
        '4' => '4 Star',
        '5' => '5 Star',])->required()->label("Ratting"),

        
        Select::make('offer_type')->options([
            'Special Discount' => 'Special Discount',
            'Free Cancellation' => 'Free Cancellation',
            'Limited Time Offer' => 'Limited Time Offer',])->required()->label("Ratting"),





            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label("Image"),


                TextColumn::make('title')->label("Title"),
                TextColumn::make('city')->label("City"),

                TextColumn::make('offer_type')->label("Offer Type"),
                TextColumn::make('rating')->label("Ratting"),



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
            'index' => Pages\ListFeaturedproperties::route('/'),
            'create' => Pages\CreateFeaturedpropertie::route('/create'),
            'edit' => Pages\EditFeaturedpropertie::route('/{record}/edit'),
        ];
    }
}
