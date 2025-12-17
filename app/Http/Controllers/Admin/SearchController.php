<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function items(Request $request): Response
    {
        $user = $request->user();
        $search = $request->string('q')->trim();

        $itemsQuery = Item::with(['status', 'category', 'project']);

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $itemsQuery->whereIn('project_id', $projectIds);
        }

        if ($search !== '') {
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('tag_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $items = $itemsQuery->limit(50)->get();

        return Inertia::render('Search/Items', [
            'items' => $items,
            'q' => $search,
        ]);
    }

    public function employees(Request $request): Response
    {
        $user = $request->user();
        $search = $request->string('q')->trim();

        $employeesQuery = Employee::with('projects');

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $employeesQuery->whereHas('projects', function ($q) use ($projectIds) {
                $q->whereIn('projects.id', $projectIds);
            });
        }

        if ($search !== '') {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->limit(50)->get();

        return Inertia::render('Search/Employees', [
            'employees' => $employees,
            'q' => $search,
        ]);
    }
}
