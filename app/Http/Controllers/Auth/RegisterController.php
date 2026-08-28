<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Регистрация на доставчик. Клиентите не се нуждаят от профил, за да
 * изпратят заявка — това е нарочно решение за MVP.
 */
class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, AnalyticsService $analytics): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role' => UserRole::Provider,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $analytics->record(AnalyticsService::PROVIDER_REGISTERED, $user);

        return redirect()
            ->route('provider.profile.create')
            ->with('success', 'Профилът ти е създаден. Попълни данните за фирмата.');
    }
}
