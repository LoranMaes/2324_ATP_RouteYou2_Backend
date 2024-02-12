<?php

namespace Database\Seeders;

use App\Models\Organisation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // * 1 organisatie met 3 beheerders(user)
        Organisation::factory()->hasOrganisers(3)->create();

        // * 3 organisaties met telkens maar 1 beheerder(user)
        for ($i = 0; $i < 3; $i++) {
            Organisation::factory()->hasOrganisers()->create();
        }

        // * 1 organisatie met 2 beheerders(user) en 10 volgers(user)
        Organisation::factory()->hasOrganisers(2)->hasFollowers(10)->create();
    }
}
