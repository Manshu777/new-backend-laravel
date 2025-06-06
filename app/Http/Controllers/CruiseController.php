<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cruise;



class CruiseController extends Controller
{
    //
    public function sendCruiseMessage(Request $request){
    $validate=$request->validate([
    'name' => 'required|string|max:50',           
    'email' => 'required|email|max:255',           
    'request_date' => 'required|date',             
    'pickup_des' => 'required|string|max:500',     
    'drop_des' => 'required|string|max:500',       
    'booking_date' => 'required|date',            
    'additional_notes' => 'nullable|string|max:1000'
     ]);
$msg=cruise::create($validate);


return response()->json(["success"=>true,"message"=>"Your Request Send Successfully our team Contact your soon"]);

    }
}
