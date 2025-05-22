<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use App\Models\HotelData;
use Carbon\Carbon;


class HotelControllerSearchRes extends Controller
{
   public function searchHotels(Request $request)
{
    $validated = $request->validate([
        'cityCode' => 'required|string',
        'checkIn' => 'required|date',
        'checkOut' => 'required|date',
        'adults' => 'required|integer|min:1',
        'children' => 'required|integer|min:0',
        'guestNationality' => 'required|string',
    ]);

    $hotelresult = [];

    // Check database for valid data (within 15 days)
    $dbHotels = HotelData::where('city_code', $validated['cityCode'])
        ->where('created_at', '>=', Carbon::now()->subDays(15))
        ->get();

    if ($dbHotels->isNotEmpty()) {
        foreach ($dbHotels as $hotel) {
            $hotelresult[] = [
                'hotelDetails' => $hotel->hotel_details,
                'searchResults' => $hotel->search_results,
            ];
        }

        return response()->json([
            'totalHotels' => $hotelresult,
        ]);
    }

    // No valid data in database, fetch from API
    $client = new Client();

    // Fetch hotel codes
    $response1 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/TBOHotelCodeList', [
        'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
        'json' => [
            "CityCode" => $validated['cityCode'],
            "IsDetailedResponse" => false,
        ]
    ]);

    $hotelData = json_decode($response1->getBody()->getContents(), true);
    $hotelCodes = array_column($hotelData['Hotels'], 'HotelCode');
    $hotelCodes = array_slice($hotelCodes, 0, 100); // Limit to 100 hotel codes

    if (empty($hotelCodes)) {
        return response()->json([
            'message' => 'No hotels available',
            'totalHotels' => [],
        ]);
    }

    // Process all hotels
    foreach ($hotelCodes as $hotelCode) {
        // Check if hotel data exists in DB and is valid
        $existingHotel = HotelData::where('hotel_code', $hotelCode)
            ->where('city_code', $validated['cityCode'])
            ->where('created_at', '>=', Carbon::now()->subDays(15))
            ->first();

        if ($existingHotel) {
            $hotelresult[] = [
                'hotelDetails' => $existingHotel->hotel_details,
                'searchResults' => $existingHotel->search_results,
            ];
            continue;
        }


        $response3 = $client->post('https://affiliate.tektravels.com/HotelAPI/Search', [
            'auth' => ['Apkatrip', 'Apkatrip@1234'],
            'json' => [
                "CheckIn" => $validated['checkIn'],
                "CheckOut" => $validated['checkOut'],
                "HotelCodes" => $hotelCode,
                "GuestNationality" => $validated['guestNationality'],
                "PaxRooms" => [
                    [
                        "Adults" => $validated['adults'],
                        "Children" => $validated['children'],
                        "ChildrenAges" => $validated['children'] > 0 ? [null] : null,
                    ]
                ],
                "ResponseTime" => 23.0,
                "IsDetailedResponse" => true,
                "Filters" => [
                    "Refundable" => false,
                    "NoOfRooms" => 1,
                    "MealType" => 0,
                    "OrderBy" => 0,
                    "StarRating" => 0,
                    "HotelName" => null,
                ]
            ]
        ]);

        $searchResults = json_decode($response3->getBody()->getContents(), true);


        $hasAvailableRooms = !($searchResults['Status']['Code'] === 201 && $searchResults['Status']['Description'] === "No Available rooms for given criteria");


        $response2 = $client->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
            'auth' => ['TBOStaticAPITest', 'Tbo@11530818'],
            'json' => [
                "Hotelcodes" => $hotelCode,
                "Language" => "EN",
            ]
        ]);

        $hotelDetails = json_decode($response2->getBody()->getContents(), true);


        HotelData::updateOrCreate(
            [
                'city_code' => $validated['cityCode'],
                'hotel_code' => $hotelCode,
            ],
            [
                'hotel_details' => $hotelDetails,
                'search_results' => $searchResults,
            ]
        );

        // Only include hotels with available rooms in the result
        if ($hasAvailableRooms) {
            $hotelresult[] = [
                "hotelDetails" => $hotelDetails,
                "searchResults" => $searchResults
            ];
        }
    }

    if (empty($hotelresult)) {
        return response()->json([
            'message' => 'No hotels available',
            'totalHotels' => [],
        ]);
    }

    return response()->json([
        'totalHotels' => $hotelresult,
    ]);
}
 



   public function singleHotelget(Request $request)
    {
        $validated = $request->validate([
            'HotelCode' => 'required|string',
            'checkIn' => 'required|date',
            'checkOut' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'guestNationality' => 'required|string',


        ]);
        $client = new \GuzzleHttp\Client();


        $response1 = Http::withBasicAuth('TBOStaticAPITest', 'Tbo@11530818')->post('http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails', [
            "Hotelcodes" => $validated['HotelCode'],
            "Language" => "EN"
        ]);

        $response2 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/Search', [


            "CheckIn" => $validated['checkIn'],
            "CheckOut" => $validated['checkOut'],
            "HotelCodes" => $validated['HotelCode'],
            "GuestNationality" => $validated['guestNationality'],
            "PaxRooms" => [
                [
                    "Adults" => $validated['adults'],
                    "Children" => $validated['children'],
                    "ChildrenAges" => $validated['children'] > 0 ? [null] : null
                ]
            ],
            "ResponseTime" => 23.0,
            "IsDetailedResponse" => true,
            "Filters" => [
                "Refundable" => false,
                "NoOfRooms" => 1,
                "MealType" => 0,
                "OrderBy" => 0,
                "StarRating" => 0,
                "HotelName" => null

            ]
        ]);



        $resp1 = json_decode($response1->getBody()->getContents(), true);
        $resp2 = json_decode($response2->getBody()->getContents(), true);

        $values = ["hoteldetail1" => $resp1['HotelDetails'], "hoteldetail2" => $resp2["HotelResult"]];
        return response()->JSON($values);

    }


    public   function preBooking(Request $request)
              {
        $validated = $request->validate([
            'BookingCode' => 'required',



        ]);


        $response1 = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')->post('https://affiliate.tektravels.com/HotelAPI/PreBook', [
            "BookingCode" => $validated['BookingCode']

        ]);
        $response1 = json_decode($response1->getBody()->getContents(), true);

        return $response1;
    }


    public function bookHotel(Request $request)
{
    $validated = $request->validate([
        'BookingCode' => 'required',
        'IsVoucherBooking' => 'required|boolean',
        'GuestNationality' => 'required|string',
        'EndUserIp' => 'required|ip',
        'RequestedBookingMode' => 'required|integer',
        'NetAmount' => 'required|numeric',
        'HotelRoomsDetails' => 'required|array',
    ]);

    // Send booking request without authentication
    $response = Http::withBasicAuth('Apkatrip', 'Apkatrip@1234')
            ->post('https://HotelBE.tektravels.com/hotelservice.svc/rest/book', [
                "BookingCode" => $validated['BookingCode'],
                "IsVoucherBooking" => $validated['IsVoucherBooking'],
                "GuestNationality" => $validated['GuestNationality'],
                "EndUserIp" => $validated['EndUserIp'],
                "RequestedBookingMode" => $validated['RequestedBookingMode'],
                "NetAmount" => $validated['NetAmount'],
                "HotelRoomsDetails" => $validated['HotelRoomsDetails']
            ]);
    // Decode the API response
    $responseData = json_decode($response->body(), true);

    return response()->json($responseData);
}



    public function getBookingDetail(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'BookingId' => 'required|string',
            'EndUserIp' => 'required|ip',
            'TokenId' => 'required|string'
        ]);

        // Send request to GetBookingDetail API
        $response = Http::post('http://HotelBE.tektravels.com/internalhotelservice.svc/rest/GetBookingDetail', [
            "BookingId" => $validated['BookingId'],
            "EndUserIp" => $validated['EndUserIp'],
            "TokenId" => $validated['TokenId']
        ]);

        // Decode API response
        $responseData = json_decode($response->body(), true);

        return response()->json($responseData);
    }

}
