<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('routes')->insert([
            'routeyou_route_id' => '13172217',
            'event_id' => Event::all()->random()->id,
        ]);
        DB::table('routes')->insert([
            'routeyou_route_id' => '13581677',
            'event_id' => Event::all()->random()->id,
        ]);
        DB::table('routes')->insert([
            'routeyou_route_id' => '13415301',
            'event_id' => Event::all()->random()->id,
        ]);
        DB::table('routes')->insert([
            'routeyou_route_id' => '6431032',
            'event_id' => Event::all()->random()->id,
        ]);
        DB::table('routes')->insert([
            'routeyou_route_id' => '13695152',
            'event_id' => Event::all()->random()->id,
        ]);
        DB::table('routes')->insert([
            'routeyou_route_id' => '13716668',
            'event_id' => Event::all()->random()->id,
        ]);
    }
}
