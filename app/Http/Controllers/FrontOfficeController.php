<?php

namespace App\Http\Controllers;

use App\Models\HotelRoom;
use App\Models\Reservation;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FrontOfficeController extends Controller
{
    /**
     * Menampilkan room rack visual dengan logika yang sudah diperbaiki.
     */
    public function index(Request $request)
    {
        $property = Auth::user()->property;
        if (!$property) {
            abort(403, 'Akun Anda tidak terikat pada properti manapun.');
        }

        $viewDate = Carbon::parse($request->input('date', 'today'))->startOfDay();

        // 1. Ambil semua kamar hotel untuk properti ini, jadikan ID sebagai key.
        $hotelRooms = HotelRoom::where('property_id', $property->id)
            ->with('roomType')
            ->orderBy('room_number', 'asc')
            ->get()
            ->keyBy('id');

        // 2. Ambil HANYA reservasi yang aktif pada tanggal yang sedang dilihat.
        $activeReservations = Reservation::where('property_id', $property->id)
            ->where('checkin_date', '<=', $viewDate->endOfDay())
            ->where('checkout_date', '>', $viewDate->startOfDay())
            ->get();

        // 3. Secara manual, pasangkan setiap reservasi aktif ke kamarnya masing-masing.
        foreach ($activeReservations as $reservation) {
            if (isset($hotelRooms[$reservation->hotel_room_id])) {
                $hotelRooms[$reservation->hotel_room_id]->setRelation('reservations', collect([$reservation]));
            }
        }
        
        $availableRoomTypes = RoomType::where('property_id', $property->id)->where('type', 'hotel')->get();

        return view('property.frontoffice.index', [
            'property' => $property,
            'hotelRooms' => $hotelRooms,
            'viewDate' => $viewDate,
            'availableRoomTypes' => $availableRoomTypes,
        ]);
    }

    /**
     * Menyimpan reservasi baru dari form Front Office.
     */
    public function storeReservation(Request $request)
    {
        $property = Auth::user()->property;
        if (!$property) {
            abort(403);
        }

        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'nullable|string|max:25',
            'guest_address' => 'nullable|string|max:500',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after_or_equal:checkin_date',
            'segment' => 'required|string',
            'final_price' => [
                Rule::requiredIf(function () use ($request) {
                    return !in_array($request->input('segment'), ['Compliment', 'House Use']);
                }),
                'nullable', 'numeric', 'min:0',
            ],
        ]);

        $validated['final_price'] = $validated['final_price'] ?? 0;
        
        $hotelRoom = HotelRoom::findOrFail($validated['hotel_room_id']);

        $reservation = Reservation::create([
            'property_id' => $property->id,
            'user_id' => Auth::id(),
            'hotel_room_id' => $validated['hotel_room_id'],
            'room_type_id' => $hotelRoom->room_type_id,
            'guest_name' => $validated['guest_name'],
            'guest_phone' => $validated['guest_phone'],
            'guest_address' => $validated['guest_address'],
            'checkin_date' => $validated['checkin_date'],
            'checkout_date' => $validated['checkout_date'],
            'segment' => $validated['segment'],
            'final_price' => $validated['final_price'],
            'status' => 'Booked',
        ]);

        return redirect()->route('property.frontoffice.index', ['date' => $validated['checkin_date']])
            ->with('success', 'Reservasi untuk ' . $validated['guest_name'] . ' berhasil dibuat.');
    }
    
    /**
     * Memproses check-in tamu yang sudah memesan.
     */
    public function checkIn(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'key_number' => 'nullable|string|max:50',
        ]);

        $reservation->update([
            'status' => 'Checked-in',
            'checked_in_at' => now(),
            'key_number' => $data['key_number'],
        ]);

        if ($reservation->hotelRoom) {
            $reservation->hotelRoom->update(['status' => 'Terisi']);
        }

        return redirect()->route('property.frontoffice.index', ['date' => $reservation->checkin_date->toDateString()])
            ->with('success', "Tamu '{$reservation->guest_name}' berhasil Check-in.");
    }

    /**
     * Memproses pembatalan reservasi.
     */
    public function cancel(Reservation $reservation)
    {
        if ($reservation->status !== 'Booked') {
            return back()->with('error', 'Hanya reservasi yang belum check-in yang bisa dibatalkan.');
        }
        
        $guestName = $reservation->guest_name;
        $reservation->delete();

        return redirect()->route('property.frontoffice.index')
            ->with('success', "Reservasi untuk '{$guestName}' berhasil dibatalkan.");
    }

    /**
     * Mengubah status kamar secara manual dari Front Office.
     */
    public function updateRoomStatus(Request $request, HotelRoom $room)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . \App\Models\HotelRoom::STATUS_TERSEDIA,
        ]);

        $room->update(['status' => $validated['status']]);

        return back()->with('success', "Status kamar {$room->room_number} berhasil diubah menjadi 'Tersedia'.");
    }
}