<?php

namespace App\Filament\Resources\BlogcategoryResource\Pages;

use App\Filament\Resources\BlogcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogcategory extends EditRecord
{
    protected static string $resource = BlogcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
