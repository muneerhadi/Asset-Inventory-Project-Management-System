<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Shared between super admins and project managers, with data filtered in controllers
    Route::middleware('role:super_admin,project_manager')->group(function () {
        Route::resource('items', ItemController::class);
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/assign-item', [EmployeeController::class, 'assignItem'])
            ->name('employees.assign-item');
        Route::delete('employees/{employee}/unassign-item/{assignment}', [EmployeeController::class, 'unassignItem'])
            ->name('employees.unassign-item');
        Route::resource('projects', ProjectController::class);
        Route::get('projects/{project}/export-items', [ProjectController::class, 'exportItems'])
            ->name('projects.export-items');
        Route::post('projects/{project}/import-employees', [ProjectController::class, 'importEmployees'])
            ->name('projects.import-employees');
        Route::post('projects/{project}/import-items', [ProjectController::class, 'importItems'])
            ->name('projects.import-items');
        Route::post('projects/{project}/attach-employee', [ProjectController::class, 'attachEmployee'])
            ->name('projects.attach-employee');
        Route::post('projects/{project}/attach-item', [ProjectController::class, 'attachItem'])
            ->name('projects.attach-item');
        Route::delete('projects/{project}/detach-employee', [ProjectController::class, 'detachEmployee'])
            ->name('projects.detach-employee');
        Route::delete('projects/{project}/detach-item', [ProjectController::class, 'detachItem'])
            ->name('projects.detach-item');
        Route::post('projects/{project}/assign-item-to-employees', [ProjectController::class, 'assignItemToEmployees'])
            ->name('projects.assign-item-to-employees');
        Route::delete('projects/{project}/assignments/{assignment}', [ProjectController::class, 'unassignItem'])
            ->name('projects.unassign-item');
        Route::post('projects/{project}/import-item-assignments', [ProjectController::class, 'importItemAssignments'])
            ->name('projects.import-item-assignments');
        Route::get('projects/{project}/export-item-assignments', [ProjectController::class, 'exportItemAssignments'])
            ->name('projects.export-item-assignments');
        Route::get('projects/{project}/print-item-assignments', [ProjectController::class, 'printItemAssignments'])
            ->name('projects.print-item-assignments');

        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/project/{project}', [ReportController::class, 'project'])
            ->name('reports.project');
        Route::get('reports/damaged-items', [ReportController::class, 'damagedItems'])
            ->name('reports.damaged-items');
        Route::get('reports/economy', [ReportController::class, 'economy'])
            ->name('reports.economy');
        Route::get('reports/custom', [ReportController::class, 'custom'])
            ->name('reports.custom');

        // Search
        Route::get('search/items', [SearchController::class, 'items'])
            ->name('search.items');
        Route::get('search/employees', [SearchController::class, 'employees'])
            ->name('search.employees');
    });

    // Settings (super admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
        Route::put('settings/categories/{category}/toggle', [SettingsController::class, 'toggleCategory'])->name('settings.categories.toggle');
        Route::delete('settings/categories/{category}', [SettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');

        Route::post('settings/statuses', [SettingsController::class, 'storeStatus'])->name('settings.statuses.store');
        Route::delete('settings/statuses/{status}', [SettingsController::class, 'destroyStatus'])->name('settings.statuses.destroy');

        Route::post('settings/currencies', [SettingsController::class, 'storeCurrency'])->name('settings.currencies.store');
        Route::delete('settings/currencies/{currency}', [SettingsController::class, 'destroyCurrency'])->name('settings.currencies.destroy');

        Route::post('settings/project-managers', [SettingsController::class, 'storeProjectManager'])->name('settings.project-managers.store');
        Route::post('settings/project-managers/{user}/projects', [SettingsController::class, 'updateProjectManagerProjects'])->name('settings.project-managers.projects.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
