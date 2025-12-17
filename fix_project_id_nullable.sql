-- Run this SQL script directly in your database to make project_id nullable
-- This fixes the constraint violation error when assigning items to employees

-- Step 1: Find and drop the foreign key constraint
-- First, find the constraint name (run this query to see the constraint name):
-- SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'item_employee_assignments' 
-- AND COLUMN_NAME = 'project_id' AND REFERENCED_TABLE_NAME = 'projects';

-- Then drop it (replace 'CONSTRAINT_NAME_HERE' with the actual name from above):
-- ALTER TABLE `item_employee_assignments` DROP FOREIGN KEY `CONSTRAINT_NAME_HERE`;

-- OR use this if the constraint name is the default Laravel naming:
ALTER TABLE `item_employee_assignments` DROP FOREIGN KEY `item_employee_assignments_project_id_foreign`;

-- Step 2: Make the column nullable
ALTER TABLE `item_employee_assignments` MODIFY `project_id` BIGINT UNSIGNED NULL;

-- Step 3: Re-add the foreign key constraint (nullable foreign keys are allowed)
ALTER TABLE `item_employee_assignments` 
ADD CONSTRAINT `item_employee_assignments_project_id_foreign` 
FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

