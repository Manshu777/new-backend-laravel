<?php

namespace App\Filament\Hotelreg\Resources;

use App\Filament\Hotelreg\Resources\CheckedInsResource\Pages;
use App\Filament\Hotelreg\Resources\CheckedInsResource\RelationManagers;
use App\Models\CheckedIns;
use App\Models\roomreg;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckedInsResource extends Resource
{
    protected static ?string $model = CheckedIns::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?string $navigationGroup = 'Hotel Booking Management';
  

    public static function getModelLabel(): string
    {
        return 'Hotel Booking';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hotel Booking';
    }
    public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
      
        ->where('hotel_id', auth()->id());
}




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('hotel_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('check_in_date')
                    ->required(),
                Forms\Components\DatePicker::make('check_out_date')
                    ->required(),
                Forms\Components\TextInput::make('room_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\DateTimePicker::make('date_of_booking')
                    ->required(),
                Forms\Components\Textarea::make('initial_response')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('refund')
                    ->required(),
                Forms\Components\Textarea::make('response')
                    ->columnSpanFull(),
            ]);  
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListCheckedIns::route('/'),
            'create' => Pages\CreateCheckedIns::route('/create'),
            'edit' => Pages\EditCheckedIns::route('/{record}/edit'),
        ];
    }
}
