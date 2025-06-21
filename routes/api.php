<?php


use App\Http\Controllers\AirportController;
use App\Http\Controllers\TopPorts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\TBOController;
use App\Http\Controllers\HotelRegistrationController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\SightseeingController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\BusControllerSearch;
use App\Http\Controllers\CheckinsController;
use App\Http\Controllers\HotelControllerSearchRes;
use App\Http\Controllers\CountryControllerCab;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransferSearchController;
use App\Http\Controllers\TicketBookingController;
use App\Http\Controllers\RazorpayOrderController;
use App\Http\Controllers\MatrixController;
use App\Http\Controllers\ChatController;
use OpenAI\Laravel\Facades\OpenAI;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ImageController;

use App\Http\Controllers\HotelRegesController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\MultiCityFareController;
use  App\Http\Controllers\OtpController;
use  App\Http\Controllers\SiteUser;
use App\Http\Controllers\TravelApplicationController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\WeddingEventController;
use App\Http\Controllers\CruiseController;
use App\Http\Controllers\WeddingJoinerController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\BookedhotelsController;

use App\Http\Controllers\HolidayspackageController;
use App\Http\Controllers\CharterController;

use App\Http\Controllers\SitelayoutController;
use App\Http\Controllers\RazorpayControllerNew;
use App\Mail\InsuranceBookingConfirmation;
use Carbon\Carbon;
use App\Http\Controllers\VisaInquiryController;
use App\Http\Controllers\HolidayBookingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('v1/airports', AirportController::class);
Route::apiResource('v1/topairports', TopPorts::class);


Route::post('v1/search-flights', [FlightController::class, 'searchFlights']);
Route::post('v1/search-return-flights', [FlightController::class, 'searchreturnflight']);
Route::post('v1/advance-search-flights', [FlightController::class, 'advance_search']);
Route::post('v1/farerule', [FlightController::class, 'fareRules']);

Route::post('v1/advance-ssr', [FlightController::class, 'ssrrequest']);
Route::post('v1/farequate', [FlightController::class, 'farequate']);


Route::post('v1/flight-book', [FlightController::class, 'bookFlight']);
Route::post('v1/genrate-ticket', [FlightController::class, 'generateTicket']);
Route::post('v1/get-calendar-fare', [FlightController::class, 'getCalendarFare']);



Route::post('v1/book-holiday', [HolidayBookingController::class, 'bookHoliday']);
// routes/api.php


Route::post('/visa/inquiry', [VisaInquiryController::class, 'store']);


Route::post('v1/travel-applications', [TravelApplicationController::class, 'store']);

Route::get('/get-token', [ApiController::class, 'getToken']);

Route::get('/test-ticket', [TicketBookingController::class, 'testTicketGeneration']);

Route::post('v1/wedding-events', [WeddingEventController::class, 'store']);

Route::post('v1/wedding-joiners', [Wedding\JoinerController::class, 'store']);
// genrateTickBook


Route::prefix('v1/matrix')->group(function () {
    Route::get('/countries', [MatrixController::class, 'getCountries']);
    Route::get('/plans', [MatrixController::class, 'getPlans']);
    Route::post('/validate-order', [MatrixController::class, 'validateOrder']);
    Route::post('/create-order', [MatrixController::class, 'createOrder']);
    Route::get('/orders', [MatrixController::class, 'getOrders']);
    Route::post('/upload-documents', [MatrixController::class, 'uploadDocuments']);
    Route::get('/wallet-balance', [MatrixController::class, 'getWalletBalance']);
    Route::get('/mobile-usage', [MatrixController::class, 'getMobileUsage']);
    Route::get('/recharge-plans', [MatrixController::class, 'getRechargePlans']);
    Route::post('/create-recharge', [MatrixController::class, 'createRecharge']);
    Route::get('/sim-installation-status', [MatrixController::class, 'getSimInstallationStatus']);
});



Route::post('v1/chat', [ChatController::class, 'chat']);

Route::post('v1/flight-book-llc', [FlightController::class, 'genrateTickBook']);

Route::post('v1/cancel-ticket', [FlightController::class, 'cancelTicket']);


Route::post('v1/multi-city-fare', [MultiCityFareController::class, 'getMultiCityFare']);


Route::get('/test-openai', function () {
    try {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello, world!'],
            ],
        ]);
        return response()->json(['message' => $response->choices[0]->message->content]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});


Route::post('/v1/flight-cancellation-charges', [FlightController::class, 'getCancellationCharges']);
Route::post('/v1/flight-send-change-request', [FlightController::class, 'sendChangeRequest']);

Route::post('/v1/hotel-cancellation', [HotelControllerSearchRes::class, 'cancelHotelBooking']);

Route::apiResource('v1/blog', BlogController::class);


Route::post('v1/initiate-razorpay-refund', [RazorpayControllerNew::class, 'RazorpayControllerNew']);

Route::get('v1/cities', [TBOController::class, 'fetchCities']);
Route::post('v1/hotels', [TBOController::class, 'fetchHotels']);

Route::post('v1/hotels/search', [HotelControllerSearchRes::class, 'searchHotels']);


Route::post('v1/hotels/hotel_single', [HotelControllerSearchRes::class, 'singleHotelget']);
Route::post('v1/hotels/prebooking', [HotelControllerSearchRes::class, 'preBooking']);

Route::post('v1/hotel/book', [HotelControllerSearchRes::class, 'bookHotel']);
Route::post('v1/hotel/bookdetails', [HotelControllerSearchRes::class, 'getBookingDetailHotel']);
// routes/api.php
Route::post('v1/hotel/cancel', [HotelControllerSearchRes::class, 'cancelHotelBooking']);



Route::get('/test-flight', function () {
    $booking = (object)[
        'date' => '12 July 2025',
        'from_code' => 'GSP',
        'to_code' => 'ORD',
        'from_city' => 'London',
        'to_city' => 'Chicago',
        'takeoff_time' => '11:15 PM',
        'landing_time' => '2:10 PM',
        'flight_number' => 'PQ 451',
        'duration' => '8h 55m',
        'seat' => '24C',
        'class' => 'Economy',
        'terminal' => 'D',
        'luggage' => '1x23kg',
        'card_type' => 'American Express',
        'card_last4' => '9805',
        'passenger_name' => 'Robert Brian Henderson',
        'subtotal' => '240.00',
        'tax' => '37.53',
        'total' => '297.53',
        'download_link' => 'https://yourdomain.com/download-ticket',
        'change_link' => 'https://yourdomain.com/change-flight',
        'manage_link' => 'https://yourdomain.com/manage',
        'support_email' => 'support@yourdomain.com'
    ];

    return view('emails.test-email', compact('booking'));
});

Route::get('/test-flight-email', function () {
    $booking = (object)[
        'date' => '12 July 2025',
        'from_code' => 'GSP',
        'to_code' => 'ORD',
        'from_city' => 'London',
        'to_city' => 'Chicago',
        'takeoff_time' => '11:15 PM',
        'landing_time' => '2:10 PM',
        'flight_number' => 'PQ451',
        'duration' => '8h 55m',
        'seat' => '24C',
        'class' => 'Economy',
        'terminal' => 'D',
        'luggage' => '1x23kg',
        'card_type' => 'American Express',
        'card_last4' => '9805',
        'passenger_name' => 'Robert Brian Henderson',
        'subtotal' => '240.00',
        'tax' => '37.53',
        'total' => '297.53',
        'download_link' => 'https://yourdomain.com/download-ticket',
        'change_link' => 'https://yourdomain.com/change-flight',
        'manage_link' => 'https://yourdomain.com/manage',
        'support_email' => 'support@yourdomain.com'
    ];

    $bookingData = [
        'PNR' => 'ABC123',
        'BookingId' => 'FL987654',
        'Origin' => $booking->from_city . ' (' . $booking->from_code . ')',
        'Destination' => $booking->to_city . ' (' . $booking->to_code . ')',
        'manage_link' => $booking->manage_link,
        'download_link' => $booking->download_link,
        'change_link' => $booking->change_link,
        'Segments' => [
            [
                'Airline' => [
                    'AirlineCode' => substr($booking->flight_number, 0, 2),
                    'AirlineName' => 'Generic Airways',
                    'FlightNumber' => $booking->flight_number
                ],
                'Origin' => [
                    'DepTime' => Carbon::parse($booking->date . ' ' . $booking->takeoff_time)->toDateTimeString()
                ],
                'Destination' => [
                    'ArrTime' => Carbon::parse($booking->date . ' ' . $booking->landing_time)->addDay()->toDateTimeString()
                ],
                'Duration' => 535
            ]
        ]
    ];

    $passengerData = [
        [
            'Title' => 'Mr',
            'FirstName' => 'Robert',
            'LastName' => 'Brian Henderson',
            'PaxType' => 1,
            'PassportNo' => 'N/A',
            'ContactNo' => 'N/A',
            'Email' => $booking->support_email
        ]
    ];

    $invoiceData = [
        'InvoiceNo' => 'INV-' . date('Y') . '-001',
        'InvoiceCreatedOn' => Carbon::now()->toDateTimeString(),
        'Currency' => 'USD',
        'BaseFare' => floatval($booking->subtotal),
        'Tax' => floatval($booking->tax),
        'OtherCharges' => 0.00,
        'InvoiceAmount' => floatval($booking->total)
    ];

    return view('emails.test-email', compact('bookingData', 'passengerData', 'invoiceData'));
});

Route::post('/upload-image', [ImageController::class, 'store']);

Route::post('v1/hotelslist', [HotelController::class, 'getHotelDetails']);
Route::post('v1/sightseeing/search', [SightseeingController::class, 'search']);
Route::post('v1/sightseeing', [SightseeingController::class, 'meRandomdata']);

Route::post('v1/verify-razorpay-payment', [RazorpayControllerNew::class, 'verifyPayment']);

Route::get('v1/bus/cities', [BusController::class, 'searchBusCityList']);
Route::post('v1/bus/search', [BusControllerSearch::class, 'searchBuses']);
Route::post('v1/bus/seatlayout', [BusControllerSearch::class, 'busSeatLayout']);
Route::post('v1/bus/busblock', [BusControllerSearch::class, 'busBlock']);

Route::post('v1/bus/book', [BusControllerSearch::class, 'bookbus']);


Route::post('v1/transfer-search', [TransferSearchController::class, 'searchTransfer']);


Route::get('v1/transfers', [TransferController::class, 'getTransferData']);

Route::get('v1/cab/countries', [CountryControllerCab::class, '']);
Route::post('v1/destination-search-static-data', [TransferController::class, 'getDestinationSearchStaticData']);
Route::get('v1/transfers', [TransferController::class, 'getTransfers']);
Route::get('v1/destinations', [TransferController::class, 'getDestinations']);
// Route::prefix('v1')->group(function () {
//     Route::apiResource('hotelreg', HotelRegistrationController::class);
// });


Route::post('v1/create-razorpay-order', [RazorpayOrderController::class, 'createOrder']);
Route::post('v1/capture-razorpay-payment', [RazorpayOrderController::class, 'capturePayment']);
Route::post('v1/send-booking-confirmation-email', [FlightController::class, 'sendBookingConfirmationEmail']);
///v1/capture-razorpay-payment

Route::post("v1/test", [HotelRegesController::class, "getHotelUser"]);
Route::post("v1/hotelreq/signupHotel", [HotelRegesController::class, "sendVerify"]);
Route::post("v1/hotelreq/otp", [HotelRegesController::class, "sendHotelOtp"]);

Route::post("v1/hotelreq/loginhotel", [HotelRegesController::class, "loginhotel"]);
Route::get("v1/hotel/all", [HotelRegesController::class, "getAllhotels"]);
Route::get("v1/hotel/single/{slug}", [HotelRegesController::class, "getSingleHotellreq"]);
Route::post("v1/user/sendotp", [OtpController::class, "sendOtp"]);
Route::post("v1/user/verifyotp", [OtpController::class, "verifyOtp"]);
Route::post("v1/user/forgotPassword", [OtpController::class, "forgotPasswordSendotp"]);
//getCancellationCharges
Route::get('v1/countries', [HotelControllerSearchRes::class, 'getCountries']);
// Route::post('v1/flight-cancel-charges', [FlightController::class, 'getCancellationCharges']);
Route::post("v1/user/signup", [SiteUser::class, "signupUser"]);
Route::post("v1/user/verifyotp", [SiteUser::class, "verifyOtp"]);
Route::post("v1/user/login", [SiteUser::class, "loginUser"]);
Route::get("v1/user/{id}", [SiteUser::class, "getSingleuser"]);
Route::put("v1/user/{id}", [SiteUser::class, "updateUser"]);
Route::get('v1/user-bookings/{id}', [FlightController::class, 'getUserBookings']);
Route::post('v1/get-booking-details', [FlightController::class, 'getBookingDetails']);
Route::post("v1/insurance",[InsuranceController::class,"GetInsurance"]);
Route::post('v1/insurance-book', [InsuranceController::class, 'bookInsurance']);


Route::post('/twilio-webhook', [ChatbotController::class, 'handleTwilioWebhook']);
Route::post('/dialogflow-webhook', [ChatbotController::class, 'handleDialogflowWebhook']);

Route::post("v1/cruise",[CruiseController::class,"sendCruiseMessage"]);
Route::post("v1/charter",[CharterController::class,"sendCharterMessage"]);
Route::resource('v1/hotels/checkins', CheckinsController::class);
Route::post("v1/hotelreg/booked",[BookedhotelsController::class,"bookhotel"]);
Route::post('/v1/test-email', [InsuranceController::class, 'testEmail']);
use App\Http\Controllers\LastUpdateController;
Route::get("v1/latestUpdate",[LastUpdateController::class,"getLAstUpdate"]);

use App\Http\Controllers\Popular_destController;

Route::get("/v1/Popular-Flight",[Popular_destController::class,"Popular_flight"]);
Route::get("/v1/Popular-hotel",[Popular_destController::class,"Popular_hotel"]);




Route::get("/v1/search-holidays-package/{name}",[HolidayspackageController::class,"SearchHolidayspackage"]);
Route::get("/v1/holidays-package/{name}",[HolidayspackageController::class,"GetHolidayPackage"]);
Route::get("/v1/holidays/top",[HolidayspackageController::class,"getActivePackage"]);

Route::get("/v1/holidays/list",[HolidayspackageController::class,"topfivepackage"]);
Route::get("/v1/holidays/review",[HolidayspackageController::class,"getallreview"]);


   use App\Http\Controllers\MyportsController;
   
   Route::get("/v1/ports/all/{name}",[MyportsController::class,"searchport"]);


   
   Route::get("/v1/home/bannerimg",[SitelayoutController::class,"siteBannerImages"]);
   Route::get("/v1/home/Featuredpropertie",[SitelayoutController::class,"Featured_Properties"]);



   Route::post('/v1/insurance/search', [InsuranceController::class, 'searchInsurance']);
   Route::post('/v1/insurance/book', [InsuranceController::class, 'bookInsurance']);
   Route::post('/v1/insurance/generate-policy', [InsuranceController::class, 'generatePolicy']);
   Route::post('/v1/insurance/get-booking-detail', [InsuranceController::class, 'getBookingDetail']);

   Route::apiResource('v1/sliders', SliderController::class);

//    Route::prefix('sliders')->group(function () {
//        Route::get('/', [SliderController::class, 'index']);  
//        Route::post('/', [SliderController::class, 'store']); // Create slider
//        Route::get('/{id}', [SliderController::class, 'show']); // Get single slider
//        Route::put('/{id}', [SliderController::class, 'update']); // Update slider
//        Route::delete('/{id}', [SliderController::class, 'destroy']); // Delete slider
//    });
   