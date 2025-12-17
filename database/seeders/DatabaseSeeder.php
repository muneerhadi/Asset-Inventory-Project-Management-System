<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'password' => Hash::make('password'),
            ],
        );

        // Project manager user
        $projectManager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Project Manager',
                'role' => 'project_manager',
                'password' => Hash::make('password'),
            ],
        );

        // Currencies
        $usd = Currency::updateOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$', 'is_default' => true],
        );

        $afg = Currency::updateOrCreate(
            ['code' => 'AFG'],
            ['name' => 'Afghani', 'symbol' => '؋', 'is_default' => false],
        );

        // Item statuses
        $activeStatus = ItemStatus::updateOrCreate(
            ['slug' => 'active'],
            [
                'name' => 'Active',
                'description' => 'In active use',
                'is_default' => true,
                'is_available' => true,
                'is_damaged' => false,
            ],
        );

        $damagedStatus = ItemStatus::updateOrCreate(
            ['slug' => 'damaged'],
            [
                'name' => 'Damaged',
                'description' => 'Damaged items',
                'is_default' => false,
                'is_available' => false,
                'is_damaged' => true,
            ],
        );

        $daghmaStatus = ItemStatus::updateOrCreate(
            ['slug' => 'daghma'],
            [
                'name' => 'Daghma',
                'description' => 'Disposed / retired items',
                'is_default' => false,
                'is_available' => false,
                'is_damaged' => false,
            ],
        );

        // Item categories
        $laptopCategory = ItemCategory::updateOrCreate(
            ['name' => 'Laptop'],
            ['description' => 'Laptop computers'],
        );

        $furnitureCategory = ItemCategory::updateOrCreate(
            ['name' => 'Furniture'],
            ['description' => 'Office furniture'],
        );

        // Sample project
        $projectAlpha = Project::updateOrCreate(
            ['code' => 'PRJ-001'],
            [
                'name' => 'Project Alpha',
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(5),
                'description' => 'Sample seeded project.',
            ],
        );

        // Assign project manager to project
        $projectManager->projects()->syncWithoutDetaching([$projectAlpha->id]);

        // Sample employee
        $employee = Employee::updateOrCreate(
            ['employee_code' => 'EMP-001'],
            [
                'name' => 'John Doe',
                'location' => 'Main Site',
                'position' => 'Engineer',
                'email' => 'john.doe@example.com',
                'phone' => '+93-700-000-000',
                'address' => 'Kabul',
            ],
        );

        $employee->projects()->syncWithoutDetaching([$projectAlpha->id]);

        // Sample items
        Item::updateOrCreate(
            ['item_code' => 'ITM-001'],
            [
                'tag_number' => 'TAG-001',
                'name' => 'Dell Laptop',
                'description' => '15" laptop for project team',
                'item_category_id' => $laptopCategory->id,
                'item_status_id' => $activeStatus->id,
                'price' => 800,
                'currency_id' => $usd->id,
                'depreciation_rate' => 20,
                'purchase_date' => now()->subMonths(2),
                'voucher_number' => 'VCH-001',
                'location' => 'Main Site',
                'sublocation' => 'Office 101',
                'model' => 'Dell Latitude',
                'project_id' => $projectAlpha->id,
                'remarks' => 'Assigned to John Doe',
            ],
        );

        Item::updateOrCreate(
            ['item_code' => 'ITM-002'],
            [
                'tag_number' => 'TAG-002',
                'name' => 'Office Chair',
                'description' => 'Ergonomic chair',
                'item_category_id' => $furnitureCategory->id,
                'item_status_id' => $damagedStatus->id,
                'price' => 120,
                'currency_id' => $afg->id,
                'depreciation_rate' => 10,
                'purchase_date' => now()->subYear(),
                'voucher_number' => 'VCH-002',
                'location' => 'Main Site',
                'sublocation' => 'Office 102',
                'model' => 'Model X',
                'project_id' => $projectAlpha->id,
                'remarks' => 'Armrest broken',
            ],
        );
    }
}
