<?php

namespace App\Queries;

use App\Models\Gym;

/**
 * Class GymBalanceReportDataTable
 */
class GymBalanceReportDataTable
{
    /*
     * @return mixed
     */
    public function get()
    {
        /** @var Gym $query */
        $query = Gym::with('user')->select('gyms.*');

        return $query;
    }
}
