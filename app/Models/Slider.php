<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;


class Slider extends Model
{
    use HasFactory;

    protected $fillable = ['slider_img', 'link', 'img2', 'img3', 'img4', 'img_links'];

    protected $casts = [
        'slider_img' => 'json', 
        'link' => 'json',      
        'img_links' => 'json',
    ];
}
