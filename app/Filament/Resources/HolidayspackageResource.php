<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayspackageResource\Pages;
use App\Models\TravelPackage;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;




class HolidayspackageResource extends Resource
{
    protected static ?string $model = TravelPackage::class;

    protected static ?string $navigationIcon = 'fontisto-holiday-village';

    protected static ?string $navigationLabel = 'Holidays Packages';
    // protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Holidays Packages';


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema(
                [
                    TextInput::make('package_name')
                    ->required()
                    ->label('Package Name'),
    
                    Toggle::make('is_active') ->label('Active Package'),

                Select::make('package_Type')
                    ->required()
                    ->label('Package Type')
                    ->options([
                        'adventure' => 'Adventure',
                        'luxury' => 'Luxury',
                        'family' => 'Family',
                        'romantic' => 'Romantic',
                    ])
                    ->placeholder('Select a Package Type'),
    
                Select::make('rating')
                    ->required()
                    ->label('Rating')
                    ->options([
                        "1" => '1 Star',
                        "2" => '2 Star',
                        "3" => '3 Star',
                        "4" => '4 Star',
                        "5" => '5 Star',
                    ])
                    ->placeholder('Rating of your package'),
    
                FileUpload::make('banner_image')
                    ->label('Banner Image')
                    ->image()
                    ->maxSize(1.5 * 1024)
                    ->helperText('Allowed file types: JPG, PNG, GIF'),
    
                TextInput::make('country')
                    ->required()
                    ->label('Country'),
    
                TextInput::make('state')
                    ->required()
                    ->label('State'),
    
                TextInput::make('city')
                    ->required()
                    ->label('City'),
    
                TextInput::make('duration')
                    ->required()
                    ->label('Duration Only in days')
                    ->numeric()
                    ->type('number'),
    
                RichEditor::make('des')
                    ->label('Description')
                    ->placeholder('Enter description about Package')
                    ->helperText('Example: Private Pool Villa, Couple Spa Session'),
    
                TextInput::make('price')
                    ->required()
                    ->label('Price'),
    
                FileUpload::make('images')
                    ->label('Package Images')
                    ->image()
                    ->multiple()
                    ->maxSize(1.5 * 1024)
                    ->helperText('Allowed file types: JPG, PNG, GIF'),
    
                Repeater::make('activite')
                    ->schema([
                        Select::make('day')
                            ->options([
                                '1' => 'Day 1',
                                '2' => 'Day 2',
                                '3' => 'Day 3',
                                '4' => 'Day 4',
                                '5' => 'Day 5',
                            ])
                            ->required(),
                        TextInput::make('activitie')
                            ->required(),
                    ])
                    ->columns(2),
    
                Repeater::make('terms')
                    ->schema([
                        TextInput::make('terms')->label("Terms & conditions")->required(),
                       
                    ]), 
                    Select::make('emoji')
    ->options([
        'beach' => 'Beach',
        'mountain' => 'Mountain',
        'forest' => 'Forest',
        'desert' => 'Desert',
    ])->required(),
                    


                    // TextInput::make('about')
                    // ->required(),         
                            
    
                         
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ToggleColumn::make('is_active')->label('Active Package'),
                TextColumn::make('package_name')->label('Package Name')->searchable(),
                TextColumn::make('package_Type')->label('Package Name')->searchable(),
                ImageColumn::make('banner_image'),
                TextColumn::make('country'),
                TextColumn::make('state'),
                TextColumn::make('city'),
                TextColumn::make('des')->label('Description'),
                TextColumn::make('price'),
                TextColumn::make('activite'),
                TextColumn::make('terms'),
                TextColumn::make('duration')->label('Duration'),
                TextColumn::make('rating')->label('Rating')->sortable(),

                



                TextColumn::make(name: 'location')->label('Location'),
                
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidayspackages::route('/'),
            'create' => Pages\CreateHolidayspackage::route('/create'),
            'view' => Pages\ViewHolidayspackage::route('/{record}'),
            'edit' => Pages\EditHolidayspackage::route('/{record}/edit'),
        ];
    }
}
