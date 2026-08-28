<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Доставчик без създаден профил се насочва към попълването му.
 */
class EnsureProviderProfileExists
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->providerProfile) {
            return redirect()
                ->route('provider.profile.create')
                ->with('info', 'Попълни данните за фирмата си, за да продължиш.');
        }

        return $next($request);
    }
}
