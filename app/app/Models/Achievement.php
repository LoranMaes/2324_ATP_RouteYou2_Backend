<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'completed',
        'checkpoint_id',
        'participation_id',
    ];

    public function checkpoint() {
        return $this->belongsTo(Checkpoint::class);
    }

    public function participation() {
        return $this->belongsTo(Participation::class);
    }
}
