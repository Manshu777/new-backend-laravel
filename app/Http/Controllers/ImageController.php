<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload the image to S3
        $file = $request->file('image');
        $filePath = 'uploads/' . uniqid() . '-' . $file->getClientOriginalName();

        $path = Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

        if ($path) {
            $url = Storage::disk('s3')->url($filePath);
            return response()->json(['message' => 'Image uploaded successfully', 'url' => $url]);
        } else {
            return response()->json(['message' => 'Image upload failed'], 500);
        }
    }
}
