<?php

use Illuminate\Database\Seeder;
use App\Models\SanctioningBody;
use App\Models\AthleteSpecialistEvents;

class AthleteSpecialistEventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedIGCSpecialistLevels();
    }

    /* USAIGC Levels */
    public function seedIGCSpecialistLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::USAIGC);

        $body->specialist_events()->create([
            'id' => AthleteSpecialistEvents::IGC_VAULT,
            'name' => 'Vault',
            'abbreviation' => 'Vt',
        ]);

        $body->specialist_events()->create([
            'id' => AthleteSpecialistEvents::IGC_BARS,
            'name' => 'Bars',
            'abbreviation' => 'Br',
            'male' => false
        ]);

        $body->specialist_events()->create([
            'id' => AthleteSpecialistEvents::IGC_BEAM,
            'name' => 'Beam',
            'abbreviation' => 'Bm',
            'male' => false
        ]);

        $body->specialist_events()->create([
            'id' => AthleteSpecialistEvents::IGC_FLOOR,
            'name' => 'Floor',
            'abbreviation' => 'Fx',
        ]);
    }
}