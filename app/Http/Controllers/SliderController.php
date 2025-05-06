<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;


class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::all();
        return response()->json($sliders);
    }

    // Store a new slider
    public function store(Request $request)
    {
        $request->validate([
            'slider_img' => 'required|array',
            'slider_img.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'nullable|array',
            'link.*' => 'url',
            'img2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img4' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img_links' => 'nullable|array',
            'img_links.*' => 'url',
        ]);

        // Handle file uploads
        $sliderImages = [];
        if ($request->hasFile('slider_img')) {
            foreach ($request->file('slider_img') as $file) {
                $sliderImages[] = $file->store('uploads/sliders', 'public');
            }
        }

        $slider = Slider::create([
            'slider_img' => $sliderImages,
            'link' => $request->link,
            'img2' => $request->file('img2') ? $request->file('img2')->store('uploads/sliders', 'public') : null,
            'img3' => $request->file('img3') ? $request->file('img3')->store('uploads/sliders', 'public') : null,
            'img4' => $request->file('img4') ? $request->file('img4')->store('uploads/sliders', 'public') : null,
            'img_links' => $request->img_links,
        ]);

        return response()->json(['message' => 'Slider created successfully', 'data' => $slider], 201);
    }

    // Display a specific slider
    public function show($id)
    {
        $slider = Slider::findOrFail($id);
        return response()->json($slider);
    }

    // Update a slider
    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $request->validate([
            'slider_img' => 'nullable|array',
            'slider_img.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'nullable|array',
            'link.*' => 'url',
            'img2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img4' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img_links' => 'nullable|array',
            'img_links.*' => 'url',
        ]);

        // Handle new file uploads
        $sliderImages = $slider->slider_img;
        if ($request->hasFile('slider_img')) {
            foreach ($request->file('slider_img') as $file) {
                $sliderImages[] = $file->store('uploads/sliders', 'public');
            }
        }

        $slider->update([
            'slider_img' => $sliderImages,
            'link' => $request->link ?? $slider->link,
            'img2' => $request->file('img2') ? $request->file('img2')->store('uploads/sliders', 'public') : $slider->img2,
            'img3' => $request->file('img3') ? $request->file('img3')->store('uploads/sliders', 'public') : $slider->img3,
            'img4' => $request->file('img4') ? $request->file('img4')->store('uploads/sliders', 'public') : $slider->img4,
            'img_links' => $request->img_links ?? $slider->img_links,
        ]);

        return response()->json(['message' => 'Slider updated successfully', 'data' => $slider]);
    }

    // Delete a slider
    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
        return response()->json(['message' => 'Slider deleted successfully']);
    }
}
