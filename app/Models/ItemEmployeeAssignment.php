<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemEmployeeAssignment extends Model
{
    protected $fillable = [
        'project_id',
        'item_id',
        'employee_id',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'item_id' => 'integer',
        'employee_id' => 'integer',
    ];

    protected $attributes = [
        'project_id' => null,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
