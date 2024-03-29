<?php

namespace App\Queries;

use App\Models\Gym;
use Illuminate\Support\Facades\DB;

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
        // $query = Gym::with('user')->select('gyms.*');
        $query = DB::select("select g.id, g.name,u.email, u.cleared_balance, u.pending_balance, 
        (select sum(ub.total) as total from user_balance_transactions as ub 
         where ub.user_id = u.id and ub.description != 'Overdraft Adjustment by Admin' and status != 4) as total
        from gyms as g
        join users as u on g.user_id = u.id
        order by total asc");

        return $query;
    }
}
