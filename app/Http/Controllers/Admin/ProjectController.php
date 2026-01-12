<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStatus;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ItemEmployeeAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $projectsQuery = Project::query();

        if (! $user->isSuperAdmin()) {
            $projectsQuery->whereIn('id', $user->projects()->pluck('projects.id'));
        }

        if ($search = $request->string('search')->trim()) {
            $projectsQuery->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $projects = $projectsQuery->withCount(['items', 'employees'])->latest()->paginate(10)->withQueryString();

        $stats = [
            'projects' => (clone $projectsQuery)->count(),
            'items' => Item::whereIn('project_id', (clone $projectsQuery)->pluck('id'))->count(),
            'employees' => Employee::whereHas('projects', function ($q) use ($projectsQuery) {
                $q->whereIn('projects.id', $projectsQuery->pluck('id'));
            })->count(),
        ];

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:255', 'unique:projects,code'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = 'PROJ-' . strtoupper(substr(uniqid(), -6));
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projects', 'public');
            $validated['logo_path'] = '/storage/'.$path;
        }

        $project = Project::create($validated);

        if ($user->isProjectManager()) {
            $user->projects()->attach($project->id);
        }

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_created',
            'description' => 'Project '.$project->code.' created',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $project->load(['items.status', 'items.category']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Request $request, Project $project): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:projects,code,'.$project->id],
            'name' => ['required', 'string', 'max:255'],
'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projects', 'public');
            $validated['logo_path'] = '/storage/'.$path;
        }

        $project->update($validated);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_updated',
            'description' => 'Project '.$project->code.' updated',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $code = $project->code;

        // Detach related data before deleting to satisfy foreign key constraints
        // 1) Clear project_id on items belonging to this project
        Item::where('project_id', $project->id)->update(['project_id' => null]);

        // 2) Detach employees from this project (pivot table)
        $project->employees()->detach();

        // Now it is safe to delete the project itself
        $project->delete();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_deleted',
            'description' => 'Project '.$code.' deleted',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function verifyPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();
        
        if (!\Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        return back()->with('password_verified', true);
    }

    public function exportItems(Request $request, Project $project): StreamedResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $fileName = 'project-'.$project->code.'-items.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $callback = function () use ($project) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                '#',
                'Tag number',
                'Item name',
                'Specification',
                'Status',
                'Price',
                'Currency',
                'Purchase date',
                'Voucher number',
                'Location',
                'Sublocation',
                'Model',
                'Serial number',
                'Remarks',
            ]);

            $index = 1;
            $project->items()->with(['category', 'status', 'currency'])->chunk(200, function ($items) use ($handle, &$index) {
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $index++,
                        $item->tag_number ?? '',
                        $item->name ?? '',
                        $item->description ?? '',
                        optional($item->status)->name ?? '',
                        $item->price ?? '',
                        optional($item->currency)->code ?? '',
                        optional($item->purchase_date)?->toDateString() ?? '',
                        $item->voucher_number ?? '',
                        $item->location ?? '',
                        $item->sublocation ?? '',
                        $item->model ?? '',
                        $item->serial_number ?? '',
                        $item->remarks ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importEmployees(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        try {
            // CSV-only import for reliability
            return $this->importEmployeesFromCsv($request, $project, $validated['file']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: '.$e->getMessage());
        }
    }

    private function importEmployeesFromCsv(Request $request, Project $project, $file): RedirectResponse
    {
        $user = $request->user();
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return redirect()->back()->with('error', 'Unable to open uploaded file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty.');
        }

        $map = [];
        foreach ($header as $index => $column) {
            $column = strtolower(trim($column));
            $map[$column] = $index;
        }

        $created = 0;
        $attached = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $get = function (string $key) use ($map, $row) {
                if (! array_key_exists($key, $map)) {
                    return null;
                }

                return trim($row[$map[$key]] ?? '') ?: null;
            };

            $employeeCode = $get('employee_code');
            if (! $employeeCode) {
                continue;
            }

            $name = $get('name');
            if (! $name) {
                continue;
            }

            $employee = Employee::updateOrCreate(
                ['employee_code' => $employeeCode],
                [
                    'name' => $name,
                    'position' => $get('position'),
                    'email' => $get('email'),
                    'phone' => $get('phone'),
                    'location' => $get('location'),
                    'address' => $get('address'),
                ],
            );

            // Attach employee to project if not already attached
            if (! $project->employees()->where('employee_id', $employee->id)->exists()) {
                $project->employees()->attach($employee->id);
                $attached++;
            }

            $created++;
        }

        fclose($handle);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_employees_imported',
            'description' => $created.' employees imported, '.$attached.' attached to project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', $created.' employees imported ('.$attached.' attached) to project.');
    }

    public function importItems(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $validated['file']->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return redirect()->back()->with('error', 'Unable to open uploaded file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty.');
        }

        $map = [];
        foreach ($header as $index => $column) {
            $normalized = strtolower(trim($column));
            $map[$normalized] = $index;
            // Also map with original case for flexibility
            $map[trim($column)] = $index;
        }

        $created = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $get = function (string ...$keys) use ($map, $row) {
                foreach ($keys as $key) {
                    $normalized = strtolower(trim($key));
                    if (array_key_exists($normalized, $map)) {
                        $value = trim($row[$map[$normalized]] ?? '');
                        if ($value !== '') {
                            return $value;
                        }
                    }
                    if (array_key_exists($key, $map)) {
                        $value = trim($row[$map[$key]] ?? '');
                        if ($value !== '') {
                            return $value;
                        }
                    }
                }
                return null;
            };

            // Try to get tag number or item code (skip # column)
            $tagNumber = $get('tag number', 'tag_number', 'Tag number');
            $itemCode = $get('item_code', 'item code');
            
            // Use tag number as identifier if available, otherwise skip
            if (!$tagNumber && !$itemCode) {
                continue;
            }

            $name = $get('item name', 'name', 'Name');
            if (! $name) {
                continue;
            }

            $categoryName = $get('type', 'category', 'Category');
            $statusName = $get('status', 'Status');
            $currencyCode = $get('currency', 'Currency');

            $category = null;
            if ($categoryName) {
                $category = ItemCategory::firstOrCreate(['name' => $categoryName]);
            }
            
            // If no category found, get the first available category or create a default one
            if (! $category) {
                $category = ItemCategory::first();
                if (! $category) {
                    $category = ItemCategory::create(['name' => 'General']);
                }
            }

            $status = null;
            if ($statusName) {
                $status = ItemStatus::where('name', $statusName)->first()
                    ?? ItemStatus::where('slug', strtolower($statusName))->first();
            }

            if (! $status) {
                $status = ItemStatus::where('is_default', true)->first()
                    ?? ItemStatus::first();
            }
            
            // Ensure we have a status
            if (! $status) {
                $status = ItemStatus::create(['name' => 'Active', 'slug' => 'active', 'is_default' => true]);
            }

            $currency = null;
            if ($currencyCode) {
                $currency = Currency::where('code', $currencyCode)->first();
            }

            $identifier = $tagNumber ?: $itemCode;
            Item::updateOrCreate(
                ['tag_number' => $identifier],
                [
                    'item_code' => $itemCode ?: $identifier,
                    'tag_number' => $tagNumber ?: $identifier,
                    'name' => $name,
                    'description' => $get('specification', 'description', 'Description'),
                    'item_category_id' => $category->id,
                    'item_status_id' => $status->id,
                    'price' => ($price = $get('price', 'Price')) !== null ? (float) $price : null,
                    'currency_id' => optional($currency)->id,
                    'depreciation_rate' => null,
                    'purchase_date' => $get('purchase date', 'purchase_date', 'Purchase date') ?: null,
                    'voucher_number' => $get('voucher number', 'voucher_number', 'Voucher number'),
                    'location' => $get('location', 'Location'),
                    'sublocation' => $get('sublocation', 'Sublocation'),
                    'model' => $get('model', 'Model'),
                    'serial_number' => $get('serial number', 'serial_number', 'Serial number'),
                    'project_id' => $project->id,
                    'remarks' => $get('remarks', 'Remarks') ?: 'No Remarks',
                ],
            );

            $created++;
        }

        fclose($handle);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_items_imported',
            'description' => $created.' items imported for project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', $created.' items imported from CSV.');
    }

    public function attachEmployee(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $project->employees()->syncWithoutDetaching([$validated['employee_id']]);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_employee_attached',
            'description' => 'Employee '.$validated['employee_id'].' attached to project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Employee added to project.');
    }

    public function attachItem(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $item->project_id = $project->id;
        $item->save();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_item_attached',
            'description' => 'Item '.$item->item_code.' attached to project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Item added to project.');
    }

    public function detachEmployee(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $project->employees()->detach($validated['employee_id']);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_employee_detached',
            'description' => 'Employee '.$validated['employee_id'].' removed from project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Employee removed from project.');
    }

    public function detachItem(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
        ]);

        $item = Item::where('id', $validated['item_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        $itemCode = $item->item_code;
        $item->project_id = null;
        $item->save();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'project_item_detached',
            'description' => 'Item '.$itemCode.' removed from project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Item removed from project.');
    }

    public function assignItemToEmployees(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);

        \Log::info('Assigning item to employees', [
            'item_id' => $validated['item_id'],
            'employee_ids' => $validated['employee_ids'],
            'project_id' => $project->id,
        ]);

        $item = Item::where('id', $validated['item_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        $count = 0;
        foreach ($validated['employee_ids'] as $employee_id) {
            // Check if assignment already exists
            if (!ItemEmployeeAssignment::where('project_id', $project->id)
                ->where('item_id', $item->id)
                ->where('employee_id', $employee_id)
                ->exists()) {
                ItemEmployeeAssignment::create([
                    'project_id' => $project->id,
                    'item_id' => $item->id,
                    'employee_id' => $employee_id,
                ]);
                $count++;
            }
        }

        \Log::info('Assigned items', ['count' => $count]);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_assigned_to_employees',
            'description' => 'Item '.$item->item_code.' assigned to '.$count.' employees in project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Item assigned to '.$count.' employees successfully.');
    }

    public function unassignItem(Request $request, Project $project, ItemEmployeeAssignment $assignment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        if ($assignment->project_id !== $project->id) {
            abort(403);
        }

        $itemCode = $assignment->item->item_code;
        $assignment->delete();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_unassigned_from_employee',
            'description' => 'Item '.$itemCode.' unassigned from employee in project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Item unassigned from employee.');
    }

    public function importItemAssignments(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $validated['file']->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return redirect()->back()->with('error', 'Unable to open uploaded file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty.');
        }

        $map = [];
        foreach ($header as $index => $column) {
            $column = strtolower(trim($column));
            $map[$column] = $index;
        }

        $created = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $get = function (string $key) use ($map, $row) {
                if (! array_key_exists($key, $map)) {
                    return null;
                }

                return trim($row[$map[$key]] ?? '') ?: null;
            };

            $itemCode = $get('item_code');
            if (! $itemCode) {
                continue;
            }

            $employeeCode = $get('employee_code');
            if (! $employeeCode) {
                continue;
            }

            $item = Item::where('item_code', $itemCode)
                ->where('project_id', $project->id)
                ->first();

            $employee = Employee::where('employee_code', $employeeCode)->first();

            if ($item && $employee) {
                // Check if assignment already exists
                if (!ItemEmployeeAssignment::where('project_id', $project->id)
                    ->where('item_id', $item->id)
                    ->where('employee_id', $employee->id)
                    ->exists()) {
                    ItemEmployeeAssignment::create([
                        'project_id' => $project->id,
                        'item_id' => $item->id,
                        'employee_id' => $employee->id,
                    ]);
                    $created++;
                }
            }
        }

        fclose($handle);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_assignments_imported',
            'description' => $created.' item-employee assignments imported for project '.$project->code,
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);

        return redirect()->route('projects.show', $project)->with('success', $created.' item-employee assignments imported successfully.');
    }

    public function exportItemAssignments(Request $request, Project $project): StreamedResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $fileName = 'project-'.$project->code.'-assignments.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $callback = function () use ($project) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'tag_number',
                'item_name',
                'category',
                'employee_code',
                'employee_name',
                'position',
                'assigned_date',
            ]);

            $project->itemEmployeeAssignments()->with(['item.category', 'employee'])->chunk(200, function ($assignments) use ($handle) {
                foreach ($assignments as $assignment) {
                    fputcsv($handle, [
                        $assignment->item->tag_number ?? $assignment->item->item_code,
                        $assignment->item->name,
                        optional($assignment->item->category)->name,
                        $assignment->employee->employee_code,
                        $assignment->employee->name,
                        $assignment->employee->position,
                        $assignment->created_at->toDateString(),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printItemAssignments(Request $request, Project $project): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->projects()->where('projects.id', $project->id)->exists()) {
            abort(403);
        }

        $project->load(['itemEmployeeAssignments.item.category', 'itemEmployeeAssignments.employee']);

        return Inertia::render('Projects/PrintAssignments', [
            'project' => $project,
        ]);
    }
}
