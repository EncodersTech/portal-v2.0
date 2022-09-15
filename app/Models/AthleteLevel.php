<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Excludable;

class AthleteLevel extends Model
{
    use Excludable;

    /* USAG Levels */
    public const USAG_ = 1000;

    public const USAG_GYMNASTICS_WOMEN_BRONZE = 1001;
    public const USAG_GYMNASTICS_WOMEN_SILVER = 1002;
    public const USAG_GYMNASTICS_WOMEN_GOLD = 1003;
    public const USAG_GYMNASTICS_WOMEN_DIAMOND = 1004;
    public const USAG_GYMNASTICS_WOMEN_PLATINUM = 1005;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_1 = 1006;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_2 = 1007;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_3 = 1008;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_4 = 1009;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_5 = 1010;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_6 = 1011;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_7 = 1012;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_8 = 1013;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_9 = 1014;
    public const USAG_GYMNASTICS_WOMEN_LEVEL_10 = 1015;
    public const USAG_GYMNASTICS_WOMEN_OPEN = 1016;
    public const USAG_GYMNASTICS_WOMEN_ELITE = 1017;
    public const USAG_GYMNASTICS_WOMEN_TOPS = 1018;
    public const USAG_GYMNASTICS_WOMEN_EXHIB = 1019;
    public const USAG_GYMNASTICS_WOMEN_HOPES = 1020;
    public const USAG_GYMNASTICS_WOMEN_HUGS = 1021;

    public const USAG_GYMNASTICS_MEN_BRONZE = 1051;
    public const USAG_GYMNASTICS_MEN_SILVER = 1052;
    public const USAG_GYMNASTICS_MEN_GOLD = 1053;
    public const USAG_GYMNASTICS_MEN_LEVEL_1 = 1054;
    public const USAG_GYMNASTICS_MEN_LEVEL_2 = 1055;
    public const USAG_GYMNASTICS_MEN_LEVEL_3 = 1056;
    public const USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_1 = 1057;
    public const USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_2 = 1058;
    public const USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_1 = 1059;
    public const USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_2 = 1060;
    public const USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_1 = 1061;
    public const USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_2 = 1062;
    public const USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_1 = 1063;
    public const USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_2 = 1064;
    public const USAG_GYMNASTICS_MEN_LEVEL_8 = 1065;
    public const USAG_GYMNASTICS_MEN_LEVEL_9 = 1066;
    public const USAG_GYMNASTICS_MEN_LEVEL_10 = 1067;
    public const USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_1 = 1068;
    public const USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_2 = 1069;
    public const USAG_GYMNASTICS_MEN_ELITE = 1070;
    public const USAG_GYMNASTICS_MEN_EXHIB = 1071;
    public const USAG_GYMNASTICS_MEN_HUGS = 1072;

    /* USAIGC Levels */
    public const IGC_GYMNASTICS_COPPER_1 = 2001;
    public const IGC_GYMNASTICS_COPPER_2 = 2002;
    public const IGC_GYMNASTICS_BRONZE = 2003;
    public const IGC_GYMNASTICS_DIAMOND = 2004;
    public const IGC_GYMNASTICS_SILVER = 2005;
    public const IGC_GYMNASTICS_GOLD = 2006;
    public const IGC_GYMNASTICS_PLATINUM = 2007;
    public const IGC_GYMNASTICS_PREMIERE = 2008;

    public const IGC_TUMBLING_1 = 2051;
    public const IGC_TUMBLING_2 = 2052;
    public const IGC_TUMBLING_3 = 2053;
    public const IGC_TUMBLING_4 = 2054;
    public const IGC_TUMBLING_5 = 2055;
    public const IGC_TUMBLING_6 = 2056;

    /* AAU Levels */
    public const AAU_WOMEN_GYMNASTICS_LEVEL_1 = 3001;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_2 = 3002;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_3 = 3003;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_4 = 3004;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_5 = 3005;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_6 = 3006;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_7 = 3007;
    public const AAU_WOMEN_GYMNASTICS_LEVEL_8 = 3008;
    public const AAU_WOMEN_GYMNASTICS_OPEN_OPTIONAL = 3009;
    public const AAU_WOMEN_GYMNASTICS_XCEL_BRONZE = 3010;
    public const AAU_WOMEN_GYMNASTICS_XCEL_DIAMOND = 3011;
    public const AAU_WOMEN_GYMNASTICS_XCEL_SILVER = 3012;
    public const AAU_WOMEN_GYMNASTICS_XCEL_GOLD = 3013;
    public const AAU_WOMEN_GYMNASTICS_XCEL_PLATINUM = 3014;

    public const AAU_MEN_GYMNASTICS_LEVEL_3 = 3051;
    public const AAU_MEN_GYMNASTICS_LEVEL_4 = 3052;
    public const AAU_MEN_GYMNASTICS_LEVEL_5 = 3053;
    public const AAU_MEN_GYMNASTICS_LEVEL_6 = 3054;
    public const AAU_MEN_GYMNASTICS_LEVEL_8 = 3055;
    public const AAU_MEN_GYMNASTICS_LEVEL_9 = 3056;


    /* NGA Levels */
    public const NGA_WOMEN_GYMNASTICS_LEVEL_1 = 4001;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_2 = 4002;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_3 = 4003;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_4 = 4004;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_5 = 4005;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_6 = 4006;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_7 = 4007;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_8 = 4008;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_9 = 4009;
    public const NGA_WOMEN_GYMNASTICS_LEVEL_0 = 4010;
    public const NGA_WOMEN_GYMNASTICS_GOLD = 4011;
    public const NGA_WOMEN_GYMNASTICS_PLATINUM = 4012;
    public const NGA_WOMEN_GYMNASTICS_DIAMOND = 4013;
    public const NGA_WOMEN_GYMNASTICS_MEMBER = 4014;
    public const NGA_WOMEN_GYMNASTICS_NON_MEMBER = 4015;
    public const NGA_WOMEN_GYMNASTICS_SILVER = 4016;

    public const NGA_MEN_GYMNASTICS_LEVEL_1 = 4051;
    public const NGA_MEN_GYMNASTICS_LEVEL_2 = 4052;
    public const NGA_MEN_GYMNASTICS_LEVEL_3 = 4053;
    public const NGA_MEN_GYMNASTICS_LEVEL_4 = 4054;
    public const NGA_MEN_GYMNASTICS_LEVEL_5 = 4055;
    public const NGA_MEN_GYMNASTICS_LEVEL_6 = 4056;
    public const NGA_MEN_GYMNASTICS_LEVEL_7 = 4057;
    public const NGA_MEN_GYMNASTICS_LEVEL_8 = 4058;
    public const NGA_MEN_GYMNASTICS_LEVEL_9 = 4059;
    public const NGA_MEN_GYMNASTICS_LEVEL_10 = 4060;
    public const NGA_MEN_GYMNASTICS_LEVEL_MEMBER = 4061;
    public const NGA_MEN_GYMNASTICS_LEVEL_NON_MEMBER = 4062;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function sanctioning_body() {
        return $this->belongsTo(SanctioningBody::class);
    }

    public function level_category()
    {
        return $this->belongsTo(LevelCategory::class, 'level_category_id');
    }

    public function hasSpecialist()
    {
        return ($this->sanctioning_body->id == SanctioningBody::USAIGC) &&
            ($this->level_category->id == LevelCategory::GYMNASTICS_WOMEN);
    }

    public function levelMeets(): BelongsToMany
    {
        return $this->belongsToMany(AthleteLevel::class, 'level_meet')
            ->using(LevelMeet::class)
            ->withPivot(LevelMeet::PIVOT_FIELDS);
    }
}
