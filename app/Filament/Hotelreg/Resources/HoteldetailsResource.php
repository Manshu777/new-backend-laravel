<?php

namespace App\Filament\Hotelreg\Resources;

use App\Filament\Hotelreg\Resources\HoteldetailsResource\Pages;
use App\Filament\Hotelreg\Resources\HoteldetailsResource\RelationManagers;
use App\Models\hoteldetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tab;

use Filament\Tables\Columns\ImageColumn;

use Filament\Tables\Columns\TextColumn;


class HoteldetailsResource extends Resource
{
    protected static ?string $model = Hoteldetails::class;
    protected static ?string $navigationLabel = 'Hotel Registration';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
      protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Hotel Registration Management';

    public static function getModelLabel(): string
    {
        return 'Hotel Registration';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hotel Registrations';
    }


    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
      
        ->where('hotel_id', auth()->id());
}

 
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Basic Information')
            ->schema([
                Forms\Components\TextInput::make('property_name')->label('Property Name')->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('slug', \Str::slug($state));
                }),
            
            Forms\Components\TextInput::make('slug')
                ->disabled()
                ->dehydrated()
                ->required(),
                Forms\Components\RichEditor::make('hotel_des')->label('Hotel Description')->required(),
                Forms\Components\FileUpload::make('hotel_img')
                    ->label('Hotel Images')
                    ->disk('s3')
                    ->directory('blog-image')
                    ->multiple()
                    ->required(),
                Forms\Components\Select::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ])
                    ->label('Hotel Rating')->required(),
                Forms\Components\DatePicker::make('built_year')->label('Year Built')->required(),
                Forms\Components\TextInput::make('price')->label("Price")->required(),
                Forms\Components\DatePicker::make('accepting_since')->label('Accepting Since')->required(),
            ])->columns(2),

        Forms\Components\Section::make('Contact Information')
            ->schema([
                Forms\Components\TextInput::make('email')->label('Email')->required(),
                Forms\Components\TextInput::make('number')->label('Phone Number')->required(),
                Forms\Components\TextInput::make('land_line')->label("Land Line"),
            ])->columns(2),

        Forms\Components\Section::make('Address Information')
            ->schema([
                Forms\Components\RichEditor::make('address')->label('Full Address')->required(),
                Forms\Components\TextInput::make('country')->label("Country")->required(),
                Forms\Components\TextInput::make('state')->label("State")->required(),
                Forms\Components\TextInput::make('city')->label("City")->required(),
                Forms\Components\TextInput::make('locality')->label("Locality")->required(),
                Forms\Components\TextInput::make('house_no')->label("House No")->required(),
                Forms\Components\TextInput::make('pincode')->label("Pincode")->required(),
            ])->columns(2),

        Forms\Components\Section::make('Terms & Conditions')
            ->schema([
                Forms\Components\RichEditor::make('terms')->label('Terms & Conditions')->required(),
            ]),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hotel_img')->disk('s3'),
                TextColumn::make('property_name'),
                TextColumn::make('rating'),
                TextColumn::make('email'),
                TextColumn::make('number'),
                TextColumn::make('rating'),
                TextColumn::make('built_year'),
                TextColumn::make('accepting_since'),
                TextColumn::make('country'),
                TextColumn::make('state'),
                TextColumn::make('city'),

                TextColumn::make('locality'),
                TextColumn::make('house_no'),
                TextColumn::make('pincode'),

                TextColumn::make('hotel_des'),
                TextColumn::make('terms'),
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
