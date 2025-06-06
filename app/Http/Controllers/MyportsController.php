<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Myports;
class MyportsController extends Controller
{
    public function searchport(string $name){
       $port = Myports::where('name', 'LIKE', "%{$name}%")
        ->orWhere('city', 'LIKE', "%{$name}%")
        ->orWhere('iata', 'LIKE', "%{$name}%")
        ->orWhere('icao', 'LIKE', "%{$name}%")
        ->get();
    
   
        return $port;
    }
}
