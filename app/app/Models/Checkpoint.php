<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'longitude',
        'latitude',
        'coin',
        'qr_code',
        'route_id',
    ];

    public $hidden = [
        'route',
        'qr_code',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
