<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookInsurace extends Model

{

        use HasFactory;

    protected $fillable = [
        'booking_id',
        'trace_id',
        'result_index',
        'title',
        'first_name',
        'last_name',
        'beneficiary_title',
        'beneficiary_name',
        'relationship_to_insured',
        'relationship_to_beneficiary',
        'gender',
        'dob',
        'passport_no',
        'phone_number',
        'email',
        'address_line1',
        'city_code',
        'country_code',
        'major_destination',
        'passport_country',
        'pin_code',
        'policy_start_date',
        'policy_end_date',
        'coverage_details',
        'pdf_url',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'coverage_details' => 'array',
    ];
}
