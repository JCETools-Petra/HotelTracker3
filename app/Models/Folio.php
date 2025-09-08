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
    /**
 * Fungsi terpusat untuk menghitung ulang seluruh total pada folio.
 */
    public function recalculate()
    {
        // Muat ulang relasi item untuk mendapatkan data terbaru
        $this->load('items');

        $subtotal = $this->items->where('type', 'charge')->sum('amount');
        $totalPayments = $this->items->where('type', 'payment')->sum('amount');

        // PERBAIKAN: Gunakan persentase dari database, TAPI berikan nilai default jika kosong.
        // Ini membuat sistem bekerja untuk folio lama (yang mungkin persentasenya 0) dan baru.
        $serviceRate = ($this->service_percentage > 0) ? $this->service_percentage / 100 : 0.10; // Default 10%
        $taxRate = ($this->tax_percentage > 0) ? $this->tax_percentage / 100 : 0.11;     // Default 11%

        // Bulatkan setiap hasil perhitungan untuk menghindari sisa desimal
        $serviceAmount = round($subtotal * $serviceRate, 0);
        $taxAmount = round(($subtotal + $serviceAmount) * $taxRate, 0);
        $grandTotal = round($subtotal + $serviceAmount + $taxAmount, 0);
        
        // Saldo dihitung dari angka yang sudah pasti
        $balance = $grandTotal - $totalPayments;

        // Update data dengan nilai yang sudah dibulatkan dan pasti
        $this->subtotal = $subtotal;
        $this->service_amount = $serviceAmount;
        $this->tax_amount = $taxAmount;
        $this->grand_total = $grandTotal;
        $this->total_payments = $totalPayments;
        $this->balance = $balance;
        $this->saveQuietly(); // Simpan tanpa memicu event
    }
}