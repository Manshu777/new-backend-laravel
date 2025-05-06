<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PopularDestination;
use App\Models\PopularHotels;

class Popular_destController extends Controller
{
    //
    public function Popular_flight(){
      $info=  PopularDestination::all();
        return $info ;
    }
    public function Popular_hotel(){
        $info=  PopularHotels::all();
          return $info ;
      }
}
