<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\File;
use App\Mail\FlightBookingConfirmation;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});


use Barryvdh\DomPDF\Facade\Pdf;



use Illuminate\Support\Facades\Artisan;


Route::get('/test-flight-email', function () {
    $ticket = [ /* Dummy data from previous response */ ];
    try {
        Mail::to('test@nextgentrip.com')
            ->send(new FlightBookingConfirmation($ticket, $ticket['username']));
        return response()->json(['message' => 'Test email with PDF invoice sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send test email', 'error' => $e->getMessage()], 500);
    }
});


Route::get('/test-email', function () {
    // Dummy data based on provided JSON
    $ticket = [
        'pnr' => 'C5EW7F',
        'booking_id' => 1982805,
        'user_name' => 'Manshu Mehra',
        'username' => 'manshu.developer@gmail.com',
        'phone_number' => '7988532993',
        'issued_date' => now()->format('d F Y'),
        'flight_name' => 'SpiceJet',
        'flight_number' => '533',
        'arrival_to' => 'Kempegowda International Airport',
        'departure_from' => 'Indira Gandhi Airport',
        'total_fare' => 3792.77,
        'usd_amount' => 3484.69,
        'conversion_rate' => 1,
        'full_route' => 'Indira Gandhi Airport - Kempegowda International Airport',
        'flight_date' => '29 May 2025',
        'date_of_booking' => now()->toDateString(),
        'invoice_no' => 'DW/2526/5945',
        'airline_toll_free_no' => '9876543210',
        'passengers' => [
            [
                'Title' => 'Mr',
                'FirstName' => 'Manshu',
                'LastName' => 'Mehra',
                'PaxType' => 1,
                'DateOfBirth' => '1997-07-19',
                'Gender' => 1,
                'PassportNo' => 'AAAN1234',
                'PassportExpiry' => '2047-07-19',
                'AddressLine1' => 'ambala',
                'City' => 'ambala',
                'CountryCode' => 'IN',
                'ContactNo' => '7988532993',
                'Email' => 'manshu.developer@gmail.com',
                'IsLeadPax' => true,
                'Fare' => [
                    'Currency' => 'INR',
                    'BaseFare' => 2499,
                    'Tax' => 1292,
                    'YQTax' => 900,
                    'AdditionalTxnFeePub' => 0.0,
                    'AdditionalTxnFeeOfrd' => 0.0,
                    'OtherCharges' => 1.77,
                    'Discount' => 0.0,
                    'PublishedFare' => 3792.77,
                    'OfferedFare' => 3484.69,
                    'TdsOnCommission' => 73.77,
                    'TdsOnPLB' => 63.28,
                    'TdsOnIncentive' => 68.33,
                    'ServiceFee' => 0,
                ],
            ],
        ],
        'segments' => [
            [
                'Baggage' => '15 KG',
                'CabinBaggage' => '7 KG',
                'Airline' => [
                    'AirlineName' => 'SpiceJet',
                    'FlightNumber' => '533',
                ],
                'Origin' => [
                    'DepTime' => '2025-05-29T06:20:00',
                    'Airport' => [
                        'AirportName' => 'Indira Gandhi Airport',
                        'Terminal' => '1D',
                    ],
                ],
                'Destination' => [
                    'ArrTime' => '2025-05-29T09:20:00',
                    'Airport' => [
                        'AirportName' => 'Kempegowda International Airport',
                        'Terminal' => '1',
                    ],
                ],
            ],
        ],
    ];

    // Send the test email
    try {
        Mail::to('manshu.developer@gmail.com') // Replace with your test email or Mailtrap inbox
            ->send(new FlightBookingConfirmation($ticket, $ticket['username']));
        return response()->json(['message' => 'Test email sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send test email', 'error' => $e->getMessage()], 500);
    }
});


Route::get('/create-storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});

Route::get("/destory-storage-link",function(){
    if (File::exists(public_path('storage'))) {
        File::delete(public_path('storage'));
        return 'Unlink successful!';
    } else {
        return 'Symlink does not exist!';
    }
}); 








// Route::middleware(['ensure.token'])->post('/search-flightss', [FlightController::class, 'searchFlights']);
// Route::middleware(['ensure.token'])->post('/search-flights', [FlightController::class, 'searchFlights']);
// Route::get('/search-flights-one', [FlightController::class, 'searchFlights']);
