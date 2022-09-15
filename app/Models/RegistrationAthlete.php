<?php

namespace App\Models;

use App\Helper;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class RegistrationAthlete extends Model
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

    public function transaction()
    {
        return $this->belongsTo(MeetTransaction::class, 'transaction_id');
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function net_fee()
    {
        return $this->fee + $this->late_fee - $this->refund - $this->late_refund;
    }

    public function refund_fee()
    {
        return $this->refund + $this->late_refund;
    }
    public static function athlete_meet($meet,$gym = '')
    {
        return RegistrationAthlete::whereHas('meet_registration', function ($query) use ($meet,$gym) {
            $query->where('meet_id', $meet);
            if($gym){
                $query->where('gym_id', $gym);
            }
        });
    }
    public static function athlete_meet_data_for_csv($registrationAthlete)
    {
        $data = [];
        if($registrationAthlete){
            foreach ($registrationAthlete as $athlete) {
                $tsize = "";
                $is_us_citizen = "false";
                if($athlete->tshirt){
                    $tsize = $athlete->tshirt->size;
                }
                if($athlete->is_us_citizen){
                    $is_us_citizen = "true";
                }
                $event = ($athlete->registration_level->level->level_category->male) ? "Men":"Women";
                $data[] = [
                    'first_name' => $athlete->first_name,
                    'last_name' => $athlete->last_name,
                    'gym' => $athlete->meet_registration->gym->short_name,
                    'event' => $event,
                    'level' => $athlete->registration_level->level->abbreviation,
                    'dob' => $athlete->dob->format(Helper::AMERICAN_SHORT_DATE),
                    'usag_no' => ($athlete->usaigc_no ?? $athlete->usaigc_no ?? ($athlete->usag_no ?? $athlete->usag_no ?? ($athlete->nga_no))),
                    'session' => "1",
                    'flight' => "",
                    'squad' => "",
                    'team1' => "",
                    'team2' => "",
                    'team3' => "",
                    'tshirt' => $tsize,
                    'is_us_citizen' => $is_us_citizen,
                    'scratched' =>"false",
                    'altID' =>"",
                ];
            }
        }
        return $data;
    }
}
