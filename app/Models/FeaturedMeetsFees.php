<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class FeaturedMeetsFees extends Model
{
    use Excludable;

    protected $fillable = [
        'meet_registration_id',
        'fees',
        'fess_in_percentage',
    ];
}
