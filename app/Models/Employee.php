<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'name',
        'created_by',
        'location',
        'position',
        'email',
        'phone',
        'address',
        'image_path',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects(): BelongsToMany
    {
        // Pivot table is project_employee (not employee_project), so specify it explicitly.
        return $this->belongsToMany(Project::class, 'project_employee')->withTimestamps();
    }

    public function itemEmployeeAssignments()
    {
        return $this->hasMany(\App\Models\ItemEmployeeAssignment::class);
    }
}
