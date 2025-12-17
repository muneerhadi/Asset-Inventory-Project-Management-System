<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the actual foreign key constraint name from the database
        $constraints = \Illuminate\Support\Facades\DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'item_employee_assignments' 
            AND COLUMN_NAME = 'project_id' 
            AND REFERENCED_TABLE_NAME = 'projects'
        ");
        
        // Drop the foreign key constraint using the actual name
        if (!empty($constraints)) {
            $constraintName = $constraints[0]->CONSTRAINT_NAME;
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `item_employee_assignments` DROP FOREIGN KEY `{$constraintName}`");
        }
        
        // Use DB facade to modify the column since change() might not work with foreignId
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `item_employee_assignments` MODIFY `project_id` BIGINT UNSIGNED NULL');
        
        Schema::table('item_employee_assignments', function (Blueprint $table) {
            // Re-add the foreign key constraint (nullable foreign keys are allowed)
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_employee_assignments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['project_id']);
        });
        
        // Use DB facade to modify the column back to NOT NULL
        // Note: This will fail if there are any NULL values in the column
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `item_employee_assignments` MODIFY `project_id` BIGINT UNSIGNED NOT NULL');
        
        Schema::table('item_employee_assignments', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }
};

