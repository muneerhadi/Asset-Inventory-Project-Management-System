<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia; 
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $activities = Activity::with(['user', 'subject'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Activity/Index', [
            'activities' => $activities,
        ]);
    }

    public function show(Request $request, Activity $activity): Response
    {
        $activity->load(['user', 'subject']);

        return Inertia::render('Activity/Show', [
            'activity' => $activity,
        ]);
    }
}
