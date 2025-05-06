<?php

namespace App\Http\Controllers;

use App\Models\Sitelayout;
use Illuminate\Http\Request;
use App\Models\Featuredpropertie;


class SitelayoutController extends Controller
{
    public function siteBannerImages(){
        $info= Sitelayout::all();
        return response()->json([
            "success"=>true,
            "info"=>$info,
        ]);
    }

    public function Featured_Properties(){
        $Featuredpropertie= Featuredpropertie::all();

        return $Featuredpropertie;
    }

}
