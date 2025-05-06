<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;



class apkatripuser extends Model
{
    use HasFactory;

    protected $table="siteusers";
protected $fillable=[
"name",
"email",
"password",
"address",
"statue",
"other_details",
"dob",
"gender",
"marital_status",
"pincode"
];
protected $casts = [
    'other_details' => 'array', // Corrected 'arry' to 'array'
];

// Mutator for password hashing
public function setPasswordAttribute($value)
{
    $this->attributes['password'] = bcrypt($value);
}
public function setNameAttribute($value){
    $this->attributes['name'] = strtolower($value);

}
public function getNameAttribute($value){
    return  ucfirst($value);
}
}

