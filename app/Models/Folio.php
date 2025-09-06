<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folio extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'subtotal',
        'tax_amount',
        'service_amount',
        'grand_total',
        'total_payments',
        'balance'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function items()
    {
        return $this->hasMany(FolioItem::class);
    }

    /**
     * Fungsi terpusat untuk menghitung ulang seluruh total pada folio.
     */
    public function recalculate()
    {
        // Muat ulang relasi item untuk mendapatkan data terbaru
        $this->load('items');

        $subtotal = $this->items->where('type', 'charge')->sum('amount');
        $totalPayments = $this->items->where('type', 'payment')->sum('amount');

        $serviceAmount = $subtotal * 0.10; // 10% Service
        $taxAmount = ($subtotal + $serviceAmount) * 0.11; // 11% Pajak dari (Subtotal + Service)

        $grandTotal = $subtotal + $serviceAmount + $taxAmount;
        $balance = $grandTotal - $totalPayments;

        // Update data tanpa memicu event model lagi untuk menghindari loop tak terbatas
        $this->subtotal = $subtotal;
        $this->service_amount = $serviceAmount;
        $this->tax_amount = $taxAmount;
        $this->grand_total = $grandTotal;
        $this->total_payments = $totalPayments;
        $this->balance = $balance;
        $this->saveQuietly(); // Simpan tanpa memicu event
    }
}