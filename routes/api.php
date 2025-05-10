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



use App\Http\Controllers\ApiController;
use App\Http\Controllers\ImageController;

use App\Http\Controllers\HotelRegesController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\MultiCityFareController;
use  App\Http\Controllers\OtpController;
use  App\Http\Controllers\SiteUser;

use App\Http\Controllers\InsuranceController;

use App\Http\Controllers\CruiseController;


use App\Http\Controllers\BookedhotelsController;

use App\Http\Controllers\HolidayspackageController;
use App\Http\Controllers\CharterController;

use App\Http\Controllers\SitelayoutController;



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



Route::get('/get-token', [ApiController::class, 'getToken']);

Route::get('/test-ticket', [TicketBookingController::class, 'testTicketGeneration']);


// genrateTickBook


Route::post('v1/flight-book-llc', [FlightController::class, 'genrateTickBook']);


Route::post('v1/multi-city-fare', [MultiCityFareController::class, 'getMultiCityFare']);


Route::post('/v1/flight-cancellation-charges', [FlightController::class, 'getCancellationCharges']);
Route::post('/v1/flight-send-change-request', [FlightController::class, 'sendChangeRequest']);


Route::apiResource('v1/blog', BlogController::class);




Route::get('v1/cities', [TBOController::class, 'fetchCities']);
Route::post('v1/hotels', [TBOController::class, 'fetchHotels']);

Route::post('v1/hotels/search', [HotelControllerSearchRes::class, 'searchHotels']);


Route::post('v1/hotels/hotel_single', [HotelControllerSearchRes::class, 'singleHotelget']);
Route::post('v1/hotels/prebooking', [HotelControllerSearchRes::class, 'preBooking']);

Route::post('v1/hotel/book', [HotelControllerSearchRes::class, 'bookHotel']);
Route::post('v1/hotel/bookdetails', [HotelControllerSearchRes::class, 'getBookingDetail']);
// routes/api.php




Route::post('/upload-image', [ImageController::class, 'store']);

Route::post('v1/hotelslist', [HotelController::class, 'getHotelDetails']);
Route::post('v1/sightseeing/search', [SightseeingController::class, 'search']);
Route::post('v1/sightseeing', [SightseeingController::class, 'meRandomdata']);



Route::get('v1/bus/cities', [BusController::class, 'searchBusCityList']);
Route::post('v1/bus/search', [BusControllerSearch::class, 'searchBuses']);
Route::post('v1/bus/seatlayout', [BusControllerSearch::class, 'busSeatLayout']);
Route::post('v1/bus/busblock', [BusControllerSearch::class, 'busBlock']);

Route::post('v1/bus/book', [BusControllerSearch::class, 'bookbus']);

// busBlock

Route::post('v1/transfer-search', [TransferSearchController::class, 'searchTransfer']);


Route::get('v1/transfers', [TransferController::class, 'getTransferData']);

Route::get('v1/cab/countries', [CountryControllerCab::class, '']);
Route::post('v1/destination-search-static-data', [TransferController::class, 'getDestinationSearchStaticData']);

// Route::prefix('v1')->group(function () {
//     Route::apiResource('hotelreg', HotelRegistrationController::class);
// });


Route::post('v1/create-razorpay-order', [RazorpayOrderController::class, 'createOrder']);


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

Route::post("v1/cruise",[CruiseController::class,"sendCruiseMessage"]);
Route::post("v1/charter",[CharterController::class,"sendCharterMessage"]);
Route::resource('v1/hotels/checkins', CheckinsController::class);
Route::post("v1/hotelreg/booked",[BookedhotelsController::class,"bookhotel"]);

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
   