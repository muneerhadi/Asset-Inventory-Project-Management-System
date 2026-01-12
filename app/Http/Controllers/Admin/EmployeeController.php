<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemEmployeeAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $employeesQuery = Employee::query();

        // Remove project restriction - project managers can see all employees
        // if (! $user->isSuperAdmin()) {
        //     $projectIds = $user->projects()->pluck('projects.id');
        //     $employeesQuery->whereHas('projects', function ($q) use ($projectIds) {
        //         $q->whereIn('projects.id', $projectIds);
        //     });
        // }

        if ($search = $request->string('search')->trim()) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => (clone $employeesQuery)->count(),
            'positions' => (clone $employeesQuery)->distinct('position')->count('position'),
            'locations' => (clone $employeesQuery)->distinct('location')->count('location'),
        ];

        // Get all available items (not already assigned to any employee)
        $assignedItemIds = ItemEmployeeAssignment::pluck('item_id')->toArray();
        $availableItems = Item::whereNotIn('id', $assignedItemIds)
            ->with('category', 'status')
            ->orderBy('name')
            ->get(['id', 'tag_number', 'name', 'item_category_id', 'item_status_id']);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
            ],
            'availableItems' => $availableItems,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Auto-generate employee code if not provided
        if (empty($validated['employee_code'])) {
            $lastEmployee = Employee::orderBy('id', 'desc')->first();
            $nextNumber = $lastEmployee ? $lastEmployee->id + 1 : 1;
            $validated['employee_code'] = 'EMP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            while (Employee::where('employee_code', $validated['employee_code'])->exists()) {
                $nextNumber++;
                $validated['employee_code'] = 'EMP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('employees', 'public');
            $validated['image_path'] = '/storage/'.$path;
        }

        $employee = Employee::create($validated);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'employee_created',
            'description' => 'Employee '.$employee->employee_code.' created',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Request $request, Employee $employee): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        $employee->load(['projects', 'itemEmployeeAssignments.item.category', 'itemEmployeeAssignments.item.status']);
        
        // Get all available items (not already assigned to any employee)
        // Items that are not assigned OR items that are assigned to this employee
        $assignedItemIds = ItemEmployeeAssignment::where('employee_id', '!=', $employee->id)
            ->pluck('item_id')
            ->toArray();
        
        $availableItems = Item::whereNotIn('id', $assignedItemIds)
            ->with('category', 'status')
            ->orderBy('name')
            ->get(['id', 'tag_number', 'name', 'item_category_id', 'item_status_id']);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
            'availableItems' => $availableItems,
        ]);
    }

    public function edit(Request $request, Employee $employee): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('employees', 'public');
            $validated['image_path'] = '/storage/'.$path;
        }

        $employee->update($validated);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'employee_updated',
            'description' => 'Employee '.$employee->employee_code.' updated',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        $code = $employee->employee_code;
        $employee->delete();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'employee_deleted',
            'description' => 'Employee '.$code.' deleted',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function assignItem(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['exists:items,id'],
        ]);

        $assignedCount = 0;
        $alreadyAssigned = [];
        
        foreach ($validated['item_ids'] as $itemId) {
            $item = Item::findOrFail($itemId);

            // Check if item is already assigned to any employee
            $existingAssignment = ItemEmployeeAssignment::where('item_id', $item->id)->first();

            if ($existingAssignment) {
                $alreadyAssigned[] = $item->tag_number ?? $item->name;
                continue;
            }

            // Create assignment
            ItemEmployeeAssignment::create([
                'item_id' => $item->id,
                'employee_id' => $employee->id,
                'project_id' => null,
            ]);
            
            $assignedCount++;
        }

        Activity::create([
            'user_id' => $user->id,
            'action' => 'items_assigned_to_employee',
            'description' => $assignedCount.' items assigned to employee '.$employee->name,
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);

        $message = $assignedCount.' items assigned successfully.';
        if (!empty($alreadyAssigned)) {
            $message .= ' Some items were already assigned: '.implode(', ', $alreadyAssigned);
        }

        return redirect()->back()->with('success', $message);
    }

    public function unassignItem(Request $request, Employee $employee, ItemEmployeeAssignment $assignment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if (! $employee->projects()->whereIn('projects.id', $projectIds)->exists()) {
                abort(403);
            }
        }

        // Verify the assignment belongs to this employee
        if ($assignment->employee_id !== $employee->id) {
            abort(403);
        }

        $itemCode = $assignment->item->tag_number ?? $assignment->item->item_code;
        $assignment->delete();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_unassigned_from_employee',
            'description' => 'Item '.$itemCode.' unassigned from employee '.$employee->name,
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);

        return redirect()->back()->with('success', 'Item unassigned from employee successfully.');
    }
}
