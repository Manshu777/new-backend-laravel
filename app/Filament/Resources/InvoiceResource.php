<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use App\Filament\Resources\InvoiceResource\Actions\DownloadInvoiceAction;
use Filament\Tables\Columns\TextColumn;
class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Property Management';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Invoice Details')
                ->schema([
                    TextInput::make('invoice_number')
                        ->required()
                        ->unique(),
                    DatePicker::make('invoice_date')
                        ->required(),
                    DatePicker::make('due_date')
                        ->required(),
                ]),
            
            Section::make('Client Information')
                ->schema([
                    TextInput::make('client_name')
                        ->required(),
                    TextInput::make('client_address')
                        ->required()
                        ->columnSpanFull(),
                ]),
            
            Section::make('Items')
                ->schema([
                    Repeater::make('items')
                        ->schema([
                            TextInput::make('description')
                                ->required(),
                            TextInput::make('unit_cost')
                                ->numeric()
                                ->required(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->required(),
                        ])
                        ->columns(3)
                        ->columnSpanFull()
                ]),
            
            TextInput::make('total_amount')
                ->numeric()
                ->required()
                ->prefix('$')
                ->reactive()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client_name')->label("client_name"),
                TextColumn::make('invoice_number')->label("invoice_number"),
                TextColumn::make('invoice_date')->label("invoice_date"),

                TextColumn::make('client_address')->label("client_address"),
                TextColumn::make('total_amount')->label("total_amount"),




            ]) 
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DownloadInvoiceAction::make(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
