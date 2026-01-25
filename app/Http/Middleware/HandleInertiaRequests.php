<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $entryUsersCanAddCategories = false;
        if ($user && $user->role === 'entry_user') {
            try {
                $entryUsersCanAddCategories = Setting::getBool('entry_users_can_add_categories', false);
            } catch (\Throwable) {
                // settings table may not exist yet (e.g. migrations not run on server)
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'entry_users_can_add_categories' => $entryUsersCanAddCategories,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_summary' => fn () => $request->session()->get('import_summary'),
            ],
        ];
    }
}
