<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class SanctioningBody extends Model
{
    use Excludable;

    public const All = 0;
    public const USAG = 1;
    public const USAIGC = 2;
    public const AAU = 3;
    public const NGA = 4;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    const SANCTION_BODY = [
        self::USAG => 'USAG',
        self::USAIGC => 'USAIGC',
        self::AAU => 'AAU',
        self::NGA => 'NGA',
    ];

    public function levels()
    {
        return $this->hasMany(AthleteLevel::class);
    }

    public function specialist_events()
    {
        return $this->hasMany(AthleteSpecialistEvents::class);
    }
}
