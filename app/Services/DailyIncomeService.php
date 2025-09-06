<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\DailyIncome;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyIncomeService
{
    /**
     * Memperbarui catatan pendapatan harian untuk setiap hari selama masa inap reservasi.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function updateIncomeFromReservation(Reservation $reservation)
    {
        $propertyId = $reservation->property_id;
        $checkin = Carbon::parse($reservation->checkin_date);
        $checkout = Carbon::parse($reservation->checkout_date);

        // Hitung harga per malam
        $nights = $checkout->diffInDays($checkin);
        $pricePerNight = ($nights > 0) ? ($reservation->final_price / $nights) : $reservation->final_price;

        // Tentukan kolom mana yang akan diupdate berdasarkan segmen reservasi
        $columnMap = $this->getColumnMap($reservation->segment);
        if (!$columnMap) {
            return; // Segmen tidak dikenal, jangan lakukan apa-apa
        }

        // Iterasi untuk setiap hari dari check-in hingga H-1 check-out
        for ($date = $checkin; $date->lessThan($checkout); $date->addDay()) {

            // Gunakan updateOrCreate untuk efisiensi
            $dailyIncome = DailyIncome::firstOrCreate(
                [
                    'property_id' => $propertyId,
                    'date' => $date->toDateString(),
                ],
                [
                    'user_id' => Auth::id() ?? $reservation->user_id,
                ]
            );

            // Gunakan increment untuk menambahkan data baru ke data yang sudah ada
            $dailyIncome->increment($columnMap['rooms'], 1);
            $dailyIncome->increment($columnMap['income'], $pricePerNight);

            // Panggil method untuk menghitung ulang total pendapatan
            $dailyIncome->recalculateTotals();
            $dailyIncome->save();
        }
    }

    /**
     * Peta untuk menentukan kolom database berdasarkan segmen.
     *
     * @param string $segment
     * @return array|null
     */
    protected function getColumnMap(string $segment): ?array
    {
        $map = [
            'Walk In'      => ['rooms' => 'offline_rooms',    'income' => 'offline_room_income'],
            'OTA'          => ['rooms' => 'online_rooms',     'income' => 'online_room_income'],
            'Travel Agent' => ['rooms' => 'ta_rooms',         'income' => 'ta_income'],
            'Government'   => ['rooms' => 'gov_rooms',        'income' => 'gov_income'],
            'Corporation'  => ['rooms' => 'corp_rooms',       'income' => 'corp_income'],

            // ==========================================================
            // == TAMBAHKAN TIGA BARIS BARU DI SINI ==
            // ==========================================================
            'Compliment'   => ['rooms' => 'compliment_rooms', 'income' => 'compliment_income'],
            'House Use'    => ['rooms' => 'house_use_rooms',  'income' => 'house_use_income'],
            'Affiliasi'    => ['rooms' => 'afiliasi_rooms',   'income' => 'afiliasi_room_income'],
        ];

        return $map[$segment] ?? null;
    }

    /**
     * Menghapus atau mengurangi catatan pendapatan harian dari reservasi yang dibatalkan.
     *
     * @param Reservation $reservation
     * @return void
     */
    
    public function removeIncomeFromReservation(Reservation $reservation)
    {
        $propertyId = $reservation->property_id;
        $checkin = Carbon::parse($reservation->checkin_date);
        $checkout = Carbon::parse($reservation->checkout_date);

        $nights = $checkout->diffInDays($checkin);
        $pricePerNight = ($nights > 0) ? ($reservation->final_price / $nights) : $reservation->final_price;

        $columnMap = $this->getColumnMap($reservation->segment);
        if (!$columnMap) {
            return;
        }

        for ($date = $checkin; $date->lessThan($checkout); $date->addDay()) {
            $dailyIncome = DailyIncome::where('property_id', $propertyId)
                                    ->where('date', $date->toDateString())
                                    ->first();

            if ($dailyIncome) {
                // Gunakan decrement untuk mengurangi data
                $dailyIncome->decrement($columnMap['rooms'], 1);
                $dailyIncome->decrement($columnMap['income'], $pricePerNight);

                $dailyIncome->recalculateTotals();
                $dailyIncome->save();
            }
        }
    }
}