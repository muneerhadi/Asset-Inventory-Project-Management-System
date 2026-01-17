<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemEmployeeAssignment;
use App\Models\ItemStatus;
use App\Models\Project;
use App\Rules\UniqueTagSequentialNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Base query used for stats (affected by project and search, but not by status filter)
        $baseQuery = Item::with(['category', 'status', 'currency', 'project', 'itemEmployeeAssignments']);

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $baseQuery->whereIn('project_id', $projectIds);
        }

        if ($search = $request->string('search')->trim()) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('tag_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // List query starts from base query and can be further filtered by status
        $itemsQuery = clone $baseQuery;

        // Optional status filter for clickable stats cards (only affects list, not stats)
        if ($status = $request->string('status')->trim()) {
            if ($status === 'available') {
                // Available = items with active status only
                $itemsQuery->whereHas('status', function ($q) {
                    $q->where('slug', 'active');
                });
            } else {
                $itemsQuery->whereHas('status', function ($q) use ($status) {
                    if (in_array($status, ['active', 'damaged', 'daghma'])) {
                        $q->where('slug', $status);
                    }
                });
            }
        }

        $items = $itemsQuery->latest()->paginate(10)->withQueryString();

        // Stats are based on base query (project + search only), independent of status filter
        $stats = [
            'total' => (clone $baseQuery)->count(),
            // Available = items with active status only
            'available' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', 'active'))->count(),
            'active' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', 'active'))->count(),
            'damaged' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', 'damaged'))->count(),
            'daghma' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', 'daghma'))->count(),
        ];

        return Inertia::render('Items/Index', [
            'items' => $items,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status ?? null,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $projectOptions = $user->isSuperAdmin()
            ? Project::orderBy('name')->get(['id', 'name'])
            : $user->projects()->orderBy('name')->get(['projects.id', 'projects.name']);

        return Inertia::render('Items/Create', [
            'categories' => ItemCategory::where('is_active', true)->orderBy('name')->get(),
            'statuses' => ItemStatus::orderBy('name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
            'projects' => $projectOptions,
            'nextSequentialNumber' => Item::getNextSequentialNumber(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'tag_number' => ['required', 'string', 'max:255', new UniqueTagSequentialNumber()],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'item_category_id' => ['required', 'exists:item_categories,id'],
            'item_status_id' => ['required', 'exists:item_statuses,id'],
            'price' => ['required', 'numeric'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'depreciation_rate' => ['nullable', 'numeric'],
            'purchase_date' => ['nullable', 'date'],
            'voucher_number' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'sublocation' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'remarks' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:20480'], // 20MB per image
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
        ]);

        // Auto-generate item code if not provided
        if (empty($validated['item_code'])) {
            $lastItem = Item::orderBy('id', 'desc')->first();
            $nextNumber = $lastItem ? $lastItem->id + 1 : 1;
            $validated['item_code'] = 'ITEM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            while (Item::where('item_code', $validated['item_code'])->exists()) {
                $nextNumber++;
                $validated['item_code'] = 'ITEM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        }

        // Handle PDF file
        if ($request->hasFile('pdf_file')) {
            $path = $request->file('pdf_file')->store('items/pdfs', 'public');
            $validated['pdf_path'] = '/storage/'.$path;
        }

        // Handle multiple images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public');
                $imagePaths[] = '/storage/'.$path;
            }
            // Store all images in images array
            $validated['images'] = $imagePaths;
            // Store first image as primary image_path for backward compatibility
            if (!empty($imagePaths)) {
                $validated['image_path'] = $imagePaths[0];
            }
        } elseif ($request->hasFile('image')) {
            // Fallback for single image upload
            $path = $request->file('image')->store('items', 'public');
            $validated['image_path'] = '/storage/'.$path;
            // Also store in images array for consistency
            $validated['images'] = [$validated['image_path']];
        }

        if (! $user->isSuperAdmin() && isset($validated['project_id'])) {
            // Ensure project managers can only assign to their projects
            $allowedProjectIds = $user->projects()->pluck('projects.id');
            if (! $allowedProjectIds->contains($validated['project_id'])) {
                abort(403);
            }
        }

        $item = Item::create($validated);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_created',
            'description' => 'Item '.$item->item_code.' created',
            'subject_type' => Item::class,
            'subject_id' => $item->id,
        ]);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function show(Request $request, Item $item): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if ($item->project_id && ! $projectIds->contains($item->project_id)) {
                abort(403);
            }
        }

        $item->load(['category', 'status', 'currency', 'project', 'itemEmployeeAssignments.employee', 'itemEmployeeAssignments.project']);

        return Inertia::render('Items/Show', [
            'item' => $item,
        ]);
    }

    public function edit(Request $request, Item $item): Response
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if ($item->project_id && ! $projectIds->contains($item->project_id)) {
                abort(403);
            }
        }

        $projectOptions = $user->isSuperAdmin()
            ? Project::orderBy('name')->get(['id', 'name'])
            : $user->projects()->orderBy('name')->get(['projects.id', 'projects.name']);

        return Inertia::render('Items/Edit', [
            'item' => $item->load(['category', 'status', 'currency', 'project']),
            'categories' => ItemCategory::where('is_active', true)->orderBy('name')->get(),
            'statuses' => ItemStatus::orderBy('name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
            'projects' => $projectOptions,
        ]);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if ($item->project_id && ! $projectIds->contains($item->project_id)) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'tag_number' => ['nullable', 'string', 'max:255', new UniqueTagSequentialNumber($item->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'item_category_id' => ['required', 'exists:item_categories,id'],
            'item_status_id' => ['required', 'exists:item_statuses,id'],
            'price' => ['nullable', 'numeric'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'depreciation_rate' => ['nullable', 'numeric'],
            'purchase_date' => ['nullable', 'date'],
            'voucher_number' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'sublocation' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'remarks' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:20480'], // 20MB per image
            'image' => ['nullable', 'image', 'max:2048'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
        ]);

        // Handle PDF file
        if ($request->hasFile('pdf_file')) {
            $path = $request->file('pdf_file')->store('items/pdfs', 'public');
            $validated['pdf_path'] = '/storage/'.$path;
        }

        // Handle multiple images - use images_to_keep if provided, otherwise keep all existing
        $imagesToKeep = $request->input('images_to_keep', []);
        if (!is_array($imagesToKeep)) {
            $imagesToKeep = [];
        }
        
        $allImages = $imagesToKeep; // Start with images to keep
        
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public');
                $imagePaths[] = '/storage/'.$path;
            }
            
            // Merge kept images with new ones (limit to max 4 total)
            $allImages = array_merge($allImages, $imagePaths);
            $allImages = array_slice($allImages, 0, 4); // Limit to 4 images
            
            // Store all images in images array
            $validated['images'] = $allImages;
            // Store first image as primary image_path for backward compatibility
            if (!empty($allImages)) {
                $validated['image_path'] = $allImages[0];
            }
        } elseif ($request->hasFile('image')) {
            // Fallback for single image upload
            $path = $request->file('image')->store('items', 'public');
            $newImagePath = '/storage/'.$path;
            
            // Merge kept images with new one (limit to max 4 total)
            $allImages = array_merge($allImages, [$newImagePath]);
            $allImages = array_slice($allImages, 0, 4); // Limit to 4 images
            
            $validated['image_path'] = $allImages[0];
            $validated['images'] = $allImages;
        } else {
            // No new images, but update with kept images
            $validated['images'] = $allImages;
            if (!empty($allImages)) {
                $validated['image_path'] = $allImages[0];
            } else {
                $validated['image_path'] = null;
            }
        }

        if (! $user->isSuperAdmin() && isset($validated['project_id'])) {
            $allowedProjectIds = $user->projects()->pluck('projects.id');
            if (! $allowedProjectIds->contains($validated['project_id'])) {
                abort(403);
            }
        }

        $item->update($validated);

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_updated',
            'description' => 'Item '.$item->item_code.' updated',
            'subject_type' => Item::class,
            'subject_id' => $item->id,
        ]);

        return redirect()->route('items.show', $item)->with('success', 'Item updated successfully.');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            if ($item->project_id && ! $projectIds->contains($item->project_id)) {
                abort(403);
            }
        }

        $itemCode = $item->item_code;
        $item->delete();

        Activity::create([
            'user_id' => $user->id,
            'action' => 'item_deleted',
            'description' => 'Item '.$itemCode.' deleted',
            'subject_type' => Item::class,
            'subject_id' => $item->id,
        ]);

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        try {
            $uploadedFile = $validated['file'];
            $path = $uploadedFile->getRealPath();
            $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: '');

            $fileSignature = @file_get_contents($path, false, null, 0, 2);
            if ($fileSignature === 'PK' && ! in_array($extension, ['xlsx', 'xls'], true)) {
                return redirect()->back()->with(
                    'error',
                    'This file looks like an Excel file (.xlsx) but was uploaded as a non-Excel format. Please upload the original .xlsx file (requires phpoffice/phpspreadsheet) or export it as a real .csv.'
                );
            }

            if (in_array($extension, ['xlsx', 'xls'], true) && ! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                return redirect()->back()->with(
                    'error',
                    'Excel (.xlsx/.xls) import is not enabled on the server. Please install phpoffice/phpspreadsheet or upload a .csv file instead.'
                );
            }
            $handle = fopen($path, 'r');

            if (!$handle) {
                return redirect()->back()->with('error', 'Unable to open uploaded file.');
            }

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return redirect()->back()->with('error', 'File is empty or invalid.');
            }

            // Log header for debugging
            \Log::info('Import Headers:', $header);

            $map = [];
            foreach ($header as $index => $column) {
                $map[strtolower(trim($column))] = $index;
            }

            if (! array_key_exists('asset tag no', $map) || ! array_key_exists('item name', $map)) {
                fclose($handle);
                return redirect()->back()->with(
                    'error',
                    'Invalid import file format. Required columns are missing: Asset Tag NO and Item Name.'
                );
            }

            $created = 0;
            $errors = [];
            $duplicates = [];
            $missingEmployees = [];
            $existingTagNumbers = Item::pluck('tag_number')->toArray();
            $rowNumber = 1;
            $allowedProjectIds = null;
            if (! $user->isSuperAdmin()) {
                $allowedProjectIds = $user->projects()->pluck('projects.id')->toArray();
            }

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                $get = function (string $key) use ($map, $row) {
                    $key = strtolower($key);
                    if (!array_key_exists($key, $map)) {
                        return null;
                    }
                    return trim($row[$map[$key]] ?? '') ?: null;
                };

                $tagNumber = $get('asset tag no');
                $name = $get('item name');

                \Log::info('Processing row ' . $rowNumber, ['tag' => $tagNumber, 'name' => $name]);

                // Skip if tag number or name is empty
                if (!$tagNumber || !$name) {
                    $errors[] = "Row {$rowNumber}: Missing tag number or item name";
                    continue;
                }

                // Extract last 5 digits from tag number
                $lastDigits = '';
                if (preg_match('/(\d+)$/', $tagNumber, $matches)) {
                    $lastDigits = $matches[1];
                    
                    // Check if any existing tag number ends with same digits
                    $isDuplicate = false;
                    foreach ($existingTagNumbers as $existingTag) {
                        if (preg_match('/(\d+)$/', $existingTag, $existingMatches)) {
                            if ($existingMatches[1] === $lastDigits) {
                                $isDuplicate = true;
                                break;
                            }
                        }
                    }
                    
                    if ($isDuplicate) {
                        $duplicates[] = $tagNumber . ' (last digits: ' . $lastDigits . ')';
                        $errors[] = "Row {$rowNumber} (Tag: {$tagNumber}): Duplicate tag number (last digits: {$lastDigits})";
                        continue;
                    }
                }

                try {
                    $categoryName = $get('category');
                    $statusName = $get('status');
                    $projectName = $get('poject') ?: $get('project');
                    $empName = $get('emp-name');
                    $dateValue = $get('date');

                    // Parse date in various formats
                    $purchaseDate = null;
                    if ($dateValue) {
                        try {
                            $purchaseDate = \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // If date parsing fails, leave it null
                            $purchaseDate = null;
                        }
                    }

                    $category = $categoryName ? ItemCategory::firstOrCreate(['name' => $categoryName]) : ItemCategory::first();
                    $status = $statusName ? ItemStatus::where('name', $statusName)->first() : ItemStatus::where('is_default', true)->first();
                    $project = $projectName ? Project::where('name', $projectName)->first() : null;

                    if ($projectName) {
                        if (! $project) {
                            $errors[] = "Row {$rowNumber} (Tag: {$tagNumber}): Project '{$projectName}' not found for this item";
                            continue;
                        }
                        if ($allowedProjectIds !== null && ! in_array($project->id, $allowedProjectIds, true)) {
                            $errors[] = "Row {$rowNumber} (Tag: {$tagNumber}): Project '{$projectName}' not found for this item";
                            continue;
                        }
                    }

                    if (!$category) {
                        $category = ItemCategory::create(['name' => 'General']);
                    }
                    if (!$status) {
                        $status = ItemStatus::create(['name' => 'Active', 'slug' => 'active', 'is_default' => true]);
                    }

                    // Auto-generate item code
                    $lastItem = Item::orderBy('id', 'desc')->first();
                    $nextNumber = $lastItem ? $lastItem->id + 1 : 1;
                    $itemCode = 'ITEM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                    
                    while (Item::where('item_code', $itemCode)->exists()) {
                        $nextNumber++;
                        $itemCode = 'ITEM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                    }

                    // Get default currency (Afghani)
                    $defaultCurrency = Currency::where('code', 'AFN')->first() ?: Currency::first();

                    $item = Item::create([
                        'item_code' => $itemCode,
                        'tag_number' => $tagNumber,
                        'name' => $name,
                        'description' => $get('description'),
                        'item_category_id' => $category->id,
                        'item_status_id' => $status->id,
                        'price' => ($price = $get('price')) ? (float) str_replace(',', '', $price) : null,
                        'currency_id' => $defaultCurrency?->id,
                        'purchase_date' => $purchaseDate,
                        'voucher_number' => $get('voucher number'),
                        'location' => $get('location'),
                        'sublocation' => $get('sublocation'),
                        'model' => $get('model'),
                        'serial_number' => $get('serial number'),
                        'project_id' => $project?->id,
                        'remarks' => $get('remarks') ?: 'Imported from Excel',
                    ]);

                    // Add to existing tags to check for duplicates in same import
                    $existingTagNumbers[] = $tagNumber;

                    // Handle employee assignment
                    if ($empName) {
                        $employee = Employee::where('name', $empName)->first();
                        if ($employee) {
                            ItemEmployeeAssignment::create([
                                'item_id' => $item->id,
                                'employee_id' => $employee->id,
                                'project_id' => $project?->id,
                            ]);
                        } else {
                            if (!in_array($empName, $missingEmployees)) {
                                $missingEmployees[] = $empName;
                            }
                        }
                    }

                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber} (Tag: {$tagNumber}): " . $e->getMessage();
                }
            }

            fclose($handle);

            Activity::create([
                'user_id' => $user->id,
                'action' => 'items_imported',
                'description' => $created . ' items imported from Excel file',
                'subject_type' => Item::class,
                'subject_id' => null,
            ]);

            $message = $created . ' items imported successfully.';
            
            \Log::info('Import completed', [
                'created' => $created,
                'duplicates' => count($duplicates),
                'errors' => count($errors),
                'missing_employees' => count($missingEmployees)
            ]);
            
            if (!empty($duplicates)) {
                $message .= ' Skipped ' . count($duplicates) . ' items with duplicate tag numbers: ' . implode(', ', array_slice($duplicates, 0, 3));
                if (count($duplicates) > 3) {
                    $message .= ' and ' . (count($duplicates) - 3) . ' more.';
                }
            }
            if (!empty($missingEmployees)) {
                $message .= ' Warning: These employees are not in the system: ' . implode(', ', array_slice($missingEmployees, 0, 3));
                if (count($missingEmployees) > 3) {
                    $message .= ' and ' . (count($missingEmployees) - 3) . ' more.';
                }
                $message .= ' Please add them first.';
            }
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' items had errors and were skipped.';
                $message .= ' ' . implode(' | ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= ' | and ' . (count($errors) - 3) . ' more.';
                }
            }

            return redirect()->route('items.index')
                ->with('success', $message)
                ->with('import_summary', [
                    'imported' => $created,
                    'skipped_duplicates' => count($duplicates),
                    'skipped_errors' => count($errors),
                    'errors' => array_values($errors),
                    'duplicate_tags' => array_values($duplicates),
                    'missing_employees' => array_values($missingEmployees),
                ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    public function inStock(Request $request): Response
    {
        $user = $request->user();

        $itemsQuery = Item::with(['category', 'status', 'currency', 'project', 'itemEmployeeAssignments'])
            ->whereDoesntHave('itemEmployeeAssignments');

        if (! $user->isSuperAdmin()) {
            $projectIds = $user->projects()->pluck('projects.id');
            $itemsQuery->whereIn('project_id', $projectIds);
        }

        $items = $itemsQuery->latest()->paginate(10)->withQueryString();

        return Inertia::render('Items/InStock', [
            'items' => $items,
        ]);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['exists:items,id'],
        ]);

        $count = Item::whereIn('id', $validated['item_ids'])->count();
        Item::whereIn('id', $validated['item_ids'])->delete();

        Activity::create([
            'user_id' => $request->user()->id,
            'action' => 'items_bulk_deleted',
            'description' => $count . ' items deleted in bulk',
            'subject_type' => Item::class,
            'subject_id' => null,
        ]);

        return redirect()->route('items.index')->with('success', $count . ' items deleted successfully.');
    }
}
