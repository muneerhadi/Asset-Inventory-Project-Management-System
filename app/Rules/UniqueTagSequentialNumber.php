<?php

namespace App\Rules;

use App\Models\Item;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueTagSequentialNumber implements ValidationRule
{
    private $ignoreId;

    public function __construct($ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if tag number ends with digits
        if (!preg_match('/(\d+)$/', $value)) {
            $fail('The tag number must end with a number.');
            return;
        }

        // Extract sequential number
        $sequentialNumber = Item::extractSequentialNumber($value);
        
        if (!$sequentialNumber) {
            $fail('The tag number must end with a valid number.');
            return;
        }

        // Check if sequential number already exists
        $existingItem = Item::query()
            ->when($this->ignoreId, fn($q) => $q->where('id', '!=', $this->ignoreId))
            ->get()
            ->first(function ($item) use ($sequentialNumber) {
                return Item::extractSequentialNumber($item->tag_number) === $sequentialNumber;
            });

        if ($existingItem) {
            $fail('The sequential number ' . $sequentialNumber . ' is already taken.');
        }
    }
}
