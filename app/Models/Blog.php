<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = [
        "blog_image",
        "blog_type",
        "blog_title",
        "blog_text",
        "quotes",
        "des1",
        "des2",
        "slug",
        'blog_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Blogcategory::class, 'blog_category_id'); // Define the relationship
    }
}
