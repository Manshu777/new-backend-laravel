<?php

namespace App\Http\Controllers;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        
        $context = "You are a travel assistant chatbot for a tour and travels company. Provide helpful information about destinations, travel tips, tour packages, or booking assistance. Be friendly and concise.";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $context],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 150,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $response->choices[0]->message->content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to process request: ' . $e->getMessage(),
            ], 500);
        }
    }
}