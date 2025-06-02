<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\MessagingResponse;
use Google\Cloud\Dialogflow\V3\WebhookClient;
use App\Models\Flight;
use App\Models\Hotel;

class ChatbotController extends Controller
{
    public function handleTwilioWebhook(Request $request)
    {
        $incomingMessage = $request->input('Body');
        $from = $request->input('From');

        // Send message to Dialogflow CX
        $sessionId = str_replace(':', '', $from); // Sanitize phone number for session ID
        $dialogflow = new WebhookClient([
            'project_id' => 'your-project-id',
            'agent_id' => 'your-agent-id',
            'session_id' => $sessionId,
        ]);
        $response = $dialogflow->detectIntent($incomingMessage);

        // Get Dialogflow response
        $fulfillmentText = $response->getQueryResult()->getFulfillmentText();

        // Create TwiML response
        $twiml = new MessagingResponse();
        $twiml->message($fulfillmentText);

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    public function handleDialogflowWebhook(Request $request)
    {
        $intent = $request->input('queryResult.intent.displayName');
        $parameters = $request->input('queryResult.parameters');

        $responseText = '';

        switch ($intent) {
            case 'book_flight':
                $destination = $parameters['destination'] ?? 'Unknown';
                $date = $parameters['date'] ?? '2025-06-10';
                $flights = Flight::where('destination', $destination)
                    ->where('date', $date)
                    ->get();
                if ($flights->isNotEmpty()) {
                    $responseText = "Available flights to $destination on $date:\n";
                    foreach ($flights as $flight) {
                        $responseText .= "Flight {$flight->flight_number}: \${$flight->price}\n";
                    }
                } else {
                    $responseText = "No flights found for $destination on $date.";
                }
                break;

            case 'book_hotel':
                $location = $parameters['location'] ?? 'Unknown';
                $guests = $parameters['guests'] ?? 1;
                $hotels = Hotel::where('location', $location)
                    ->where('capacity', '>=', $guests)
                    ->get();
                if ($hotels->isNotEmpty()) {
                    $responseText = "Available hotels in $location for $guests guests:\n";
                    foreach ($hotels as $hotel) {
                        $responseText .= "{$hotel->name} (Rating: {$hotel->rating})\n";
                    }
                } else {
                    $responseText = "No hotels found in $location for $guests guests.";
                }
                break;

            case 'escalate':
                $responseText = "Connecting you to a live agent. Please hold.";
                // Initiate a call using Twilio
                $twilio = new \Twilio\Rest\Client(
                    env('TWILIO_ACCOUNT_SID'),
                    env('TWILIO_AUTH_TOKEN')
                );
                $twilio->calls->create(
                    $request->input('sessionInfo.parameters.from'), // User's number
                    env('TWILIO_PHONE_NUMBER'),
                    ['url' => 'https://your-laravel-app.com/api/voice-webhook']
                );
                break;

            default:
                $responseText = "Sorry, I didn't understand that. Could you clarify?";
        }

        return response()->json([
            'fulfillmentText' => $responseText
        ]);
    }
}