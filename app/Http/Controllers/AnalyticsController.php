<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Приема събития от браузъра (започната/изоставена форма и др.).
 * Разрешени са само предварително изброените имена.
 */
class AnalyticsController extends Controller
{
    public function __invoke(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'in:'.implode(',', AnalyticsService::CLIENT_EVENTS)],
            'properties' => ['sometimes', 'array', 'max:10'],
            'properties.*' => ['nullable', 'string', 'max:120'],
        ]);

        $analytics->record($data['name'], null, $data['properties'] ?? []);

        return response()->json(['ok' => true]);
    }
}
