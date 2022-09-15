<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        $query = User::where('id','!=', Auth::id())->orderBy('email','ASC');

        return $query->get();
    }
}
