<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check() && Auth::user()->is_disabled == true) {
            Auth::logout();

            return redirect()->back()->withErrors('Your account is not active. Please contact to administrator.');
        }

        return $response;
    }
}
