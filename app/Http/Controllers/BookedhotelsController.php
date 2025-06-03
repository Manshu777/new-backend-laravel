<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bookedhotels;
use Illuminate\Support\Facades\Validator;
use App\Models\Hotel;
class BookedhotelsController extends Controller
{
    //

    public function bookhotel(Request $request){

        $validate = Validator::make($request->all(), [
            "check_in_date" => "required",
            "check_out_date" => "required",
            "user_name" => "required|string|max:255", 
            "user_number" => "required|string|max:20", 
            "hotel_id"=>"required",
            "room_type" => "required|string|max:50",    
            "price" => "required|numeric|min:0",        
            "date_of_booking" => "required|date",          
            "hotel_name"=>"required|string",          
                        
        
            
            
            // "location"=>"required|string",
            "address"=>"required|string",
            "user_id" => "required|integer", 
        ]);
     
        if($validate->fails()){
            return response()->json(["success"=>false,"message"=>$validate->errors()->all()]);

        }
        $validatedData = $validate->validated();
        
       
        $infodata = Bookedhotels::create([
            "hotel_id" =>(int) $validatedData["hotel_id"],
            "user_id" => (int) $validatedData["user_id"],
            "user_name" => $validatedData["user_name"],
            "user_number" => $validatedData["user_number"],
            "hotel_name" => $validatedData["hotel_name"] ?? null,
            "location" => $validatedData["location"] ?? null,
            "address" => $validatedData["address"] ?? null,
            "check_in_date" => $validatedData["check_in_date"],
            "check_out_date" => $validatedData["check_out_date"],
            "room_type" => $validatedData["room_type"],
            "price" => (int) $validatedData["price"],
            
        ]);

    return response()->json(["success"=>true,"message"=>"Hotel Booked","info"=> $infodata]);

    }





}
