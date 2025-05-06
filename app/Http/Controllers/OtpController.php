<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\otp;
use App\Models\apkatripuser;
use Illuminate\Support\Facades\Http;



class OtpController extends Controller
{
    //

    public function sendOtp(Request $request){
        $validate =$request->validate([
            "number"=>"required",
           
        ]);


           $allreadyuser =apkatripuser::where("number",$validate["number"])->first();

if($allreadyuser){
    return response()->json(["message"=>"number allready exist","success"=>false]);
}

$otpsend = Http::post('https://otp-verify-service.onrender.com/send-otp', [
    'phone' => $validate['number'],
]);
// if(!$otpsend["success"]){
//     return response()->json(["message"=>"Otp Error","success"=>false]);
// }


return $otpsend;
}










public function verifyOtp(Request $request){

$validate= $request->validate([
    "otp"=>"required",
    "name"=>"required",
    "number"=>"required",
    "password"=>"required|min:6"


]);

$otpsend = Http::post('https://otp-verify-service.onrender.com/verify-otp', [
    'phone' => $validate['number'],
    "code"=>$validate["otp"]
]);

if(!$otpsend["success"]){
    return response()->json(["message"=>"Enter valid otp","success"=>false]);
}



   $addUser= apkatripuser::create([
    "name"=>$validate["name"],
    "number"=>$validate["number"],
    "password"=>$validate["password"],
    ]);



return response()->json([
    'message' => 'User registered successfully',
    'success' => true,
    'info' => $addUser,
]);
}






public function forgotPasswordSendotp(Request $request){
    $validate =$request->validate([
    "number"=>"required",
           
]);


   $allreadyuser =apkatripuser::where("number",$validate["number"])->first();

if(!$allreadyuser){
return response()->json(["message"=>"number Does't exist","success"=>false]);
}

$alreadyotp=otp::where("number",$validate["number"]) ->first();
$otp = rand(100000, 999999);


if($alreadyotp){
    otp::where("number", $validate["number"])->delete();

   

$addotp=otp::create(["otp"=>$otp,"number"=>$validate["number"]]);


return response()->json(["message"=>"otp send","success"=>true,"data"=> $addotp]);
}
else{
  

$addotp=otp::create(["otp"=>$otp,"number"=>$validate["number"]]);
return response()->json(["message"=>"otp send","success"=>true,"data"=> $addotp]);

}



}





}
