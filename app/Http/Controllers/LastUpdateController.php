<?php

namespace App\Http\Controllers;

use App\Models\LastUpdate;

use Illuminate\Http\Request;

class LastUpdateController extends Controller
{
    //
public function getLAstUpdate(Request $request){

    $getUpdate=LastUpdate::all();

    return $getUpdate;


}

}
