<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'routeyou_route_id',
        'event_id'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function checkpoints() {
        return $this->hasMany(Checkpoint::class);
    }
}
