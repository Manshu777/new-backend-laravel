<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Str;
use App\Models\Blogcategory;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

  
    protected static ?string $navigationLabel = 'Blog  Resources';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Website Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Add Courses')
                    ->description('Add Content')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('blog_image')
                        ->disk('s3')
                        ->directory('blog-image'),
                        // Select::make('blog_type')
                        // ->options([
                        //     "travel"=>"travel",
                        //     "business"=>"business",
                        //     "economics"=>"economics",
                        //     "vacation"=>"vacation"
                        // ]),
                        Select::make('blog_category_id')
                            ->label('Blog Category')
                            ->options(Blogcategory::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        TextInput::make('blog_title')->required()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'edit') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),
                        Hidden::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('blog_text'),
                        TextInput::make('quotes'),
                        RichEditor::make('des1')->label('Description 1'),
                        RichEditor::make('des2')->label('Description 2'),





                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('blog_image')
                ->disk('s3'),

                TextColumn::make('blog_title'),
                TextColumn::make('blog_text'),
                TextColumn::make('quotes'),
                TextColumn::make('des1')
                    ->limit(20) // Adjust this number based on average word length
                    ->label('Description 1'), // Optional: set a custom label
                TextColumn::make('des2')
                    ->limit(20) // Adjust this number based on average word length
                    ->label('Description 1'), // Optional: set a custom label

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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
