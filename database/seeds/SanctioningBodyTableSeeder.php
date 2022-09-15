<?php

use Illuminate\Database\Seeder;
use App\Models\SanctioningBody;

class SanctioningBodyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SanctioningBody::create([
            'id' => SanctioningBody::USAG,
            'name' => 'USA Gymnastics',
            'initialism' => 'USAG'
        ]);

        SanctioningBody::create([
            'id' => SanctioningBody::USAIGC,
            'name' => 'United States Association of Independent Gymnastics Clubs',
            'initialism' => 'USAIGC'
        ]);

        SanctioningBody::create([
            'id' => SanctioningBody::AAU,
            'name' => 'Amateur Athletic Union',
            'initialism' => 'AAU'
        ]);
    }
}
