<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedAthleteImport extends Model
{
    public const ERROR_CODE_DUPLICATE = -9999;
    public const ERROR_CODE_VALIDATION = -9998;
    public const ERROR_CODE_SERVER = -9997;

    protected $guarded = ['id'];

    protected $dates = ['dob'];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function usag_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'usag_level_id');
    }
    public function nga_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'nga_level_id');
    }

    public function usaigc_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'usaigc_level_id');
    }

    public function aau_level()
    {
        return $this->belongsTo(AthleteLevel::class, 'aau_level_id');
    }
}
