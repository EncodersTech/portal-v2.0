<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;
/**
 * Class UserDataTable
 */
class UserDataTable
{
    /*
     * @return mixed
     */
    public function get()
    {
        /** @var User $query */
        // $query = DB::table('users')->join('gyms', 'gyms.user_id', '=','users.id')->where('users.id','!=', Auth::id())->select('users.*, gyms.name as gname')->orderBy('email','ASC');
        $query = DB::select("SELECT users.*, CONCAT(users.first_name , ' ', users.last_name) as full_name, gyms.name as gname, (SELECT users.email FROM users WHERE id = member_user.member_id) as member_info 
        FROM users 
        LEFT JOIN gyms ON gyms.user_id = users.id 
        LEFT JOIN member_user ON member_user.user_id = users.id
        WHERE users.id != ".Auth::id()." ORDER BY users.email ASC");

        return $query;
    }
}
