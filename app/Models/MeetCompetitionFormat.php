<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class MeetCompetitionFormat extends Model
{
    use Excludable;
    
    public const CAPITAL_CUP = 1;
    public const CAPITAL_CUP_JUDGES = 2;
    public const MODIFIED_CAPITAL_CUP = 3;
    public const TRADITIONAL = 4;
    public const MODIFIED_TRADITIONAL = 5;
    public const TRADITIONAL_MODIFIED_TRADITIONAL = 6;
    public const TBA = 7;
    public const WARM_UP_COMPETE = 8;
    public const DOUBLE_CARPET_COMPETITION_WARM_UP_COMPETE = 9;
    public const OTHER = 10;
    
}
