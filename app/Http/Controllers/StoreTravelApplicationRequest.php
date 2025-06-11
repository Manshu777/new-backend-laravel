<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelApplicationRequest;
use App\Models\TravelApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TravelApplicationController extends Controller
{
    public function store(StoreTravelApplicationRequest $request)
    {
        // Handle file uploads
        $files = [
            'passport_front' => $request->file('passport_front'),
            'passport_back' => $request->file('passport_back'),
            'photograph' => $request->file('photograph'),
            'supporting_document' => $request->file('supporting_document'),
        ];

        $paths = [];
        foreach ($files as $key => $file) {
            if ($file) {
                $paths[$key . '_path'] = $file->store('documents', 'public');
            }
        }

        // Prepare data for storage
        $data = $request->validated();
        $data['tentative_departure_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['tentative_departure_date']);
        $data['tentative_return_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['tentative_return_date']);
        $data['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['date_of_birth']);
        $data = array_merge($data, $paths);

        // Create the travel application
        $application = TravelApplication::create($data);

        return response()->json([
            'message' => 'Travel application submitted successfully',
            'data' => $application,
        ], 201);
    }
}
