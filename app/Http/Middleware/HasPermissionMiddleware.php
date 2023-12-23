<?php

namespace App\Http\Middleware;

use Closure;

class HasPermissionMiddleware
{
    private const PERMISSION_ATTRIBUTE_MAP = [
        'manage_gym' => 'can_manage_gyms',
        'manage_roster' => 'can_manage_rosters',
        'create_meet' => 'can_create_meet',
        'edit_meet' => 'can_edit_meet',
        'register' => 'can_register_in_meet',
        'email_participant' => 'can_email_participant',
        'email_host' => 'can_email_host',
        'access_report' => 'can_access_reports',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$perms)
    {
        if (count($perms) < 1)
            throw new \InvalidArgumentException(self::class . ': at least one parameter required.');

        $managed = request()->_managed_account; /** @var \App\Models\User $managed */
        if (!$managed->isCurrentUser()) {
            $flag = false;
            foreach ($perms as $perm) {
                if (!key_exists($perm, self::PERMISSION_ATTRIBUTE_MAP))
                    throw new \InvalidArgumentException(self::class . ': Invalid parameter "' . $perm . '"');
                
                $perm = self::PERMISSION_ATTRIBUTE_MAP[$perm];
                $flag = $flag || $managed->pivot->can($perm);
            }

            if (!$flag) {
                return redirect(route('dashboard'))->with(
                    'error',
                    'You do not have permission to perform this action on behalf of ' .
                    $managed->fullName() . ' (code: 42)'
                );
            }
        }
        return $next($request);
    }
}
