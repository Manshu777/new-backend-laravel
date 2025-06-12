<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeddingExperiences extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country',
        'city',
        'nationality',
        'age',
        'gender',
        'experience_reason',
        'attended_before',
        'interests',
        'available_months',
        'region_preference',
        'stay_duration',
        'travel_arranged',
        'need_assistance',
        'paid_experience',
        'budget',
        'consent',
        'promo_consent',
    ];

    protected $casts = [
        'interests' => 'array',
        'available_months' => 'array',
        'consent' => 'boolean',
        'promo_consent' => 'boolean',
    ];
}