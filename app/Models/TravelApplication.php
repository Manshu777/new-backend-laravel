<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelApplication extends Model
{
    protected $fillable = [
        'tentative_departure_date',
        'tentative_return_date',
        'full_name',
        'date_of_birth',
        'gender',
        'travel_purpose',
        'email',
        'phone',
        'passport_number',
        'given_name',
        'surname',
        'place_of_birth',
        'passport_front_path',
        'passport_back_path',
        'photograph_path',
        'supporting_document_path',
        'study_abroad',
    ];

    protected $casts = [
        'tentative_departure_date' => 'date',
        'tentative_return_date' => 'date',
        'date_of_birth' => 'date',
        'study_abroad' => 'boolean',
    ];
}
