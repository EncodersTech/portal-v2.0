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
        // $query = DB::select("select g.id, g.name,u.email, u.cleared_balance, u.pending_balance, 
        // (select sum(ub.total) as total from user_balance_transactions as ub 
        //  where ub.user_id = u.id and ub.description != 'Overdraft Adjustment by Admin' and status != 4) as total
        // from gyms as g
        // join users as u on g.user_id = u.id
        // order by total asc");
        $query = DB::select("
        SELECT 
            string_agg(DISTINCT g.name, ', ' ORDER BY g.name) AS name,
            u.id,
            u.email,
            u.cleared_balance,
            u.pending_balance,
            (SELECT SUM(ub.total) AS total 
             FROM user_balance_transactions AS ub 
             WHERE ub.user_id = u.id 
             AND ub.description != 'Overdraft Adjustment by Admin' 
             AND status != 4) AS total
        FROM 
            users AS u
        LEFT JOIN 
            gyms AS g ON g.user_id = u.id
        GROUP BY 
            u.id
        ORDER BY 
            total ASC");

        return $query;
    }
}
