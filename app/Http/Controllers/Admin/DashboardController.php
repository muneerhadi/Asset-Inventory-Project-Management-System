<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $user = $request->user();

            if ($user->isSuperAdmin()) {
                $projectsQuery = Project::query();
                $itemsQuery = Item::query();
                $employeesQuery = Employee::query();
            } else {
                $projectIds = $user->projects()->pluck('projects.id');

                $projectsQuery = Project::whereIn('id', $projectIds);
                $itemsQuery = Item::whereIn('project_id', $projectIds);
                $employeesQuery = Employee::whereHas('projects', function ($q) use ($projectIds) {
                    $q->whereIn('projects.id', $projectIds);
                });
            }

            // For project managers, compute per-project deadlines
            $projectDeadlines = [];
            if ($user->isProjectManager()) {
                $projectDeadlines = $projectsQuery->get()->map(function (Project $project) {
                    $now = now();

                    $daysToDeadline = null;
                    if ($project->end_date) {
                        $daysToDeadline = (int) $now->diffInDays($project->end_date, false);
                    }

                    return [
                        'id' => $project->id,
                        'code' => $project->code,
                        'name' => $project->name,
                        'start_date' => optional($project->start_date)->toDateString(),
                        'end_date' => optional($project->end_date)->toDateString(),
                        'days_to_deadline' => $daysToDeadline,
                    ];
                });
            }

            $projectsCount = $projectsQuery->count();
            $employeesCount = $employeesQuery->count();
            $itemsCount = $itemsQuery->count();

            $totalStockItems = $itemsQuery->count();
            $totalDistributedItems = $itemsQuery->whereNotNull('project_id')->count();

            $recentActivities = Activity::with('user')
                ->latest()
                ->take(3)
                ->get();

            $recentEmployees = $employeesQuery->latest()->take(3)->get();
            
            // Get recent items by sequential number instead of creation date
            if ($user->isSuperAdmin()) {
                $recentItems = Item::getRecentItemsBySequence(3);
            } else {
                $projectIds = $user->projects()->pluck('projects.id');
                $recentItems = Item::with(['category', 'status', 'project'])
                    ->whereIn('project_id', $projectIds)
                    ->get()
                    ->map(function ($item) {
                        $item->sequential_number = Item::extractSequentialNumber($item->tag_number);
                        return $item;
                    })
                    ->filter(fn($item) => $item->sequential_number !== null)
                    ->sortByDesc('sequential_number')
                    ->take(3)
                    ->values();
            }

            return Inertia::render('Dashboard', [
                'welcomeMessage' => 'Welcome, '.$user->name,
                'now' => now()->toDateTimeString(),
                'userRole' => $user->role,
                'metrics' => [
                    'employees' => $employeesCount,
                    'items' => $itemsCount,
                    'projects' => $projectsCount,
                    'totalStockItems' => $totalStockItems,
                    'totalDistributedItems' => $totalDistributedItems,
                ],
                'recentActivities' => $recentActivities,
                'recentEmployees' => $recentEmployees,
                'recentItems' => $recentItems,
                'projectDeadlines' => $projectDeadlines,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Dashboard', [
                'error' => $e->getMessage(),
                'welcomeMessage' => 'Welcome',
                'userRole' => null,
                'metrics' => [
                    'employees' => 0,
                    'items' => 0,
                    'projects' => 0,
                    'totalStockItems' => 0,
                    'totalDistributedItems' => 0,
                ],
                'recentActivities' => [],
                'recentEmployees' => [],
                'recentItems' => [],
                'projectDeadlines' => [],
            ]);
        }
    }
}
