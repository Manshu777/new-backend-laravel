<?php

namespace App\Http\Controllers;

use App\Models\airport;
use Illuminate\Support\Facades\Http; 

use Illuminate\Http\Request;

class AirportController extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query("query");
        // Modify the query to include a condition for popular airports
        if (!empty($query)) {
            $res = Airport::where('name', 'like', '%' . $query . '%')->orWhere('municipality', 'like', '%' . $query . '%')->paginate(8);

        } else {

            $res = Airport::where('popular', 1)->orWhere('name', 'jonty')
                ->get(); 
        }

        return response()->json($res);
    }

    public function show($id)
    {
        $airport = Airport::find($id);

        if ($airport) {
            return response()->json($airport);
        }

        return response()->json(['message' => 'Airport not found'], 404);
    }

    // Create a new airport
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ident' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'latitude_deg' => 'nullable|numeric',
            'longitude_deg' => 'nullable|numeric',
            'elevation_ft' => 'nullable|integer',
            'continent' => 'required|string|max:255',
            'iso_country' => 'required|string|max:255',
            'iso_region' => 'required|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'scheduled_service' => 'required|boolean',
            'gps_code' => 'nullable|string|max:10',
            'iata_code' => 'nullable|string|max:10',
            'local_code' => 'nullable|string|max:10',
            'home_link' => 'nullable|url',
            'wikipedia_link' => 'nullable|url',
            'keywords' => 'nullable|string',
        ]);

        $airport = Airport::create($validated);
        return response()->json($airport, 201);
    }

    // Update an existing airport
    public function update(Request $request, $id)
    {
        $airport = Airport::find($id);

        if (!$airport) {
            return response()->json(['message' => 'Airport not found'], 404);
        }

        $validated = $request->validate([
            'ident' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'latitude_deg' => 'nullable|numeric',
            'longitude_deg' => 'nullable|numeric',
            'elevation_ft' => 'nullable|integer',
            'continent' => 'required|string|max:255',
            'iso_country' => 'required|string|max:255',
            'iso_region' => 'required|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'scheduled_service' => 'required|boolean',
            'gps_code' => 'nullable|string|max:10',
            'iata_code' => 'nullable|string|max:10',
            'local_code' => 'nullable|string|max:10',
            'home_link' => 'nullable|url',
            'wikipedia_link' => 'nullable|url',
            'keywords' => 'nullable|string',
        ]);

        $airport->update($validated);
        return response()->json($airport);
    }

    // Delete an airport
    public function destroy($id)
    {
        $airport = Airport::find($id);

        if (!$airport) {
            return response()->json(['message' => 'Airport not found'], 404);
        }

        $airport->delete();
        return response()->json(['message' => 'Airport deleted successfully']);
    }
}



// function getallPorts(Request $request){
//     $page = $request->query('page', 1); 
//     $query = $request->query('query', '');
//      $res=airport::where('name', 'like', '%' . $query . '%')->orWhere('name', 'like', '%' . $query . '%' )->paginate($page);
//      if ($res->currentPage() > $res->lastPage()) {
       
//         return redirect()->to('/api/airports?page='.urlencode($page-1).' &query=' . urlencode($query));
//     }
//     return response()->json($res);


//    }
// }