<?php

namespace App\Queries;

use App\Models\Meet;
use App\Models\SanctioningBody;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserDataTable
 */
class MeetDataTable
{
    /*
     * @return mixed
     */
    public function get($input = [])
    {
        /** @var Meet $query */
        $query = Meet::with(['gym.user','gym','levels','meetCategories','venue_state']);

        $query->when(isset($input['sanction_type']) && $input['sanction_type'] != SanctioningBody::All, function (Builder $q) use ($input) {
            $q->whereHas('meetCategories', function (Builder $q) use ($input){
                $q->where('sanctioning_body_id',$input['sanction_type']);
            });
        });

        $query->when(isset($input['from_date']) && $input['from_date'] != '', function (Builder $q) use ($input) {
            $q->where('start_date', '>=', $input['from_date']);
        });

        $query->when(isset($input['to_date']) && $input['to_date'] != '', function (Builder $q) use ($input) {
            $q->where('end_date', '<=', $input['to_date']);
        });

        $query->when(isset($input['state']) && $input['state'] != '', function (Builder $q) use ($input) {
            $q->where('venue_state_id' ,$input['state']);
        });

        $query->when(isset($input['status']) && $input['status'] != '', function (Builder $q) use ($input) {
            $now = now()->setTime(0, 0, 0);

//            if ($input['status'] == Meet::REGISTRATION_STATUS_CLOSED) {
//                $q->where(function ($q) use ($now) {
//                    $q->where('late_registration_end_date', '<', $now)
//                        ->OrWhere('registration_end_date', '<', $now);
//                });
//            }

            if($input['status'] == Meet::REGISTRATION_STATUS_OPEN){
                    $q->where('registration_start_date', '<=', $now)
                        ->where('registration_end_date', '>=', $now);
            }
        });

        return $query;
    }
}