<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolioController extends Controller
{
    public function show(Reservation $reservation)
    {
        $folio = $reservation->folio()->with('items')->firstOrFail();
        return view('property.folios.show', compact('reservation', 'folio'));
    }

    public function addCharge(Request $request, Folio $folio)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $folio->items()->create([
            'description' => $data['description'],
            'amount' => $data['amount'],
            'type' => 'charge',
        ]);

        $folio->recalculate();

        return back()->with('success', 'Tagihan berhasil ditambahkan.');
    }

    /**
     * Logika Pembayaran yang Sudah Diperbaiki
     */
    public function addPayment(Request $request, Folio $folio)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        // Selalu catat jumlah pembayaran penuh yang diinput
        $folio->items()->create([
            'description' => $data['description'],
            'amount' => $data['amount'],
            'type' => 'payment',
        ]);

        $folio->recalculate(); // Panggil mesin hitung

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function processCheckout(Request $request, Reservation $reservation)
    {
        $folio = $reservation->folio;
        // Diperbarui: Izinkan checkout jika saldo 0 atau kurang (ada kembalian)
        if ($folio->balance > 0) {
            return back()->with('error', 'Check-out tidak dapat diproses. Saldo tagihan masih belum lunas.');
        }
        $reservation->update([
            'status' => 'Checked-out',
            'checked_out_at' => now(),
            'checkout_date' => now(),
        ]);
        if ($reservation->hotelRoom) {
            $reservation->hotelRoom->update(['status' => \App\Models\HotelRoom::STATUS_KOTOR]);
        }
        return redirect()->route('property.frontoffice.index')
                         ->with('success', "Tamu '{$reservation->guest_name}' berhasil Check-out.");
    }

    public function printReceipt(Reservation $reservation)
    {
        $folio = $reservation->folio()->with('items')->firstOrFail();
        return view('property.folios.print', compact('reservation', 'folio'));
    }
}