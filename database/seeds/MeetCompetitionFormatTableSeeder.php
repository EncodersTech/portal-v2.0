<?php

use Illuminate\Database\Seeder;
use App\Models\MeetCompetitionFormat;

class MeetCompetitionFormatTableSeeder extends Seeder
{
    private const FORMATS = [
        MeetCompetitionFormat::CAPITAL_CUP => 'Capital Cup',
        MeetCompetitionFormat::CAPITAL_CUP_JUDGES => 'Capital Cup with Judges Critique',
        MeetCompetitionFormat::MODIFIED_CAPITAL_CUP => 'Modified Capital Cup',
        MeetCompetitionFormat::TRADITIONAL => 'Traditional',
        MeetCompetitionFormat::MODIFIED_TRADITIONAL => 'Modified Traditional',
        MeetCompetitionFormat::TRADITIONAL_MODIFIED_TRADITIONAL => 'Traditional / Modified Traditional',
        MeetCompetitionFormat::TBA => 'TBA',
        MeetCompetitionFormat::WARM_UP_COMPETE => 'Warm-up Compete',
        MeetCompetitionFormat::DOUBLE_CARPET_COMPETITION_WARM_UP_COMPETE => 'Double Carpet Competition / Warmup-Compete',
        MeetCompetitionFormat::OTHER => 'Other',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::FORMATS as $id => $name) {
            MeetCompetitionFormat::create([
                'id' => $id,
                'name' => $name
            ]);
        }        
    }
}
