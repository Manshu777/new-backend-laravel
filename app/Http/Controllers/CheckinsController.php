<?php

namespace App\Http\Controllers;

use App\Models\CheckedIns;
use Illuminate\Http\Request;

class CheckinsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookedHotels = CheckedIns::all(); // Fetch all booked hotels
        return response()->json($bookedHotels); // Return as JSON
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return a view for creating a booking
        return view('checkins.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'hotel_id' => 'required|integer',
            'user_id' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'user_number' => 'required|string|max:255',
            'hotel_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'room_type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'date_of_booking' => 'required|date',
            'initial_response' => 'nullable|string',
            'refund' => 'required|boolean',
            'response' => 'nullable|string',
        ]);
    
        $booking = new CheckedIns([
            'hotel_id' => $request->hotel_id,
            'user_id' => $request->user_id,
            'user_name' => $request->user_name,
            'user_number' => $request->user_number,
            'hotel_name' => $request->hotel_name,
            'location' => $request->location,
            'address' => $request->address,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'room_type' => $request->room_type,
            'price' => $request->price,
            'date_of_booking' => $request->date_of_booking,
            'initial_response' => $request->initial_response,
            'refund' => $request->refund,
            'response' => $request->response,
        ]);
    
        // Save the booking
        $booking->save();
    
        return response()->json(['message' => 'Booking created successfully!', 'data' => $booking], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $booking = CheckedIns::findOrFail($id); // Find the booking by ID
        return response()->json($booking); // Return booking details
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = CheckedIns::findOrFail($id); // Find the booking
        return view('checkins.edit', compact('booking')); // Return the edit view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_number' => 'required|string|max:255',
            'hotel_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'room_type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'date_of_booking' => 'required|date',
            'initial_response' => 'nullable|string',
            'refund' => 'required|boolean',
            'response' => 'nullable|string',
        ]);

        $checkedIn = CheckedIns::findOrFail($id); // Find the booking
        $checkedIn->update($validated); // Update the booking

        return response()->json(['message' => 'Booking updated successfully', 'data' => $checkedIn]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $checkedIn = CheckedIns::findOrFail($id); // Find the booking
        $checkedIn->delete(); // Delete the booking

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
