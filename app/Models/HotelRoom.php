<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RoomType; // <-- PERBAIKAN ADA DI BARIS INI

class HotelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public const STATUS_TERSEDIA = 'Tersedia';
    public const STATUS_TERISI = 'Terisi';
    public const STATUS_KOTOR = 'Kotor';
    public const STATUS_PEMBERSIHAN = 'Pembersihan';
    public const STATUS_PERBAIKAN = 'Perbaikan';

    protected $fillable = [
        'property_id',
        'room_type_id',
        'room_number',
        'status',
        'capacity',
        'notes',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
}