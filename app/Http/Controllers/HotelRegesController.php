<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\hoteldetails;
use App\Models\amenities;

use App\Models\roomreg;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use Twilio\Rest\Client;

class HotelRegesController extends Controller
{
    //

 public function sendHotelOtp(Request $req){
    $validated=$req->validate([
        "phone"=>"required",
        "email"=>"required"
    ]);
 $allreadyhotel=Hotel::where("phone",$validated["phone"])->first();
if($allreadyhotel){
    return response()->json(["message"=>"Number allready exist","success"=>false]);
}
$allreadyemail=Hotel::where("email",$validated["email"])->first();
if($allreadyemail){
    return response()->json(["message"=>"Email allready exist","success"=>false]);
}
$otpsend = Http::post('https://otp-verify-service.onrender.com/send-otp', [
    'phone' => $validated['phone'],
]);

return $otpsend;
}


public function  sendVerify(Request $req){
    $validated = $req->validate([
        'name'=>'required|string|max:25',
        'phone'=>'required',
        "otp"=> "required",
        "email"=>"required",
        'password'=>'required|min:6',
         ]);


         $otpsend = Http::post('https://otp-verify-service.onrender.com/verify-otp', [
            'phone' => $validated['phone'],
            "code"=>$validated["otp"]
        ]);
        if(!$otpsend["success"]){
            return response()->json(["message"=>"Enter valid otp","success"=>false]);
        }
        $datePart = date('Y-m-d'); 

       
        $slug = $datePart . '-' . $validated['name'];
        $hashedSlug = Hash::make($slug);


        $newuser=Hotel::create([
            'name' => $validated["name"],
            'phone' => $validated["phone"],
            "slug"=>$hashedSlug,
            "email"=>$validated["email"],
            'password' => $validated["password"]]);
             
    return  response()->json(["message"=>"Signup Success","success"=>true,"info"=>$newuser],201);


}














    public function getHotelUser(Request $Req)
    {

     $user=hotelreguser::find($Req->id);

        if ($user) {
            return $user;
        } else {
            // Return a response indicating the user was not found
            return response()->json(['message' => 'User not found'], 404);
        }
    }



    public function signupHotel(Request $req)
    {



        $validated = $req->validate([
            'name' => 'required|string|max:25',
            'phone' => 'required',
            'password' => 'required|min:6',
        ]);

        $Alreadyuser = hotelreguser::where("phone", $validated['phone'])->first();

        if ($Alreadyuser) {
            return response()->json(["message" => "Phone Allready exist", "success" => false]);
        }


        $newuser = hotelreguser::create([
            'name' => $validated["name"],
            'phone' => $validated["phone"],
            'password' => $validated["password"],

        ]);

        return  response()->json(["message" => "Signup Success", "success" => true, "info" => $newuser], 201);
    }







    public function loginhotel(Request $req)
    {
        $validated = $req->validate([
            "phone" => "required",
            "password" => "required|min:6"
        ]);

        $userFind = hotelreguser::where("phone", $validated['phone'])->first();

        if (!$userFind) {
            return response()->json([
                "message" => "Enter Valid data",
                "success" => false
            ]);
        }

        $checkpassword = Hash::check($validated["password"], $userFind->password);

        if (!$checkpassword) {
            return response()->json([
                "message" => "Enter Valid data",
                "success" => false
            ]);
        }
        return response()->json([
            "message" => "Login Success",
            "success" => true,
            "info" => $userFind,
        ]);
    }


    public function getAllhotels()
    {
        // Fetch hotels where is_active = 1
        $users = Hotel::where('is_active', 1)->get(); 
        $info = [];
    
        foreach ($users as $user) {

            if ($user->user && $user->user->is_active != 1) {
                continue;
            }
    
  
            $singlehotel = HotelDetails::where("hotel_id", $user["id"])
                                        ->where('is_active', 1)
                                        ->first();
    
   
            if ($singlehotel === null) {
                continue;
            }
    
            // Add to result array if all conditions are met
            $info[] = [
                "hotel" => $singlehotel,
                "user" => $user
            ];
        }
    
        return $info;
    }


public function getSingleHotellreq(string $slug)
{
    $user = hoteldetails::where("slug", $slug)->first();
    if (!$user) {
        return response()->json(['message' => 'Hotel not found'], 404);
    }

    $hotel = hoteldetails::where("hotel_id", $user["id"])->first();
    $amenities = amenities::where("hotel_details_id", $user["id"])->first();
    $rooms = roomreg::where("hotel_reg_new_id", $user["id"])->get();

    $data = [
        "user" => $user,
        "hotel" => $hotel,
        "amenities" => $amenities,
        "rooms" => $rooms
    ];

    return response()->json($data);
}



}