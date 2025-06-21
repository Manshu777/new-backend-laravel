<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_name',
        'username',
        'email',
        'phone_number',
        'message',
    ];
}
