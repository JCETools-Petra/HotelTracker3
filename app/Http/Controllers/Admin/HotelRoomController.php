<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HotelRoomController extends Controller
{
    public function index(Property $property)
    {
        $rooms = $property->hotelRooms()->with('roomType')->latest()->paginate(10);
        return view('admin.hotel_rooms.index', compact('property', 'rooms'));
    }

    public function create(Property $property)
    {
        $roomTypes = $property->roomTypes()->where('type', 'hotel')->get();
        return view('admin.hotel_rooms.create', compact('property', 'roomTypes'));
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:255', Rule::unique('hotel_rooms')->where('property_id', $property->id)->whereNull('deleted_at')],
            'room_type_id' => 'required|exists:room_types,id',
            'status' => 'required|string',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $property->hotelRooms()->create($validated);

        return redirect()->route('admin.properties.hotel-rooms.index', $property)
                         ->with('success', 'Kamar hotel berhasil ditambahkan.');
    }

    public function edit(HotelRoom $hotel_room)
    {
        $property = $hotel_room->property;
        $roomTypes = $property->roomTypes()->where('type', 'hotel')->get();
        return view('admin.hotel_rooms.edit', compact('hotel_room', 'property', 'roomTypes'));
    }

    public function update(Request $request, HotelRoom $hotel_room)
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:255', Rule::unique('hotel_rooms')->where('property_id', $hotel_room->property_id)->whereNull('deleted_at')->ignore($hotel_room->id)],
            'room_type_id' => 'required|exists:room_types,id',
            'status' => 'required|string',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $hotel_room->update($validated);

        return redirect()->route('admin.properties.hotel-rooms.index', $hotel_room->property)
                         ->with('success', 'Kamar hotel berhasil diperbarui.');
    }

    public function destroy(HotelRoom $hotel_room)
    {
        $hotel_room->delete();
        return redirect()->back()->with('success', 'Kamar hotel berhasil dihapus.');
    }
}