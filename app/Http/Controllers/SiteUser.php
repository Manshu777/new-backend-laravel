<?php

namespace App\Http\Controllers;

use App\Mail\SendOtp;
use Illuminate\Http\Request;
use App\Models\apkatripuser;
use App\Models\userotp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SiteUser extends Controller
{
    //

    public function signupUser(Request $request)
    {
 $validate =$request->validate([
"name"=>"required|min:3",
"email"=>"required|email",
"password"=>"required|min:6"
        ]);

        $allreadyuser = apkatripuser::where("email", $validate["email"])->first();

        if ($allreadyuser && $allreadyuser->statue) {
            return response()->json(["message" => "email allready exist", "success" => false]);
        }

       
        

        $otpCode = rand(100000, 999999); // Generate a random OTP


        Mail::to($validate["email"])->send(new SendOtp($validate["email"], $otpCode));
        

        $userOtp = userotp::where("email", $validate["email"])->first();
        
        if ($userOtp) {
            // Update the existing OTP
            $userOtp->update(["otp" => $otpCode]);
        } else {
           
            userOtp::create(["email" => $validate["email"], "otp" => $otpCode]);
        }
        
      
        $verifuser = apkatripuser::where("email", $validate["email"])->first();
        
        if($verifuser) {
            
            $verifuser->update([
                "name" => $validate["name"],
                "password" => $validate["password"],
            ]);
            $addUser = $verifuser;
        } else {
            // Create a new user if they do not exist
            $addUser = apkatripuser::create([
                "name" => $validate["name"],
                "email" => $validate["email"],
                "password" => $validate["password"],
            ]);
        }
       
        return response()->json([
            'message' => 'User registered successfully',
            'success' => true,
            'info' => $addUser,
        ]);
    }


public function verifyOtp(Request $request){

$validate=$request->validate([
    "email"=>"required",
    "otp"=>"required|digits:6"
]);

$allreadyuser = apkatripuser::where("email", $validate["email"])->first();
if ($allreadyuser && $allreadyuser->statue) {
    return response()->json(["message" => "email allready exist", "success" => false]);
};


$findotp=userotp::where("email", $validate["email"])->first();
if($validate["otp"] != $findotp["otp"]){

return response()->json(["message" => "incorrect otp", "success" => false]);
} 

$findotp->delete();
$allreadyuser->update(["statue"=>true]);
return response()->json(["success"=>true,"message"=>"signup successfully","info"=>$allreadyuser]);





}



    public function loginUser(Request $request)
    {


      
        $validatedData = $request->validate([
            "email" => "required|email", // Ensure email is a valid email address
            "password" => "required|min:6", // Minimum password length
        ]);
        
   
        
        $user = ApkatripUser::where("email",$validatedData["email"])->first();
      
        if (!$user) {
            return response()->json([
                "message" => "Enter valid data 1", // Fixed typo in message
                "success" => false,
            ]);
        }
    
        // Verify the password
        if (!Hash::check($validatedData["password"], $user->password)) {
            return response()->json([
                "message" => "Enter valid data", // Same consistent error message
                "success" => false,
            ]);
        }
    
        // If all checks pass, return success response
        return response()->json([
            "message" => "User logged in successfully", // Updated success message
            "success" => true,
            "info" => $user, // Include user data in the response
        ]);  
    
    }

public function getSingleuser(string $id){
$user =apkatripuser::find($id);
 
return response()->json(["success"=>true,"user"=>$user]);

}

public function updateUser( string $id,Request $request){
   
    $user=apkatripuser::find($id);
$user->update(["name"=>$request["fullName"],"dob"=>$request["birthday"],"gender"=>$request["gender"],"marital_status","address"=>$request["address"]]);

    return $request;



}

}