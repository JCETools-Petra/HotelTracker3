<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\DailyIncomeService;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // 1. Buat Folio baru
        $folio = $reservation->folio()->create([]);

        // 2. Tambahkan biaya kamar sebagai item pertama
        if ($reservation->final_price > 0) {
            $folio->items()->create([
                'description' => 'Room Charge',
                'amount' => $reservation->final_price,
                'type' => 'charge',
            ]);
        }

        // 3. Panggil mesin hitung untuk mengkalkulasi semuanya dengan benar
        $folio->recalculate();

        // 4. Update pendapatan harian
        (new \App\Services\DailyIncomeService())->updateIncomeFromReservation($reservation);
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        //
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleting(Reservation $reservation): void
    {
        // Panggil service untuk menghapus/mengurangi pendapatan
        (new DailyIncomeService())->removeIncomeFromReservation($reservation);

        // Ubah status kamar kembali menjadi 'Tersedia'
        if ($reservation->hotelRoom) {
            $reservation->hotelRoom->update(['status' => 'Tersedia']);
        }
    }

    /**
     * Handle the Reservation "restored" event.
     */
    public function restored(Reservation $reservation): void
    {
        //
    }

    /**
     * Handle the Reservation "force deleted" event.
     */
    public function forceDeleted(Reservation $reservation): void
    {
        //
    }
}
