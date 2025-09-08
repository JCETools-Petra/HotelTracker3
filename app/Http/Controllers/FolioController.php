<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolioController extends Controller
{
    public function show(Folio $folio)
    {
        // Memuat semua relasi yang dibutuhkan oleh view dalam satu query
        $folio->load('items', 'reservation.hotelRoom.roomType');

        // Mengambil data reservasi dari folio yang sudah di-load
        $reservation = $folio->reservation;

        // Mengirim data yang sudah lengkap ke view
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
        // Validasi bisa ditambahkan di sini jika perlu

        // PERBAIKAN: Gunakan round() untuk membulatkan saldo ke 2 angka desimal sebelum diperiksa.
        // Ini akan mengatasi masalah floating point (angka desimal yang sangat kecil).
        if ($reservation->folio && round($reservation->folio->balance, 2) > 0) {
            return back()->with('error', 'Folio balance must be zero or less to checkout.');
        }

        // 1. Perbarui status reservasi menjadi 'checked-out'
        $reservation->update([
            'status' => 'checked-out',
            'checked_out_at' => now(), // Catat waktu checkout
        ]);

        // 2. Perbarui status kamar menjadi 'dirty' (atau 'vacant dirty')
        if ($reservation->hotelRoom) {
            $reservation->hotelRoom->update(['status' => 'dirty']);
        }

        // Catat aktivitas (jika Anda menggunakan trait LogActivity)
        // $this->logActivity('Checked out guest ' . $reservation->guest_name);

        return redirect()->route('property.frontoffice.index')->with('success', 'Guest has been checked out successfully.');
    }

    public function printReceipt(Reservation $reservation)
    {
        $folio = $reservation->folio()->with('items')->firstOrFail();
        return view('property.folios.print', compact('reservation', 'folio'));
    }
}