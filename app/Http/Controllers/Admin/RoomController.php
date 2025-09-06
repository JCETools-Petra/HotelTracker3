<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Property $property)
    {
        $rooms = $property->rooms()->whereHas('roomType', function ($query) {
            $query->where('type', 'mice');
        })->with('roomType')->latest()->paginate(10);

        return view('admin.rooms.index', compact('property', 'rooms'));
    }

    public function create(Property $property)
    {
        $roomTypes = RoomType::where('type', 'mice')->get();
        return view('admin.rooms.create', compact('property', 'roomTypes'));
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $property->rooms()->create($validated);

        return redirect()->route('admin.properties.rooms.index', $property)
                         ->with('success', 'Ruangan MICE berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        $property = $room->property;
        $roomTypes = RoomType::where('type', 'mice')->get();
        return view('admin.rooms.edit', compact('room', 'property', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $room->update($validated);

        return redirect()->route('admin.properties.rooms.index', $room->property)
                         ->with('success', 'Ruangan MICE berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $property = $room->property;
        $room->delete();

        return redirect()->route('admin.properties.rooms.index', $property)
                         ->with('success', 'Ruangan MICE berhasil dihapus.');
    }
}