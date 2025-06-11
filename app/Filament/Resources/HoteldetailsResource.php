<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HoteldetailsResource\Pages;
use App\Filament\Resources\HoteldetailsResource\RelationManagers;
use App\Models\HotelDetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

use Filament\Tables\Columns\ImageColumn;
// ToggleColumn
class HoteldetailsResource extends Resource
{
    protected static ?string $model = Hoteldetails::class;
    protected static ?string $navigationLabel = 'Hotels Details';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Hotel Management';

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
                ToggleColumn::make('is_active')
                ->label('Status')
                ->onIcon('heroicon-o-check-circle')
                ->offIcon('heroicon-o-x-circle')
                ->sortable(),
                TextColumn::make('property_name')
                ->label('Hotel Name')
                ->searchable(),

                TextColumn::make('hotel_id')
                ->label('User Name'),

            TextColumn::make('hotel_des')
                ->label('Description')
                ->limit(50) 
                ->wrap(),

                ImageColumn::make('hotel_img')
                ->label('Image')
                ->limit(20),

            TextColumn::make('rating')
                ->label('Rating'),

            TextColumn::make('built_year')
                ->label('Built Year'),

            TextColumn::make('accepting_since')
                ->label('Accepting Since')
                ->date(), // Formats as date

            TextColumn::make('email')
                ->label('Email')
                ->searchable(),

            TextColumn::make('number')
                ->label('Number'),

            TextColumn::make('land_line')
                ->label('Land Line'),

            TextColumn::make('address')
                ->label('Address'),

            TextColumn::make('lat')
                ->label('Latitude'),

            TextColumn::make('lang')
                ->label('Longitude'),

            TextColumn::make('house_no')
                ->label('House No'),

            TextColumn::make('locality')
                ->label('Locality'),

            TextColumn::make('pincode')
                ->label('Pincode'),

            TextColumn::make('country')
                ->label('Country'),

            TextColumn::make('state')
                ->label('State'),

            TextColumn::make('city')
                ->label('City'),

            TextColumn::make('terms')
                ->label('Terms')
                ->limit(50)
                ->wrap(),

            TextColumn::make('price')
                ->label('Price')
                ->money('USD'), // Display as money format


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
            'index' => Pages\ListHoteldetails::route('/'),
            'create' => Pages\CreateHoteldetails::route('/create'),
            'edit' => Pages\EditHoteldetails::route('/{record}/edit'),
        ];
    }
}
