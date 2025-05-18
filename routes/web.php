<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\File;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});


use Barryvdh\DomPDF\Facade\Pdf;

// In routes/web.php or routes/api.php
Route::get('/test-view', function () {
    $bookingDetails = [
        'pax' => [
            'Title' => 'Mr',
            'FirstName' => 'Test',
            'LastName' => 'User',
            'EmailId' => 'test@example.com',
            'PhoneNumber' => '1234567890',
            'AddressLine1' => 'Test Address',
            'City' => 'Test City',
            'Country' => 'India',
            'PinCode' => '123456',
            'MajorDestination' => 'INDIA',
            'Price' => ['GrossFare' => 1000],
        ],
        'itinerary' => [
            'BookingId' => 123,
            'InsuranceId' => 456,
            'PlanName' => 'Test Plan',
        ],
        'coverageHtml' => '<li>Test Coverage: INR 1000</li>',
        'startDate' => 'June 1, 2025',
        'endDate' => 'June 30, 2025',
        'bookingDate' => 'May 18, 2025',
        'dob' => 'July 17, 1990',
        'recipientEmail' => 'test@example.com',
    ];
    return view('pdf.insurance_booking_confirmation', $bookingDetails);
});

Route::get('/test-email-view', function () {


    $ticket = [
        'pnr' => Str::random(6),  // Random PNR (6-character string)
        'booking_id' => rand(100000, 999999), // Random booking ID (6 digits)
        'user_name' => 'John Doe', // Random user name
        'username' => 'johndoe' . rand(1, 999) . '@example.com', // Random email
        'phone_number' => '+1' . rand(1000000000, 9999999999), // Random phone number (US format)
        'issued_date' => now()->format('d F Y'),
        
        // Dynamically fill flight details with random data
        'flight_name' => 'Airline ' . rand(1, 10), // Random airline name
        'flight_number' => 'FL' . rand(1000, 9999), // Random flight number
        'arrival_to' => 'Airport ' . rand(1, 5), // Random airport name for destination
        'departure_from' => 'Airport ' . rand(1, 5), // Random airport name for origin
        
        // Fare details with random values
        'total_fare' => rand(100, 9999), // Random fare amount
        'usd_amount' => rand(100, 9999), // Random USD amount
        'conversion_rate' => round(rand(50, 100) / 10, 2), // Random conversion rate between 5.0 and 10.0
        
        // Flight route and flight date
        'route' => 'Airport ' . rand(1, 5) . ' - ' . 'Airport ' . rand(1, 5), // Random route
        'flight_date' => \Carbon\Carbon::now()->addDays(rand(1, 30))->format('d F Y'), // Random flight date within next 30 days
        'passenger_name' => 'John',
        'gender' => 'Male',
        'nationality' => 'IN',
        //full_route
        
        'full_route' => 'Airport ' . rand(1, 5) . ' - ' . 'Airport ' . rand(1, 5), // Random route
        'ticket_no' => 'TICKET' . rand(1000, 9999), 
        'base_fare' => rand(100, 9999), // Random base fare
        'tax' => rand(10, 100), // Random tax amount
        'total_bdt' => rand(100, 9999), // Random total amount in BDT

        'date' => now()->toDateString(),
     
        'time' => now()->format('H:i:s'),
        'status' => 'Confirmed',
        'flight_date' => now()->toDateString(),

        'passengers' => [
            [
                'passenger_name' => 'John',
                'LastName' => 'Doe',
                'Email' => 'johndoe@example.com',
                'ContactNo' => '+1' . rand(1000000000, 9999999999),
            ],
        ], 
    ];
    

    
    Mail::to('manshu.developer@gmail.com')->send(new BookingConfirmation($ticket));
    return view('emails.invoice', compact('ticket'));


});



Route::get('/test-bus-ticket-email', function () {
    $dummyPassenger = [
        'FirstName' => 'John',
        'LastName' => 'Doe',
        'Email' => 'manshu.developer@gmail.com',
        'Phoneno' => '9876543210',
    ];

    $dummyResult = [
        'TicketNo' => '4AK77DG5',
        'TravelOperatorPNR' => '4AK77DG5',
        'InvoiceNumber' => 'MW/2425/12905',
        'InvoiceAmount' => 297.00,
        'BusBookingStatus' => 'Confirmed',
    ];

    Mail::send('emails.bus_ticket_invoice', [
        'ticket_no' => $dummyResult['TicketNo'],
        'pnr' => $dummyResult['TravelOperatorPNR'],
        'invoice_no' => $dummyResult['InvoiceNumber'],
        'amount' => $dummyResult['InvoiceAmount'],
        'status' => $dummyResult['BusBookingStatus'],
        'passenger_name' => $dummyPassenger['FirstName'] . ' ' . $dummyPassenger['LastName'],
        'email' => $dummyPassenger['Email'],
        'phone' => $dummyPassenger['Phoneno'],
    ], function ($message) use ($dummyPassenger, $dummyResult) {
        $message->to($dummyPassenger['Email'], $dummyPassenger['FirstName'])
                ->subject('Your Bus Ticket - ' . $dummyResult['TicketNo']);
    });

    return 'Test email sent to ' . $dummyPassenger['Email'];
});




Route::get('/generate-bus-ticket', function () {
    $passenger = [
        'FirstName' => 'John',
        'LastName' => 'Doe',
        'Email' => 'manshu.developer@gmail.com',
        'Phoneno' => '9876543210',
    ];

    $result = [
        'TicketNo' => '4AK77DG5',
        'TravelOperatorPNR' => '4AK77DG5',
        'InvoiceNumber' => 'MW/2425/12905',
        'InvoiceAmount' => 297.00,
        'BusBookingStatus' => 'Confirmed',
    ];

    $routeDetails = [
        'source' => 'Delhi',
        'destination' => 'Jaipur',
        'departure' => '2025-05-05 08:00 AM',
        'arrival' => '2025-05-05 12:30 PM',
        'bus_name' => 'Super Deluxe Volvo',
        'seat_no' => 'A1',
    ];

    $pdf = PDF::loadView('emails.bus_ticket_invoice', [
        'ticket_no' => $result['TicketNo'],
        'pnr' => $result['TravelOperatorPNR'],
        'invoice_no' => $result['InvoiceNumber'],
        'amount' => $result['InvoiceAmount'],
        'status' => $result['BusBookingStatus'],
        'passenger_name' => $passenger['FirstName'] . ' ' . $passenger['LastName'],
        'email' => $passenger['Email'],
        'phone' => $passenger['Phoneno'],
        'source' => $routeDetails['source'],
        'destination' => $routeDetails['destination'],
        'departure' => $routeDetails['departure'],
        'arrival' => $routeDetails['arrival'],
        'bus_name' => $routeDetails['bus_name'],
        'seat_no' => $routeDetails['seat_no'],
    ]);

    $pdfPath = storage_path('app/public/ticket.pdf');
    $pdf->save($pdfPath);

    if (file_exists($pdfPath)) {
        Mail::send([], [], function ($message) use ($passenger, $result, $pdfPath) {
            $message->to($passenger['Email'], $passenger['FirstName'])
                ->subject('Your Bus Ticket - ' . $result['TicketNo'])
                ->attach($pdfPath, [
                    'as' => 'Bus_Ticket.pdf',
                    'mime' => 'application/pdf',
                ])
                ->html('Dear ' . $passenger['FirstName'] . ', your bus ticket is attached as a PDF. Please carry it during your journey.');
        });
        unlink($pdfPath);
    }

    return $pdf->download('Bus_Ticket.pdf');
});

Route::get('/invoice-email-view', function () {
    $ticket = [
        'pnr' => 'TEST123',
        'user_name' => 'Test User',
        'username' => 'test@example.com',
        'flight_name' => 'Test Airlines',
        'flight_number' => 'TA001',
        'departure_from' => 'Test City',
        'flight_date' => '2025-04-25 10:00:00',
        'arrival_to' => 'Test Destination',
        'return_date' => '2025-04-25 12:30:00',
        'total_fare' => 1000,
        'booking_id' => 'BOOK123',
        'phone_number' => '1234567890',
        'date_of_booking' => '2025-04-20',

        // Extra fields
        'payment_method' => 'Credit Card',
        'class' => 'Economy',
        'seat_number' => '12A',
        'airline_code' => 'TA',
        'transaction_id' => 'TXN987654321',
    ];

    return view('emails.invoice', compact('ticket'));
});

use Illuminate\Support\Facades\Artisan;


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
