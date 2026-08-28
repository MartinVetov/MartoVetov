<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreProviderProfileRequest;
use App\Http\Requests\Provider\UpdateProviderProfileRequest;
use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Notifications\ProviderSubmittedForReview;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct(protected ImageUploadService $images) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->providerProfile) {
            return redirect()->route('provider.profile.edit');
        }

        return view('provider.profile.create', ['cities' => $this->cities()]);
    }

    public function store(StoreProviderProfileRequest $request): RedirectResponse
    {
        abort_if($request->user()->providerProfile !== null, 409);

        $profile = new ProviderProfile($request->validated());
        $profile->user_id = $request->user()->id;
        $profile->slug = $this->uniqueSlug($request->validated('company_name'));

        if ($request->hasFile('logo')) {
            $profile->logo_path = $this->images->store($request->file('logo'), 'providers');
        }

        $profile->save();

        return redirect()
            ->route('provider.equipment.create')
            ->with('success', 'Профилът е създаден. Добави първата си техника.');
    }

    public function edit(Request $request): View
    {
        $profile = $request->user()->providerProfile;
        $this->authorize('update', $profile);

        return view('provider.profile.edit', [
            'profile' => $profile,
            'cities' => $this->cities(),
        ]);
    }

    public function update(UpdateProviderProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $this->authorize('update', $profile);

        $profile->fill($request->validated());

        if ($request->hasFile('logo')) {
            $this->images->delete($profile->logo_path);
            $profile->logo_path = $this->images->store($request->file('logo'), 'providers');
        }

        $profile->save();

        return back()->with('success', 'Профилът е обновен.');
    }

    /** Изпращане на профила за одобрение от администратор. */
    public function submit(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $this->authorize('update', $profile);

        if ($profile->equipment()->count() === 0) {
            return back()->with('error', 'Добави поне една единица техника, преди да изпратиш профила за одобрение.');
        }

        if ($profile->isApproved()) {
            return back()->with('info', 'Профилът ти вече е одобрен.');
        }

        $profile->forceFill(['submitted_at' => now()])->save();

        $admins = User::where('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new ProviderSubmittedForReview($profile));
        }

        return back()->with('success', 'Профилът е изпратен за одобрение. Ще получиш имейл, когато бъде прегледан.');
    }

    protected function cities()
    {
        return City::active()->orderBy('name')->get(['id', 'name', 'region']);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'dostavchik';
        $slug = $base;
        $i = 2;

        while (ProviderProfile::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
