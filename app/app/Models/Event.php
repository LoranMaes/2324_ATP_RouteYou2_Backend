<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'description',
        'start',
        'end',
        'price',
        'max_participant',
        'city',
        'zip',
        'street',
        'house_number',
        'visible',
        'image',
        'type',
        'latitude',
        'longitude',
        'organisation_id',
        'badge_id'
    ];

    protected $appends = [
        'slug',
        'status',
        'going_count'
    ];

    protected $hidden = [
        'pivot'
    ];

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return Str::slug($attributes['title'] . ' ' . $attributes['id'], '-');
            }
        );
    }

    protected function status(): Attribute{
        return Attribute::make(
            get: function ($value, $attributes) {
                $now = now('GMT+1');
                $start = $attributes['start'];
                $end = $attributes['end'];

                if ($now < $start) {
                    return 'UPCOMING';
                } else if ($now > $end) {
                    return 'FINISHED';
                } else {
                    return 'ONGOING';
                }
            }
        );
    }

    protected function goingCount(): Attribute {
        return Attribute::make(
            get: function ($value, $attributes) {
                // get all participations where type is going and count them
                return $this->participations()->where('reaction', 'GOING')->count();

            }
        );
    }
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'participations');
    }
}
