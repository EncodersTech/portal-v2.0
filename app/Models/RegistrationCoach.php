<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class RegistrationCoach extends Model
{
    use Excludable;

    public const STATUS_REGISTERED = 1;
    public const STATUS_PENDING_NON_RESERVED = 2;
    public const STATUS_PENDING_RESERVED = 3;
    public const STATUS_SCRATCHED = 4;

    protected $guarded = ['id'];
    protected $dates = ['dob'];

    public function meet_registration()
    {
        return $this->belongsTo(MeetRegistration::class);
    }

    public function tshirt()
    {
        return $this->belongsTo(ClothingSize::class, 'tshirt_size_id');
    }

    public function transaction()
    {
        return $this->belongsTo(MeetTransaction::class, 'transaction_id');
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
