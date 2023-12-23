<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class AthleteSpecialistEvents extends Model
{
    use Excludable;

    public const IGC_VAULT = 2001;
    public const IGC_BARS = 2002;
    public const IGC_BEAM = 2003;
    public const IGC_FLOOR = 2004;

    public function sanctioning_body() {
        return $this->belongsTo(SanctioningBody::class);
    }

    public function events() {
        return $this->hasMany(RegistrationSpecialistEvent::class, 'event_id');
    }
}
