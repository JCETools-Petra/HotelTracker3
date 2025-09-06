<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index(Property $property)
    {
        $roomTypes = $property->roomTypes()->where('type', 'hotel')->latest()->paginate(10);
        return view('admin.room_types.index', compact('property', 'roomTypes'));
    }

    public function create(Property $property)
    {
        return view('admin.room_types.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedData['type'] = 'hotel'; // Ini akan otomatis mengatur tipe sebagai 'hotel'

        $property->roomTypes()->create($validatedData);

        return redirect()->route('admin.properties.room-types.index', $property)
                         ->with('success', 'Tipe kamar hotel berhasil ditambahkan.');
    }

    public function edit(RoomType $roomType)
    {
        return view('admin.room_types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $roomType->update($validatedData);

        return redirect()->route('admin.properties.room-types.index', $roomType->property)
                         ->with('success', 'Tipe kamar hotel berhasil diperbarui.');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->hotelRooms()->exists()) {
            return redirect()->back()->with('error', 'Tipe kamar tidak dapat dihapus karena masih digunakan oleh kamar.');
        }

        $property = $roomType->property;
        $roomType->delete();

        return redirect()->route('admin.properties.room-types.index', $property)
                         ->with('success', 'Tipe kamar hotel berhasil dihapus.');
    }
}