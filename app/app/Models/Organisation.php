<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description'
    ];

    public function organisers() {
        return $this->hasMany(User::class);
    }

    public function followers() {
        return $this->belongsToMany(User::class, 'followers');
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}

