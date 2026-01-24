<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ItemCategory;
use App\Models\ItemStatus;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Index', [
            'categories' => ItemCategory::orderBy('name')->get(),
            'statuses' => ItemStatus::orderBy('name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
            'projectManagers' => User::where('role', 'project_manager')
                ->with('projects:id,code,name')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
            'entryUsers' => User::where('role', 'entry_user')->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'projects' => Project::orderBy('name')->get(['id', 'code', 'name']),
            'entryUsersCanAddCategories' => Setting::getBool('entry_users_can_add_categories', false),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        ItemCategory::create($validated);

        return redirect()->route('settings.index')->with('success', 'Category created.');
    }

    public function toggleCategory(Request $request, ItemCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $category->update(['is_active' => $validated['is_active']]);

        return redirect()->route('settings.index')->with('success', 'Category updated.');
    }

    public function destroyCategory(ItemCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('settings.index')->with('success', 'Category deleted.');
    }

    public function storeStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // Auto-generate slug from name
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);

        ItemStatus::create($validated);

        return redirect()->route('settings.index')->with('success', 'Status created.');
    }

    public function destroyStatus(ItemStatus $status): RedirectResponse
    {
        $status->delete();

        return redirect()->route('settings.index')->with('success', 'Status deleted.');
    }

    public function storeCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:currencies,code'],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:8'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);

        if ($validated['is_default']) {
            Currency::query()->update(['is_default' => false]);
        }

        Currency::create($validated);

        return redirect()->route('settings.index')->with('success', 'Currency saved.');
    }

    public function destroyCurrency(Currency $currency): RedirectResponse
    {
        $currency->delete();

        return redirect()->route('settings.index')->with('success', 'Currency deleted.');
    }

    public function storeProjectManager(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->role = 'project_manager';
        $user->save();

        if (! empty($validated['project_ids'])) {
            $user->projects()->sync($validated['project_ids']);
        }

        return redirect()->route('settings.index')->with('success', 'Project manager created.');
    }

    public function updateProjectManagerProjects(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isProjectManager(), 404);

        $validated = $request->validate([
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        $user->projects()->sync($validated['project_ids'] ?? []);

        return redirect()->route('settings.index')->with('success', 'Project manager projects updated.');
    }

    public function destroyProjectManager(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isProjectManager(), 404);

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        $user->delete();

        return redirect()->route('settings.index')->with('success', 'Project manager deleted.');
    }

    public function storeEntryUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->role = 'entry_user';
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Entry user created.');
    }

    public function destroyEntryUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isEntryUser(), 404);

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['password'], auth()->user()->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        $user->delete();

        return redirect()->route('settings.index')->with('success', 'Entry user deleted.');
    }

    public function updateEntryUserCategoriesSetting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entry_users_can_add_categories' => ['required', 'boolean'],
        ]);

        Setting::set('entry_users_can_add_categories', $validated['entry_users_can_add_categories'] ? '1' : '0');

        return redirect()->route('settings.index')->with('success', 'Entry user setting updated.');
    }

    public function entryCategories(Request $request): Response
    {
        $user = $request->user();
        if (! $user->isEntryUser() || ! Setting::getBool('entry_users_can_add_categories', false)) {
            abort(403, 'You cannot add item categories.');
        }

        return Inertia::render('Entry/Categories', [
            'categories' => ItemCategory::orderBy('name')->get(),
        ]);
    }

    public function storeEntryCategory(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isEntryUser() || ! Setting::getBool('entry_users_can_add_categories', false)) {
            abort(403, 'You cannot add item categories.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        ItemCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('entry.categories.index')->with('success', 'Category created.');
    }
}
