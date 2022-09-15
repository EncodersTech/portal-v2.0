<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use App\Exceptions\CustomBaseException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ManagedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $_managed = null;
        $aborted = false;

        if (Auth::guard()->check()) {      // If a user is authenticated
            $user = auth()->user();
            $isApi = $request->wantsJson();
            $claimed_managed = null;
            $session = session();
            
            if ($isApi) {
                $claimed_managed = $request->input('__managed'); // Grab the managed user from request data
            } else {
                $claimed_managed = $session->get('managed');   // Grab the managed user stored in session
                if ($claimed_managed != null)
                    $claimed_managed = $claimed_managed->id;
            }

            if (($claimed_managed != null) && ($claimed_managed != $user->id)) {
                // If it is there and is different from the authenticated user

                $_managed = $user->memberOf->find($claimed_managed);
                // check to see if the authenticated user is actually managing the user from the session
                if ($_managed == null) {
                    // if not, revert to current user and clear stored account, then let them know
                    // something went wrong.
                    $_managed = $user;
                    if (!$isApi)
                        $session->forget('managed');
                    $aborted = true;
                }
            } else {
                $_managed = $user;

                if (!$isApi)
                    $session->forget('managed');
            }
        }

        $request->_managed_account = $_managed;
        View::share('_managed', $_managed);

        if ($aborted)
            throw new CustomBaseException(
                'Action canceled. You are no longer managing that account. If this keeps happening, please contact us.',
                -1
            );

        return $next($request);
    }
}
