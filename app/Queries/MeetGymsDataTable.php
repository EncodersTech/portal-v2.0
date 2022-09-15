<?php

namespace App\Queries;

use App\Models\MeetRegistration;

/**
 * Class UserDataTable
 */
class MeetGymsDataTable
{
    /**
     * @param $input
     * @return mixed
     */
    public function get($input)
    {
        /** @var MeetRegistration $ query */
        $query = MeetRegistration::with(['gym','gym.user'])->where('meet_id',$input['meet_id'])->withCount(['athletes','coaches']);
        return $query->get();
    }
}