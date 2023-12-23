<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class RegistrationSpecialistEvent extends Model
{
    use Excludable;

    public const STATUS_SPECIALIST_REGISTERED = 1;
    public const STATUS_SPECIALIST_PENDING = 2;
    public const STATUS_SPECIALIST_SCRATCHED = 4;

    protected $guarded = ['id'];

    public function specialist()
    {
        return $this->belongsTo(RegistrationSpecialist::class, 'specialist_id');
    }

    public function specialist_event()
    {
        return $this->belongsTo(AthleteSpecialistEvents::class, 'event_id');
    }

    public function transaction()
    {
        return $this->belongsTo(MeetTransaction::class, 'transaction_id');
    }

    public function net_fee()
    {
        return $this->fee + $this->late_fee - $this->refund - $this->late_refund;
    }

    public function refund_fee()
    {
        return $this->refund + $this->late_refund;
    }
}
