<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogcategoryResource\Pages;
use App\Filament\Resources\BlogcategoryResource\RelationManagers;
use App\Models\Blogcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;

use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
class BlogcategoryResource extends Resource
{
    protected static ?string $model = Blogcategory::class;

    protected static ?string $navigationLabel = 'Blog Category Resources';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Website Content';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Category Details')
                ->description('Fill out the details for this blog category')
                ->schema([
                    TextInput::make('name')
                        ->label('Category Name')
                        ->required()
                        ->placeholder('Enter the category name')
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'edit') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    Hidden::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Provide a short description of the category')
                        ->rows(3),

                
                ])
                ->columns(2), // Organize into two columns for better layout
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
            'index' => Pages\ListBlogcategories::route('/'),
            'create' => Pages\CreateBlogcategory::route('/create'),
            'edit' => Pages\EditBlogcategory::route('/{record}/edit'),
        ];
    }
}
