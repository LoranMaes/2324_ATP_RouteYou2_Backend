<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'name',
        'description',
        'image'
    ];

    public function event () {
        return $this->hasOne(Event::class);
    }

    public function participations() {
        return $this->hasMany(Participation::class);
    }
}
