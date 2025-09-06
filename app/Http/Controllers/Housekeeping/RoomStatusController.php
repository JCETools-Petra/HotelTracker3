<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomStatusController extends Controller
{
    /**
     * Menampilkan daftar semua status kamar untuk Housekeeping.
     */
    public function index()
    {
        $property = Auth::user()->property;
        if (!$property) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan properti manapun.');
        }

        $rooms = $property->hotelRooms()
            ->with('roomType')
            ->orderBy('room_number', 'asc')
            ->get()
            ->groupBy('status'); // Kelompokkan kamar berdasarkan status

        return view('housekeeping.room_status.index', compact('property', 'rooms'));
    }

    /**
     * Memperbarui status sebuah kamar.
     */
    public function update(Request $request, HotelRoom $room)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                HotelRoom::STATUS_TERSEDIA,
                HotelRoom::STATUS_KOTOR,
                HotelRoom::STATUS_PEMBERSIHAN,
                HotelRoom::STATUS_PERBAIKAN,
            ]),
        ]);

        $room->update(['status' => $validated['status']]);

        // Tambahkan Log Aktivitas (Opsional tapi direkomendasikan)
        // LogActivity::add('Mengubah status kamar ' . $room->room_number . ' menjadi ' . $validated['status']);

        return back()->with('success', "Status kamar {$room->room_number} berhasil diubah menjadi '{$validated['status']}'.");
    }
}