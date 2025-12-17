<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ItemCategory;
use App\Models\ItemStatus;
use App\Models\Project;
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
            'projects' => Project::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        ItemCategory::create($validated);

        return redirect()->route('settings.index')->with('success', 'Category created.');
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
            'slug' => ['required', 'string', 'max:255', 'unique:item_statuses,slug'],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            'is_damaged' => ['nullable', 'boolean'],
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);
        $validated['is_available'] = (bool) ($validated['is_available'] ?? false);
        $validated['is_damaged'] = (bool) ($validated['is_damaged'] ?? false);

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
}
