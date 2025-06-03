<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use App\Mail\BusBookingConfirmation;
use App\Models\BusBooking;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ApiService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BusControllerSearch extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }


    public function searchBuses(Request $request)
    {
    
        $token = $this->apiService->getToken();

   
        $validatedData = $request->validate([
            'DateOfJourney' => 'required|date',
            'OriginId' => 'required|integer',
            'DestinationId' => 'required|integer',
            'PreferredCurrency' => 'required|string',
            'EndUserIp' => 'required|ip',  
        ]);

   
        $searchPayload = [
            "DateOfJourney" => $validatedData['DateOfJourney'],
            "DestinationId" => $validatedData['DestinationId'],
            "EndUserIp" => $validatedData['EndUserIp'], 
            "OriginId" => $validatedData['OriginId'],
            "TokenId" => $token,  
            "PreferredCurrency" => $validatedData['PreferredCurrency'],
        ];


        $response = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);


        if ($response->json('Response.Error.ErrorCode') === 6) {

            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;


            $response = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/Search', $searchPayload);
        }


        return $response->json();
    }

    public function busBlock(Request $request)
    {
        $token = $this->apiService->getToken();
    
        $validatedData = $request->validate([
            'EndUserIp' => 'required|ip',
            'ResultIndex' => 'required|integer',
            'TraceId' => 'required|string',
            'BoardingPointId' => 'required|integer',
            'DroppingPointId' => 'required|integer',
            'Passenger' => 'required|array',
        ]);
    
        $payload = [
            "EndUserIp" => $validatedData['EndUserIp'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "TraceId" => $validatedData['TraceId'],
            "TokenId" => $token,
            "BoardingPointId" => $validatedData['BoardingPointId'],
            "DroppingPointId" => $validatedData['DroppingPointId'],
            "Passenger" => $validatedData['Passenger'],
        ];
    
        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://BusBE.tektravels.com/Busservice.svc/rest/Block', $payload);
    
        if ($response->json('BlockResult.Error.ErrorCode') === 6) {
            // Re-authenticate
            $token = $this->apiService->authenticate();
            $payload['TokenId'] = $token;
    
            // Retry request
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://BusBE.tektravels.com/Busservice.svc/rest/Block', $payload);
        }
    
        return $response->json();
    }
    

    public function busSeatLayout(Request $request){
        $token = $this->apiService->getToken();
        
      
        $validatedData = $request->validate([
            'TraceId' => 'required',
            'ResultIndex' => 'required',
          
            'EndUserIp' => 'required|ip',  
        ]);

        $searchPayload = [
            "TraceId" => $validatedData['TraceId'],
            "ResultIndex" => $validatedData['ResultIndex'],
            "EndUserIp" => $validatedData['EndUserIp'],  // Use validated IP
            "TokenId" => $token,  // Use the token from the service
        ];
//https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails
        $buslayout = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
        $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        
        if ($buslayout->json('Response.Error.ErrorCode') === 6) {
            // Re-authenticate to get a new token
            $token = $this->apiService->authenticate();
            $searchPayload['TokenId'] = $token;

            // Retry the API request with the new token
            $buslayout = Http::timeout(90)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBusSeatLayOut', $searchPayload);
            $busBOARDING = Http::timeout(100)->withHeaders([])->post('https://BusBE.tektravels.com/Busservice.svc/rest/GetBoardingPointDetails', $searchPayload);

        }
        // return $searchPayload;
        return   response()->json(["buslayout"=>json_decode($buslayout),"busbording"=>json_decode($busBOARDING)]);


    }  

public function bookbus(Request $request)
{
    try {
        $token = $this->apiService->getToken();

        // Transform request data to match validation expectations
        $requestData = $request->all();
        if (isset($requestData['Passenger'])) {
            $requestData['passenger'] = array_map(function ($passenger) {
                return [
                    'name' => $passenger['FirstName'] . ' ' . $passenger['LastName'],
                    'email' => $passenger['Email'],
                    'phone' => $passenger['Phoneno'],
                    'FirstName' => $passenger['FirstName'],
                    'LastName' => $passenger['LastName'],
                    'Title' => $passenger['Title'],
                    'Age' => $passenger['Age'],
                    'Gender' => $passenger['Gender'],
                    'IdType' => $passenger['IdType'],
                    'IdNumber' => $passenger['IdNumber'],
                    'Address' => $passenger['Address'],
                    'Seat' => $passenger['Seat'],
                    'LeadPassenger' => $passenger['LeadPassenger'],
                    'PassengerId' => $passenger['PassengerId'],
                ];
            }, $requestData['Passenger']);
            unset($requestData['Passenger']);
        }
        if (isset($requestData['DroppingPointId'])) {
            $requestData['DroppingPointId'] = $requestData['DroppingPointId'];
        }
        $request->replace($requestData);

        $validatedData = $request->validate([
            'TraceId' => 'required|string',
            'BoardingPointId' => 'required|integer',
            'DroppingPointId' => 'required|integer', // Fixed spelling
            'ResultIndex' => 'required', // Allow integer or string
            'passenger' => 'required|array',
            'passenger.*.name' => 'required|string',
            'passenger.*.email' => 'required|email',
            'passenger.*.phone' => 'required|string',
            'passenger.*.FirstName' => 'required|string',
            'passenger.*.LastName' => 'required|string',
            'passenger.*.Title' => 'required|string',
            'passenger.*.Age' => 'required|integer',
            'passenger.*.Gender' => 'required|integer',
            'passenger.*.IdType' => 'integer',
            'passenger.*.IdNumber' => 'string',
            'passenger.*.Address' => 'required|string',
            'passenger.*.LeadPassenger' => 'required|boolean',
            'passenger.*.PassengerId' => 'required|integer',
            'passenger.*.Seat' => 'required|array',
        ]);

        // Cast ResultIndex to string for API
        $validatedData['ResultIndex'] = (string) $validatedData['ResultIndex'];

        // Prepare API payload, maintaining original field names
        $searchData = [
            'EndUserIp' => '148.135.137.54',
            'ResultIndex' => $validatedData['ResultIndex'],
            'TraceId' => $validatedData['TraceId'],
            'TokenId' => $token,
            'BoardingPointId' => $validatedData['BoardingPointId'],
            'DroppingPointId' => $validatedData['DroppingPointId'], // Fixed spelling
            'Passenger' => array_map(function ($passenger) {
                // Revert to original field names for API
                return [
                    'FirstName' => $passenger['FirstName'],
                    'LastName' => $passenger['LastName'],
                    'Email' => $passenger['email'],
                    'Phoneno' => $passenger['phone'],
                    'Title' => $passenger['Title'],
                    'Age' => $passenger['Age'],
                    'Gender' => $passenger['Gender'],
                    'IdType' => $passenger['IdType'],
                    'IdNumber' => $passenger['IdNumber'],
                    'Address' => $passenger['Address'],
                    'Seat' => $passenger['Seat'],
                    'LeadPassenger' => $passenger['LeadPassenger'],
                    'PassengerId' => $passenger['PassengerId'],
                ];
            }, $validatedData['passenger']),
        ];

        $bookbus = Http::timeout(90)->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);

        Log::info('Book Bus API Response:', $bookbus->json());

        $errorCode = data_get($bookbus->json(), 'BookResult.Error.ErrorCode');
        $errorMessage = data_get($bookbus->json(), 'BookResult.Error.ErrorMessage');

        if ($errorCode === 6) {
            $token = $this->apiService->authenticate();
            $searchData['TokenId'] = $token;
            $bookbus = Http::timeout(90)->post('https://BusBE.tektravels.com/Busservice.svc/rest/Book', $searchData);
            $errorCode = data_get($bookbus->json(), 'BookResult.Error.ErrorCode');
            $errorMessage = data_get($bookbus->json(), 'BookResult.Error.ErrorMessage');
        }

        if ($errorCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage ?? 'Booking failed',
                'error_code' => $errorCode,
                'response' => $bookbus->json()
            ], 400);
        }

        // Save booking details to database
        $bookingData = $bookbus->json()['BookResult'];
        $booking = BusBooking::create([
            'trace_id' => $bookingData['TraceId'],
            'booking_status' => $bookingData['BusBookingStatus'],
            'invoice_amount' => $bookingData['InvoiceAmount'],
            'invoice_number' => $bookingData['InvoiceNumber'],
            'bus_id' => $bookingData['BusId'],
            'ticket_no' => $bookingData['TicketNo'],
            'travel_operator_pnr' => $bookingData['TravelOperatorPNR'],
            'passenger_details' => json_encode($validatedData['passenger']),
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.booking-confirmation', [
            'booking' => $bookingData,
            'passengers' => $validatedData['passenger'],
        ]);

        $pdfPath = storage_path('app/public/booking_' . $booking->id . '.pdf');
        $pdf->save($pdfPath);

        // Send confirmation email with PDF attachment
        $primaryPassenger = $validatedData['passenger'][0];
        Mail::to($primaryPassenger['email'])->send(new BusBookingConfirmation(
            $bookingData,
            $validatedData['passenger'],
            $pdfPath
        ));

        return response()->json([
            'success' => true,
            'data' => $bookbus->json(),
            'booking_id' => $booking->id
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Illuminate\Http\Client\RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'HTTP request failed',
            'error' => $e->getMessage()
        ], 500);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}
  
   
}