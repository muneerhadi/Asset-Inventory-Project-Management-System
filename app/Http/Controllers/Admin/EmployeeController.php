<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemEmployeeAssignment;
use App\Models\Project;
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
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $user = $request->user();

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

        // Only super admins can delete employees
        if (! $user->isSuperAdmin()) {
            abort(403, 'Only super administrators can delete employees.');
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

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        try {
            $path = $validated['file']->getRealPath();
            $handle = fopen($path, 'r');

            if (!$handle) {
                return redirect()->back()->with('error', 'Unable to open uploaded file.');
            }

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return redirect()->back()->with('error', 'File is empty or invalid.');
            }

            $map = [];
            foreach ($header as $index => $column) {
                $map[strtolower(trim($column))] = $index;
            }

            $created = 0;
            $errors = [];
            $duplicates = [];

            while (($row = fgetcsv($handle)) !== false) {
                $get = function (string $key) use ($map, $row) {
                    $key = strtolower($key);
                    if (!array_key_exists($key, $map)) {
                        return null;
                    }
                    return trim($row[$map[$key]] ?? '') ?: null;
                };

                $name = $get('name');
                $email = $get('email');

                if (!$name || !$email) {
                    continue;
                }

                // Check for duplicate email
                if (Employee::where('email', $email)->exists()) {
                    $duplicates[] = $email;
                    continue;
                }

                try {
                    $projectName = $get('project');
                    $project = $projectName ? Project::where('name', $projectName)->first() : null;

                    // Auto-generate employee code
                    $lastEmployee = Employee::orderBy('id', 'desc')->first();
                    $nextNumber = $lastEmployee ? $lastEmployee->id + 1 : 1;
                    $employeeCode = 'EMP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                    
                    // Ensure uniqueness
                    while (Employee::where('employee_code', $employeeCode)->exists()) {
                        $nextNumber++;
                        $employeeCode = 'EMP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                    }

                    $employee = Employee::create([
                        'employee_code' => $employeeCode,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $get('phone'),
                        'position' => $get('position'),
                        'location' => $get('location'),
                        'address' => $get('department'), // Using department as address if needed
                        // Add other fields as needed
                    ]);

                    // Attach to project if specified
                    if ($project) {
                        $employee->projects()->attach($project->id);
                    }

                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Row with email {$email}: " . $e->getMessage();
                }
            }

            fclose($handle);

            Activity::create([
                'user_id' => $user->id,
                'action' => 'employees_imported',
                'description' => $created . ' employees imported from Excel file',
                'subject_type' => Employee::class,
                'subject_id' => null,
            ]);

            $message = $created . ' employees imported successfully.';
            if (!empty($duplicates)) {
                $message .= ' Skipped ' . count($duplicates) . ' duplicate emails: ' . implode(', ', array_slice($duplicates, 0, 5));
                if (count($duplicates) > 5) {
                    $message .= ' and ' . (count($duplicates) - 5) . ' more.';
                }
            }
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' employees had errors and were skipped.';
            }

            return redirect()->route('employees.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }
}
