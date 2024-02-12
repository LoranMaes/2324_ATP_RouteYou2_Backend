<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'paid',
        'present',
        'reaction',
        'qr_code',
        'checkout_url',
        'carpool',
        'carpool_role',
        'club_name',
        'problem',
        'user_id',
        'event_id',
        'badge_id',
    ];

    protected $hidden = [
        'checkout_url',
        'qr_code'
    ];

    public function badge() {
        return $this->belongsTo(Badge::class);
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }
    public function achievements() {
        return $this->hasMany(Achievement::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
