<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class amenities extends Model
{
    use HasFactory;

    protected $table = 'amenities';

    protected $fillable = [
        'mandatory',
        'basic_facilities',
        'general_services',
        'outdoor_activities',
        'common_areas',
        'food_drinks',
        'health_wellness',
        'business_center',
        'beauty_spa',
        'security',
        'transfers',
        'shopping',
        'payment_services',
        'indoor_activities',
        'family_kids',
        'pets_essentials',
        'hotel_details_id',
    ];

    protected $casts = [
        'mandatory' => 'array',
        'basic_facilities' => 'array',
        'general_services' => 'array',
        'outdoor_activities' => 'array',
        'common_areas' => 'array',
        'food_drinks' => 'array',
        'health_wellness' => 'array',
        'business_center' => 'array',
        'beauty_spa' => 'array',
        'security' => 'array',
        'transfers' => 'array',
        'shopping' => 'array',
        'payment_services' => 'array',
        'indoor_activities' => 'array',
        'family_kids' => 'array',
        'pets_essentials' => 'array',
    ];

    /**
     * Relationship with Hotel model.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_details_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('hotel_details_id', auth()->user()->id);
            }
        });
    }
}
