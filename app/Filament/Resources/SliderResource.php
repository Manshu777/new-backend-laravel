<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource\RelationManagers;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('slider_img')
                ->image()
                ->multiple() // Allows multiple image uploads
                ->label('Slider Images')
                ->required(),

            Forms\Components\Repeater::make('link')
                ->schema([
                    Forms\Components\TextInput::make('url')
                        ->label('Slider Link')
                        ->required(),
                ])
                ->label('Slider Links')
                ->grid(2),

            Forms\Components\FileUpload::make('img2')->image()->label('Image 2'),
            Forms\Components\FileUpload::make('img3')->image()->label('Image 3'),
            Forms\Components\FileUpload::make('img4')->image()->label('Image 4'),

            Forms\Components\Repeater::make('img_links')
                ->schema([
                    Forms\Components\TextInput::make('img2_link')->label('Image 2 Link'),
                    Forms\Components\TextInput::make('img3_link')->label('Image 3 Link'),
                    Forms\Components\TextInput::make('img4_link')->label('Image 4 Link'),
                ])
                ->columns(3)
                ->label('Links for Extra Images'),
        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('slider_img')->label('Slider Images')->size(50),
                Tables\Columns\TextColumn::make('link')->label('Slider Links')->limit(30),
                Tables\Columns\ImageColumn::make('img2')->label('Image 2')->size(50),
                Tables\Columns\ImageColumn::make('img3')->label('Image 3')->size(50),
                Tables\Columns\ImageColumn::make('img4')->label('Image 4')->size(50),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
