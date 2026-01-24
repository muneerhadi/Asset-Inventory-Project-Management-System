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
        'created_by',
        'remarks',
        'image_path',
        'images',
        'pdf_path',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function itemEmployeeAssignments()
    {
        return $this->hasMany(ItemEmployeeAssignment::class);
    }

    /**
     * Extract sequential number from tag number
     */
    public static function extractSequentialNumber($tagNumber)
    {
        if (preg_match('/(\d+)$/', $tagNumber, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Get next available sequential number
     */
    public static function getNextSequentialNumber()
    {
        $maxNumber = self::query()
            ->get()
            ->map(fn($item) => self::extractSequentialNumber($item->tag_number))
            ->filter()
            ->max();
        
        return $maxNumber ? $maxNumber + 1 : 1;
    }

    /**
     * Get recent items sorted by sequential number
     */
    public static function getRecentItemsBySequence($limit = 3)
    {
        return self::with(['category', 'status', 'project'])
            ->get()
            ->map(function ($item) {
                $item->sequential_number = self::extractSequentialNumber($item->tag_number);
                return $item;
            })
            ->filter(fn($item) => $item->sequential_number !== null)
            ->sortByDesc('sequential_number')
            ->take($limit)
            ->values();
    }
}
