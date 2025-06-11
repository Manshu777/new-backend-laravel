<?php

namespace App\Http\Controllers;

use App\Models\TravelApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TravelApplicationController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validate request directly in the controller
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:15',
            'passport_number' => 'required|string|max:20',
            'passport_expiry_date' => 'required|date_format:d/m/Y',
            'tentative_departure_date' => 'required|date_format:d/m/Y',
            'tentative_return_date' => 'required|date_format:d/m/Y',
            'destination_country' => 'required|string|max:100',
            'purpose_of_visit' => 'required|string|max:255',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'gender' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|string|max:10',

            // ✅ Optional file validation (add limits if needed)
            'passport_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'photograph' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // ✅ Handle file uploads
        $paths = [];
        $files = ['passport_front', 'passport_back', 'photograph', 'supporting_document'];

        foreach ($files as $field) {
            if ($request->hasFile($field)) {
                $paths[$field . '_path'] = $request->file($field)->store('documents', 'public');
            }
        }

        // ✅ Format dates to Carbon instances
        $data['tentative_departure_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_departure_date']);
        $data['tentative_return_date'] = Carbon::createFromFormat('d/m/Y', $data['tentative_return_date']);
        $data['passport_expiry_date'] = Carbon::createFromFormat('d/m/Y', $data['passport_expiry_date']);
        $data['date_of_birth'] = Carbon::createFromFormat('d/m/Y', $data['date_of_birth']);

        // ✅ Merge paths into data
        $data = array_merge($data, $paths);

        // ✅ Store into DB
        $application = TravelApplication::create($data);

        return response()->json([
            'message' => 'Travel application submitted successfully',
            'data' => $application,
        ], 201);
    }
}
