<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStatus;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $projectsQuery = Project::orderBy('name');
        
        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $projectsQuery->whereIn('id', $projectIds);
        }
        
        $projects = $projectsQuery->get(['id', 'code', 'name']);

        return Inertia::render('Reports/Index', [
            'projects' => $projects,
        ]);
    }

    public function project(Request $request, Project $project): Response
    {
        $user = $request->user();
        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $items = $project->items()->with(['status', 'category', 'currency'])->get();

        return Inertia::render('Reports/Project', [
            'project' => $project,
            'items' => $items,
        ]);
    }

    public function damagedItems(Request $request): Response
    {
        $user = $request->user();

        $itemsQuery = Item::with(['status', 'project', 'currency'])
            ->whereHas('status', fn ($q) => $q->where('is_damaged', true));

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $itemsQuery->whereIn('project_id', $projectIds);
        }

        $items = $itemsQuery->get();

        return Inertia::render('Reports/DamagedItems', [
            'items' => $items,
        ]);
    }

    public function economy(Request $request): Response
    {
        $user = $request->user();

        $itemsQuery = Item::with('currency');
        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $itemsQuery->whereIn('project_id', $projectIds);
        }

        $items = (clone $itemsQuery)->get();

        // USD totals
        $usdItems = $items->filter(fn ($item) => $item->currency && strtoupper($item->currency->code) === 'USD');
        $usdCount = $usdItems->count();
        $usdTotal = $usdItems->sum('price');

        // AFG totals
        $afgItems = $items->filter(fn ($item) => $item->currency && strtoupper($item->currency->code) === 'AFG');
        $afgCount = $afgItems->count();
        $afgTotal = $afgItems->sum('price');

        // Overall totals
        $totalItems = $items->count();
        $totalValue = $items->sum('price');

        return Inertia::render('Reports/Economy', [
            'summary' => [
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'usd' => [
                    'count' => $usdCount,
                    'total' => $usdTotal,
                ],
                'afg' => [
                    'count' => $afgCount,
                    'total' => $afgTotal,
                ],
            ],
        ]);
    }

    public function custom(Request $request): Response
    {
        $user = $request->user();

        $filters = [
            'project_id' => $request->integer('project_id') ?: null,
            'status_id' => $request->integer('status_id') ?: null,
            'assignment_status' => $request->input('assignment_status'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'price_min' => $request->input('price_min') ? (float) $request->input('price_min') : null,
            'price_max' => $request->input('price_max') ? (float) $request->input('price_max') : null,
        ];

        $itemsQuery = Item::with(['status', 'category', 'project', 'itemEmployeeAssignments']);

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $itemsQuery->whereIn('project_id', $projectIds);
        }

        if ($filters['project_id']) {
            $itemsQuery->where('project_id', $filters['project_id']);
        }

        if ($filters['status_id']) {
            $itemsQuery->where('item_status_id', $filters['status_id']);
        }

        if ($filters['from']) {
            $itemsQuery->whereDate('purchase_date', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $itemsQuery->whereDate('purchase_date', '<=', $filters['to']);
        }

        if ($filters['price_min']) {
            $itemsQuery->where('price', '>=', $filters['price_min']);
        }

        if ($filters['price_max']) {
            $itemsQuery->where('price', '<=', $filters['price_max']);
        }

        $items = $itemsQuery->orderBy('purchase_date')->get()->map(function ($item) {
            $item->assignment_status = $item->itemEmployeeAssignments->isNotEmpty() ? 'In Use' : 'In Stock';
            return $item;
        });

        if ($filters['assignment_status']) {
            $items = $items->filter(function ($item) use ($filters) {
                return $item->assignment_status === $filters['assignment_status'];
            });
        }

        $projectsQuery = Project::orderBy('name');
        
        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $projectsQuery->whereIn('id', $projectIds);
        }

        return Inertia::render('Reports/Custom', [
            'items' => $items,
            'filters' => $filters,
            'projects' => $projectsQuery->get(['id', 'code', 'name']),
            'statuses' => ItemStatus::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
