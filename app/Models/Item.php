<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'tag_number',
        'name',
        'description',
        'item_category_id',
        'item_status_id',
        'price',
        'currency_id',
        'depreciation_rate',
        'purchase_date',
        'voucher_number',
        'location',
        'sublocation',
        'model',
        'serial_number',
        'project_id',
        'remarks',
        'image_path',
        'images',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'images' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ItemStatus::class, 'item_status_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function itemEmployeeAssignments()
    {
        return $this->hasMany(ItemEmployeeAssignment::class);
    }
}
