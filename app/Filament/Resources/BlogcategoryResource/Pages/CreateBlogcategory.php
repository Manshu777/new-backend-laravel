<?php

namespace App\Filament\Resources\BlogcategoryResource\Pages;

use App\Filament\Resources\BlogcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogcategory extends CreateRecord
{
    protected static string $resource = BlogcategoryResource::class;
}
