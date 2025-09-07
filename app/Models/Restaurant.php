<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'location',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}