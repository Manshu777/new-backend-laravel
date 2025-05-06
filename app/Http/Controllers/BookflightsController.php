<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookflightsController extends Controller
{
    public function index()
    {
        $flights = BookflightsController::with('user')->get();
        return view('bookflights.index', compact('flights'));
    }

    public function create()
    {
        return view('bookflights.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'flight_name' => 'required|string|max:255',
            'flight_number' => 'required|string|max:255',
            'departure_from' => 'required|string|max:255',
            'arrival_to' => 'required|string|max:255',
            'flight_date' => 'required|date',
            'return_date' => 'nullable|date|after:flight_date',
            'refund' => 'required|boolean',
        ]);

        BookflightsController::create($validated);
        return redirect()->route('bookflights.index')->with('success', 'Flight booked successfully.');
    }

    public function edit($id)
    {
        $flight = BookflightsController::findOrFail($id);
        return view('bookflights.edit', compact('flight'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'flight_name' => 'required|string|max:255',
            'flight_number' => 'required|string|max:255',
            'departure_from' => 'required|string|max:255',
            'arrival_to' => 'required|string|max:255',
            'flight_date' => 'required|date',
            'return_date' => 'nullable|date|after:flight_date',
            'refund' => 'required|boolean',
        ]);

        $flight = BookflightsController::findOrFail($id);
        $flight->update($validated);
        return redirect()->route('bookflights.index')->with('success', 'Flight updated successfully.');
    }

    public function destroy($id)
    {
        $flight = BookflightsController::findOrFail($id);
        $flight->delete();
        return redirect()->route('bookflights.index')->with('success', 'Flight deleted successfully.');
    }
}
