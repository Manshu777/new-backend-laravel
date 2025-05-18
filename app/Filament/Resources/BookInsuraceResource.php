<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookInsuraceResource\Pages;
use App\Filament\Resources\BookInsuraceResource\RelationManagers;
use App\Models\BookInsurace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookInsuraceResource extends Resource
{
    protected static ?string $model = BookInsurace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Insurance Bookings';
protected static ?string $navigationGroup = 'Booking Managment';
    protected static ?string $modelLabel = 'Insurance Booking';

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\TextInput::make('booking_id')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('trace_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('result_index')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('title')
                    ->options([
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms' => 'Ms',
                        'Miss' => 'Miss',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Select::make('beneficiary_title')
                    ->options([
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms' => 'Ms',
                        'Miss' => 'Miss',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('beneficiary_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('relationship_to_insured')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('relationship_to_beneficiary')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->options([
                        '1' => 'Male',
                        '2' => 'Female',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('dob')
                    ->required()
                    ->maxDate(now()),
                Forms\Components\TextInput::make('passport_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->maxLength(15)
                    ->tel(),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line1')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('city_code')
                    ->required()
                    ->maxLength(3),
                Forms\Components\TextInput::make('country_code')
                    ->required()
                    ->maxLength(3)
                    ->default('IND'),
                Forms\Components\TextInput::make('major_destination')
                    ->required()
                    ->maxLength(255)
                    ->default('INDIA'),
                Forms\Components\TextInput::make('passport_country')
                    ->required()
                    ->maxLength(2),
                Forms\Components\TextInput::make('pin_code')
                    ->required()
                    ->maxLength(10),
                Forms\Components\DatePicker::make('policy_start_date')
                    ->required(),
                Forms\Components\DatePicker::make('policy_end_date')
                    ->required(),
                Forms\Components\Textarea::make('coverage_details')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('pdf_url')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('policy_start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('policy_end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBookInsuraces::route('/'),
            'create' => Pages\CreateBookInsurace::route('/create'),
            'edit' => Pages\EditBookInsurace::route('/{record}/edit'),
        ];
    }
}
