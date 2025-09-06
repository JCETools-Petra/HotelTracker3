<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioItem extends Model
{
    use HasFactory;

    protected $fillable = ['folio_id', 'description', 'amount', 'type'];

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }
}