<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\Excludable;

class LevelMeet extends Pivot
{
    use Excludable;
    
    protected $guarded = [];

    public const PIVOT_FIELDS = [
        'id', 'allow_men', 'allow_women', 'registration_fee', 'late_registration_fee',
        'allow_specialist', 'specialist_registration_fee', 'specialist_late_registration_fee',
        'allow_teams', 'team_registration_fee', 'team_late_registration_fee',
        'enable_athlete_limit', 'athlete_limit', 'disabled', 'registration_fee_first'
    ];

    public function athlete_level() {
        return $this->belongsTo(AthleteLevel::class);
    }

    public function athletes() {
        return $this->hasMany(RegistrationAthlete::class, 'level_id');
    }
}
