<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Записва посещенията на публичните страници за фунията във фийда на
 * администрацията. Администраторската част и служебните адреси се пропускат.
 */
class TrackPageView
{
    protected array $except = [
        'admin', 'admin/*', 'profil', 'profil/*',
        'sabitie', 'sitemap.xml', 'robots.txt', 'up',
    ];

    public function __construct(protected AnalyticsService $analytics) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            $this->analytics->record(AnalyticsService::PAGE_VIEW, null, [
                'path' => mb_substr($request->path(), 0, 120),
            ]);
        }

        return $response;
    }

    protected function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ! $request->ajax()
            && ! $request->is($this->except);
    }
}
