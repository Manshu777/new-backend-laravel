<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Bookflights;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Mail\TicketMail;

class TicketBookingController extends Controller
{
    public function testTicketGeneration()
    {
        try {
            // Dummy data mimicking API response
            $dummyData = [
                'Response' => [
                    'PNR' => 'DEMO123',
                    'BookingId' => 999999,
                    'TraceId' => 'a1b2c3d4-5678-90ab-cdef-1234567890ab',
                    'FlightItinerary' => [
                        'Passenger' => [
                            [
                                'Title' => 'Mr',
                                'FirstName' => 'Manshu',
                                'LastName' => 'Maan',
                                'Email' => 'manshu.developer@gmail.com',
                                'ContactNo' => '7988532993',
                                'Ticket' => [
                                    'TicketNumber' => 'DEMO123',
                                    'IssueDate' => now()->toDateTimeString(),
                                ],
                            ],
                        ],
                        'Segments' => [
                            [
                                'Airline' => [
                                    'AirlineName' => 'SpiceJet',
                                    'FlightNumber' => 'SG999',
                                ],
                                'Origin' => [
                                    'Airport' => [
                                        'CityName' => 'Delhi',
                                        'AirportCode' => 'DEL',
                                    ],
                                    'DepTime' => '2025-04-25 06:00:00',
                                ],
                                'Destination' => [
                                    'Airport' => [
                                        'CityName' => 'Bangalore',
                                        'AirportCode' => 'BLR',
                                    ],
                                    'ArrTime' => '2025-04-25 09:00:00',
                                ],
                            ],
                        ],
                        'Fare' => [
                            'BaseFare' => 5000,
                            'Tax' => 1500,
                            'PublishedFare' => 6500,
                        ],
                    ],
                ],
            ];

            // Prepare ticket data
            $ticketData = [
                'pnr' => $dummyData['Response']['PNR'],
                'booking_id' => $dummyData['Response']['BookingId'],
                'user_name' => $dummyData['Response']['FlightItinerary']['Passenger'][0]['Title'] . ' ' . 
                               $dummyData['Response']['FlightItinerary']['Passenger'][0]['FirstName'] . ' ' . 
                               $dummyData['Response']['FlightItinerary']['Passenger'][0]['LastName'],
                'username' => $dummyData['Response']['FlightItinerary']['Passenger'][0]['Email'],
                'phone_number' => $dummyData['Response']['FlightItinerary']['Passenger'][0]['ContactNo'],
                'user_number' => $dummyData['Response']['FlightItinerary']['Passenger'][0]['ContactNo'],
                'flight_name' => $dummyData['Response']['FlightItinerary']['Segments'][0]['Airline']['AirlineName'],
                'flight_number' => $dummyData['Response']['FlightItinerary']['Segments'][0]['Airline']['FlightNumber'],
                'departure_from' => $dummyData['Response']['FlightItinerary']['Segments'][0]['Origin']['Airport']['CityName'] . 
                                   ' (' . $dummyData['Response']['FlightItinerary']['Segments'][0]['Origin']['Airport']['AirportCode'] . ')',
                'flight_date' => $dummyData['Response']['FlightItinerary']['Segments'][0]['Origin']['DepTime'],
                'arrival_to' => $dummyData['Response']['FlightItinerary']['Segments'][0]['Destination']['Airport']['CityName'] . 
                                ' (' . $dummyData['Response']['FlightItinerary']['Segments'][0]['Destination']['Airport']['AirportCode'] . ')',
                'return_date' => null,
                'date_of_booking' => $dummyData['Response']['FlightItinerary']['Passenger'][0]['Ticket']['IssueDate'],
                'initial_response' => json_encode($dummyData),
                'total_fare' => $dummyData['Response']['FlightItinerary']['Fare']['PublishedFare'],
            ];

            // Generate PDF
            $pdf = PDF::loadView('pdf.ticket', ['ticket' => $ticketData]);
            $pdfPath = 'tickets/ticket_' . $ticketData['pnr'] . '.pdf';
            Storage::put($pdfPath, $pdf->output());

            // Save to database
            Bookflights::create([
                'pnr' => $ticketData['pnr'],
                'booking_id' => $ticketData['booking_id'],
                'user_name' => $ticketData['user_name'],
                'username' => $ticketData['username'],
                'phone_number' => $ticketData['phone_number'],
                'user_number' => $ticketData['user_number'],
                'flight_name' => $ticketData['flight_name'],
                'flight_number' => $ticketData['flight_number'],
                'departure_from' => $ticketData['departure_from'],
                'flight_date' => $ticketData['flight_date'],
                'arrival_to' => $ticketData['arrival_to'],
                'return_date' => $ticketData['return_date'],
                'date_of_booking' => $ticketData['date_of_booking'],
                'initial_response' => $ticketData['initial_response'],
                'pdf_path' => $pdfPath,
                'user_id' => 1,
                'trace_id' => $dummyData['Response']['TraceId'],
                'user_ip' => request()->ip(),
                'token' => 'DEMO-TOKEN',
                'refund' => 0,
            ]);

            // Send email to manshu.developer@gmail.com
            Mail::to('manshu.developer@gmail.com')->send(new TicketMail($ticketData, $pdfPath));

            return response()->json([
                'status' => 'success',
                'message' => 'Demo ticket generated and emailed to manshu.developer@gmail.com',
                'data' => $ticketData,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Test Ticket Generation Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while generating the demo ticket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

    