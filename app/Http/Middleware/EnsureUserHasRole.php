<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Достъпът до администрацията и профила на доставчика се пази на сървъра —
 * скриването на бутони във фронтенда не е защита.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, 'Нямаш достъп до тази част от платформата.');
        }

        return $next($request);
    }
}
