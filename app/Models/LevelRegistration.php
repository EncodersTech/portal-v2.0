<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LevelRegistration extends Pivot
{
    use Excludable;

    public const PIVOT_FIELDS = [
        'id', 'allow_men', 'allow_women', 'registration_fee', 'late_registration_fee',
        'allow_specialist', 'specialist_registration_fee', 'specialist_late_registration_fee',
        'allow_teams', 'team_registration_fee', 'team_late_registration_fee',
        'enable_athlete_limit', 'athlete_limit', 'has_team', 'was_late', 'team_fee', 'team_late_fee',
        'team_refund', 'team_late_refund', 'disabled'
    ];

    public function registration() {
        return $this->hasOne(MeetRegistration::class, 'meet_registration_id');
    }

    public function athletes() {
        return $this->hasMany(RegistrationAthlete::class, 'level_registration_id');
    }

    public function specialists() {
        return $this->hasMany(RegistrationSpecialist::class, 'level_registration_id');
    }

    public function level() {
        return $this->belongsTo(AthleteLevel::class, 'level_id');
    }

    public function teamPaidFor() : bool
    {
        $fee = $this->team_fee - $this->team_refund;
        $lateFee = $this->team_late_fee - $this->team_late_refund;
        return ($fee + $lateFee) > 0;
    }

    public function net_fee()
    {
        return $this->team_fee + $this->team_late_fee - $this->team_refund - $this->team_late_refund;
    }

    public function refund_fee()
    {
        return $this->team_refund + $this->team_late_refund;
    }

    public function _uid() : string
    {
        return $this->level->id . ($this->allow_men ? '-m' : '') . ($this->allow_women ? '-f' : '');
    }
}
