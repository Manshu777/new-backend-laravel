<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blogcategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'slug', 'status',
    ];
    public function blogs()
    {
        return $this->hasMany(Blog::class, 'blog_category_id'); // Define the inverse relationship
    }
}
