<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelApplicationResource\Pages;
use App\Filament\Resources\TravelApplicationResource\RelationManagers;
use App\Models\TravelApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, DatePicker, Select, FileUpload, Toggle, Textarea};
use Filament\Tables\Columns\{TextColumn, BooleanColumn, DateColumn};
class TravelApplicationResource extends Resource
{
    protected static ?string $model = TravelApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
  

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tentative_departure_date')->required(),
                DatePicker::make('tentative_return_date'),
                TextInput::make('full_name')->required()->maxLength(255),
                DatePicker::make('date_of_birth'),
                Select::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ]),
                TextInput::make('travel_purpose')->maxLength(255),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')->tel()->maxLength(20),
                TextInput::make('passport_number')->maxLength(100),
                TextInput::make('given_name')->maxLength(255),
                TextInput::make('surname')->maxLength(255),
                TextInput::make('place_of_birth')->maxLength(255),

                FileUpload::make('passport_front_path')->directory('travel/passports')->image(),
                FileUpload::make('passport_back_path')->directory('travel/passports')->image(),
                FileUpload::make('photograph_path')->directory('travel/photos')->image(),
                FileUpload::make('supporting_document_path')->directory('travel/docs'),

                Toggle::make('study_abroad')->label('Study Abroad'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('tentative_departure_date'),
                TextColumn::make('tentative_return_date'),
                BooleanColumn::make('study_abroad')->label('Study Abroad'),
                TextColumn::make('passport_number'),
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
            'index' => Pages\ListTravelApplications::route('/'),
            'create' => Pages\CreateTravelApplication::route('/create'),
            'edit' => Pages\EditTravelApplication::route('/{record}/edit'),
        ];
    }
}
