<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class RegistrationAthleteVerification extends Model
{
    use Excludable;
    
    protected $casts = [
        'athletes' => 'json',
        'results' => 'json',
    ];

    protected $guarded = ['id'];

    public const VERIFICATION_PROCESSING = 1;
    public const VERIFICATION_DONE = 2;

    public function meet_registration()
    {
        return $this->belongsTo(MeetRegistration::class);
    }

    public function sanctioning_body() {
        return $this->belongsTo(SanctioningBody::class);
    }
}
