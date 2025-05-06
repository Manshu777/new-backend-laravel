<?php

namespace App\Filament\Hotelreg\Resources;

use App\Filament\Hotelreg\Resources\AmenitiesResource\Pages;
use App\Filament\Hotelreg\Resources\AmenitiesResource\RelationManagers;
use App\Models\Amenities;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Models\HotelDetails; // Import your HotelDetails model
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class AmenitiesResource extends Resource
{
    protected static ?string $model = Amenities::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';


    protected static ?string $navigationGroup = 'Amenities Management';


    public static function getModelLabel(): string
    {
        return 'Amenities Management';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Amenities Management';
    }

  
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                Forms\Components\Select::make('hotel_details_id')
                    ->label('Hotel')
                    ->options(
                        HotelDetails::query()
                            ->pluck('property_name', 'id') 
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->placeholder('Select a Hotel')
                    ->columns(2)
                    ])
                    ->label('Package Rating & Reviews'),

                Tabs::make('Services')
                    ->tabs([
                        Tab::make('Mandatory')
                            ->schema([
                                Forms\Components\CheckboxList::make('mandatory')
                                    ->options([
                                        'Air Conditioning' => 'Air Conditioning',
                                        'Laundry' => 'Laundry',
                                        'Newspaper' => 'Newspaper',
                                        'Parking' => 'Parking',
                                        'Room Service' => 'Room Service',
                                        'Smoke Detector' => 'Smoke Detector',
                                        'Smoking Rooms' => 'Smoking Rooms',
                                        'Swimming Pool' => 'Swimming Pool',
                                        'Wi-Fi' => 'Wi-Fi',
                                        'Lounge' => 'Lounge',
                                        'Reception' => 'Reception',
                                        'Bar' => 'Bar',
                                        'Restaurant' => 'Restaurant',
                                        'Luggage Assistance' => 'Luggage Assistance',
                                        'Wheelchair' => 'Wheelchair',
                                        'Gym/Fitness Center' => 'Gym/Fitness Center',
                                        'CCTV' => 'CCTV',
                                        'Fire Extinguishers' => 'Fire Extinguishers',
                                        'Airport Transfers' => 'Airport Transfers',
                                        'First Aid Services' => 'First Aid Services',
                                    ])
                                    ->label('Mandatory Amenities'),
                            ]),

                        Tab::make('Basic Facilities')
                            ->schema([
                                Forms\Components\CheckboxList::make('basic_facilities')
                                    ->options([
                                        'Elevator/Lift' => 'Elevator/Lift',
                                        'Housekeeping' => 'Housekeeping',
                                        'Kitchen' => 'Kitchen',
                                        'LAN' => 'LAN',
                                        'Power Backup' => 'Power Backup',
                                        'Refrigerator' => 'Refrigerator',
                                        'Umbrellas' => 'Umbrellas',
                                        'Washing Machine' => 'Washing Machine',
                                        'Laundromat' => 'Laundromat',
                                        'EV Charging Station' => 'EV Charging Station',
                                        'Driver’s Accommodation' => 'Driver’s Accommodation',
                                    ])
                                    ->label('Basic Facilities'),
                            ]),

                        Tab::make('General Services')
                            ->schema([
                                Forms\Components\CheckboxList::make('general_services')
                                    ->options([
                                        'Bellboy Services' => 'Bellboy Services',
                                        'Caretaker' => 'Caretaker',
                                        'Concierge' => 'Concierge',
                                        'Multilingual Staff' => 'Multilingual Staff',
                                        'Luggage Storage' => 'Luggage Storage',
                                        'Specially-Abled Assistance' => 'Specially-Abled Assistance',
                                        'Wake-Up Call Service' => 'Wake-Up Call Service',
                                        'Butler Services' => 'Butler Services',
                                        'Doctor on Call' => 'Doctor on Call',
                                        'Medical Center' => 'Medical Center',
                                        'Pool/Beach Towels' => 'Pool/Beach Towels',
                                    ])
                                    ->label('General Services'),
                            ]),
                    ]),

                Tabs::make('Services')
                    ->tabs([
                        Tab::make('Outdoor Activities')
                            ->schema([
                                Forms\Components\CheckboxList::make('outdoor_activities')
                                    ->options([
                                        'Beach' => 'Beach',
                                        'Bonfire' => 'Bonfire',
                                        'Golf' => 'Golf',
                                        'Kayaks' => 'Kayaks',
                                        'Outdoor Games' => 'Outdoor Games',
                                        'Snorkeling' => 'Snorkeling',
                                        'Telescope' => 'Telescope',
                                        'Water Sports' => 'Water Sports',
                                        'Canoeing' => 'Canoeing',
                                        'Skiing' => 'Skiing',
                                        'Jungle Safari' => 'Jungle Safari',
                                        'Cycling' => 'Cycling',
                                    ])
                                    ->label('Outdoor Activities'),
                            ]),
                        Tab::make('Comman Areas')
                            ->schema([
                                Forms\Components\CheckboxList::make('common_areas')
                                    ->options([
                                        'Balcony/Terrace' => 'Balcony/Terrace',
                                        'Fireplace' => 'Fireplace',
                                        'Lawn' => 'Lawn',
                                        'Library' => 'Library',
                                        'Seating Area' => 'Seating Area',
                                        'Sun Deck Verandah' => 'Sun Deck Verandah',
                                        'Jacuzzi' => 'Jacuzzi',
                                        'Living Room' => 'Living Room',
                                        'Outdoor Furniture' => 'Outdoor Furniture',
                                        'Panic Rooms' => 'Panic Rooms',
                                        'Game Room' => 'Game Room',
                                        'Sitout Areas' => 'Sitout Areas',
                                        'Bonfire Pit' => 'Bonfire Pit',
                                    ])
                                    ->label('Common Areas'),
                            ]),
                        Tab::make('Food Drinks')
                            ->schema([
                                Forms\Components\CheckboxList::make('food_drinks')
                                    ->options([
                                        'Restaurant' => 'Restaurant',
                                        'Bar' => 'Bar',
                                        'Coffee Shop' => 'Coffee Shop',
                                        'Barbeque' => 'Barbeque',
                                        'Cafe' => 'Cafe',
                                        'Dining Area' => 'Dining Area',
                                        'Kid\'s Menu' => 'Kid\'s Menu',
                                        'Bakery' => 'Bakery',
                                        'Breakfast Food Options Available' => 'Breakfast Food Options Available',
                                    ])
                                    ->label('Food & Drinks'),
                            ]),
                    ]),

                Tabs::make('Services')
                    ->tabs([
                        Tab::make('Outdoor Activities')
                            ->schema([
                                Forms\Components\CheckboxList::make('health_wellness')
                                    ->options([
                                        'Gym' => 'Gym',
                                        'Spa' => 'Spa',
                                        'Yoga Center' => 'Yoga Center',
                                        'Activity Center' => 'Activity Center',
                                        'Reflexology' => 'Reflexology',
                                        'Meditation Room' => 'Meditation Room',
                                    ])
                                    ->label('Health & Wellness'),
                            ]),
                        Tab::make('Comman Areas')
                            ->schema([
                                Forms\Components\CheckboxList::make('business_center')
                                    ->options([
                                        'Banquet' => 'Banquet',
                                        'Business Center' => 'Business Center',
                                        'Conference Room' => 'Conference Room',
                                        'Photocopying' => 'Photocopying',
                                        'Fax Services' => 'Fax Services',
                                        'Printer' => 'Printer',
                                    ])
                                    ->label('Business and Conference Facilities'),
                            ]),
                        Tab::make('Food Drinks')
                            ->schema([
                                Forms\Components\CheckboxList::make('beauty_spa')
                                    ->options([
                                        'Beauty Treatments' => 'Beauty Treatments',
                                        'Massage' => 'Massage',
                                        'Facial' => 'Facial',
                                        'Manicure/Pedicure' => 'Manicure/Pedicure',
                                        'Aromatherapy' => 'Aromatherapy',
                                        'Hair Salon' => 'Hair Salon',
                                        'Nail Salon' => 'Nail Salon',
                                    ])
                                    ->label('Beauty and Spa'),
                            ]),
                    ]),
                Tabs::make('Services')
                    ->tabs([
                        Tab::make('Outdoor Activities')
                            ->schema([
                                Forms\Components\CheckboxList::make('security')
                                    ->options([
                                        'CCTV Surveillance' => 'CCTV Surveillance',
                                        'Fire Extinguisher' => 'Fire Extinguisher',
                                        '24/7 Security' => '24/7 Security',
                                        'Emergency Exits' => 'Emergency Exits',
                                        'Security Guards' => 'Security Guards',
                                        'Metal Detectors' => 'Metal Detectors',
                                        'First Aid Kit' => 'First Aid Kit',
                                        'Secure Parking' => 'Secure Parking',
                                        'Disaster Management' => 'Disaster Management',
                                    ])
                                    ->label('Security'),
                            ]),
                        Tab::make('Comman Areas')
                            ->schema([
                                Forms\Components\CheckboxList::make('transfers')
                                    ->options([
                                        'Airport Transfer' => 'Airport Transfer',
                                        'Railway Station Transfer' => 'Railway Station Transfer',
                                        'Bus Station Transfer' => 'Bus Station Transfer',
                                        'Car Rental' => 'Car Rental',
                                        'Shuttle Service' => 'Shuttle Service',
                                        'Private Cabs' => 'Private Cabs',
                                        'Bicycle Rental' => 'Bicycle Rental',
                                        'City Transfers' => 'City Transfers',
                                        'Hotel Pickup & Drop' => 'Hotel Pickup & Drop',
                                        'Luxury Transfers' => 'Luxury Transfers',
                                    ])
                                    ->label('Transfers'),
                            ]),
                        Tab::make('Food Drinks')
                            ->schema([
                                Forms\Components\CheckboxList::make('shopping')
                                    ->options([
                                        'Shopping Mall' => 'Shopping Mall',
                                        'Souvenir Shops' => 'Souvenir Shops',
                                        'Local Markets' => 'Local Markets',
                                        'Duty-Free Shops' => 'Duty-Free Shops',
                                        'Luxury Boutiques' => 'Luxury Boutiques',
                                        'Art & Craft Stores' => 'Art & Craft Stores',
                                        'Street Shopping' => 'Street Shopping',
                                        'Bookstores' => 'Bookstores',
                                        'Fashion Outlets' => 'Fashion Outlets',
                                    ])
                                    ->label('Shopping Options'),
                            ]),
                    ]),

                Tabs::make('Services')
                    ->tabs([
                        Tab::make('Outdoor Activities')
                            ->schema([
                                Forms\Components\CheckboxList::make('payment_services')
                                    ->options([
                                        'Credit Card' => 'Credit Card',
                                        'Debit Card' => 'Debit Card',
                                        'UPI' => 'UPI',
                                        'Cash' => 'Cash',
                                        'PayPal' => 'PayPal',
                                        'Mobile Wallets' => 'Mobile Wallets',
                                        'Net Banking' => 'Net Banking',
                                        'Cryptocurrency' => 'Cryptocurrency',
                                    ])
                                    ->label('Payment Options'),
                            ]),
                        Tab::make('Indoor Activities')
                            ->schema([
                                Forms\Components\CheckboxList::make('indoor_activities')
                                    ->options([
                                        'Board Games' => 'Board Games',
                                        'Table Tennis' => 'Table Tennis',
                                        'Billiards' => 'Billiards',
                                        'Chess' => 'Chess',
                                        'Karaoke' => 'Karaoke',
                                        'Reading Lounge' => 'Reading Lounge',
                                        'Virtual Reality Games' => 'Virtual Reality Games',
                                        'Indoor Swimming Pool' => 'Indoor Swimming Pool',
                                    ])
                                    ->label('Indoor Activities'),
                            ]),
                        Tab::make('Family Kids')
                            ->schema([
                                Forms\Components\CheckboxList::make('family_kids')
                                    ->options([
                                        'Kids Play Area' => 'Kids Play Area',
                                        'Babysitting Services' => 'Babysitting Services',
                                        'Family Dining' => 'Family Dining',
                                        'Children’s Pool' => 'Children’s Pool',
                                        'Arcade Games' => 'Arcade Games',
                                        'Storytelling Sessions' => 'Storytelling Sessions',
                                        'Family Movie Nights' => 'Family Movie Nights',
                                        'Parenting Room' => 'Parenting Room',
                                    ])
                                    ->label('Family & Kids'),
                            ]),

                        Tab::make('Pets Essentials')
                            ->schema([
                                Forms\Components\CheckboxList::make('pets_essentials')
                                    ->options([
                                        'Pet-Friendly Rooms' => 'Pet-Friendly Rooms',
                                        'Pet Grooming Services' => 'Pet Grooming Services',
                                        'Pet Food Supplies' => 'Pet Food Supplies',
                                        'Pet Walking Areas' => 'Pet Walking Areas',
                                        'Veterinary Services' => 'Veterinary Services',
                                        'Pet Daycare' => 'Pet Daycare',
                                        'Pet Toys' => 'Pet Toys',
                                        'Pet Waste Bags' => 'Pet Waste Bags',
                                    ])
                                    ->label('Pets Essentials'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hotel.property_name')->label('Hotel'),
                TextColumn::make('mandatory'),
                TextColumn::make('basic_facilities'),
                TextColumn::make('general_services'),
                TextColumn::make('outdoor_activities'),
                TextColumn::make('common_areas'),
                TextColumn::make('food_drinks'),
                TextColumn::make('health_wellness'),
                TextColumn::make('business_center'),
                TextColumn::make('beauty_spa'),
                TextColumn::make('security'),
                TextColumn::make('transfers'),
                TextColumn::make('shopping'),
                TextColumn::make('payment_services'),
                TextColumn::make('indoor_activities'),
                TextColumn::make('family_kids'),
                TextColumn::make('pets_essentials'),
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
            'index' => Pages\ListAmenities::route('/'),
            'create' => Pages\CreateAmenities::route('/create'),
            'edit' => Pages\EditAmenities::route('/{record}/edit'),
        ];
    }
}
