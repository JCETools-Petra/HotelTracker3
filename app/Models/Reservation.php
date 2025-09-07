<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Reservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'property_id',
        'guest_name',
        'guest_phone',
        'guest_address',
        'checkin_date',
        'checkout_date',
        'source',
        'final_price',
        'user_id',
        'room_type_id',
        'hotel_room_id',
        'segment',
        'status',
        'key_number',
        'checked_in_at',
        'checked_out_at',
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'checkin_date' => 'datetime',
        'checkout_date' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function folio()
    {
        return $this->hasOne(Folio::class);
    }

    // ==========================================================
    // == TAMBAHKAN FUNGSI RELASI BARU DI SINI ==
    // ==========================================================
    /**
     * Mendefinisikan bahwa sebuah Reservasi dimiliki oleh satu Kamar Hotel.
     */
    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }
}