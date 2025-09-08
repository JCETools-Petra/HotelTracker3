<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'status',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Relasi untuk mengambil SEMUA order yang terkait dengan meja ini.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relasi untuk mengambil HANYA order yang statusnya 'pending'.
     * Ini digunakan untuk menentukan apakah meja sedang terisi atau tidak.
     */
    public function pendingOrder()
    {
        return $this->hasOne(Order::class)->where('status', 'pending');
    }
}