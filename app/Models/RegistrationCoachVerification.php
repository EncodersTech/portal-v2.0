<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class RegistrationCoachVerification extends Model
{
    use Excludable;
    
    protected $casts = [
        'coaches' => 'json',
        'results' => 'json',
    ];

    protected $guarded = ['id'];

    public function meet_registration()
    {
        return $this->belongsTo(MeetRegistration::class);
    }

    public function sanctioning_body() {
        return $this->belongsTo(SanctioningBody::class);
    }
}
