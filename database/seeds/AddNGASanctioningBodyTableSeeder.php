<?php

use App\Models\SanctioningBody;
use Illuminate\Database\Seeder;

class AddNGASanctioningBodyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SanctioningBody::create([
            'id' => SanctioningBody::NGA,
            'name' => 'National Gym Membership',
            'initialism' => 'NGA'
        ]);
    }
}
