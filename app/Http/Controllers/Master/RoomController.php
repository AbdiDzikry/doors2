<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::paginate(10);
        return view('master.rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.rooms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'facilities' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'floor' => 'nullable|string|max:255', // Added validation for floor
            'status' => 'required|in:available,under_maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $validatedData['image_path'] = $request->file('image')->store('rooms', 'local');
            }
    
            Room::create($validatedData);
    
            return redirect()->route('master.rooms.create')
                            ->with('message','Room created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating room: ' . $e->getMessage());
            return redirect()->route('master.rooms.create')
                            ->with('error','Failed to create room. Please check the logs for details.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        return view('master.rooms.edit', compact('room'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'facilities' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'floor' => 'nullable|string|max:255', // Added validation for floor
            'status' => 'required|in:available,under_maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($room->image_path) {
                    Storage::disk('local')->delete($room->image_path);
                }
                $validatedData['image_path'] = $request->file('image')->store('rooms', 'local');
            }

            $room->update($validatedData);

            return redirect()->route('master.rooms.edit', $room->id)
                            ->with('message','Room updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return redirect()->route('master.rooms.edit', $room->id)
                            ->with('error','Failed to update room. Please check the logs for details.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if ($room->image_path) {
            Storage::disk('local')->delete($room->image_path);
        }
        $room->delete();

        return redirect()->route('master.rooms.index')
                        ->with('success','Room deleted successfully');
    }

    public function getImage($filename)
    {
        $path = 'rooms/' . $filename;

        $disk = null;
        if (Storage::disk('local')->exists($path)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = Storage::disk('public');
        } else {
            abort(404);
        }

        $file = $disk->get($path);
        $type = $disk->mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        $response->header("Cache-Control", "public, max-age=31536000"); // Cache for 1 year
        $response->header("Expires", gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

        return $response;
    }
}