<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class RegistrationSpecialist extends Model
{
    use Excludable;

    public const STATUS_MIXED = -1;

    protected $guarded = ['id'];
    protected $dates = ['dob'];

    public function meet_registration()
    {
        return $this->belongsTo(MeetRegistration::class);
    }

    public function registration_level() {
        return $this->belongsTo(LevelRegistration::class, 'level_registration_id');
    }

    public function tshirt()
    {
        return $this->belongsTo(ClothingSize::class, 'tshirt_size_id');
    }

    public function leo()
    {
        return $this->belongsTo(ClothingSize::class, 'leo_size_id');
    }

    public function events()
    {
        return $this->hasMany(RegistrationSpecialistEvent::class, 'specialist_id');
    }

    public function hasPendingEvents() {
        return ($this->events()
                        ->where('status', RegistrationSpecialistEvent::STATUS_SPECIALIST_PENDING)
                        ->count()
                ) > 0;
    }

    public function status() {
        $statuses = $this->events()->select('status')->groupBy('status')->get();

        if (count($statuses) > 1)
            return self::STATUS_MIXED;

        return $statuses[0]->status;
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function net_fee()
    {
        $total = 0;

        foreach ($this->events as $evt) { /** @var RegistrationSpecialistEvent $evt */
            $total += $evt->net_fee();
        }
        return $total;
    }

    public function refund_fee()
    {
        $total = 0;

        foreach ($this->events as $evt) { /** @var RegistrationSpecialistEvent $evt */
            $total += $evt->refund_fee();
        }
        return $total;
    }
}
