-- Simple SQL to fix project_id nullable issue
-- Run this in your MySQL database

-- First, find the constraint name
SELECT CONSTRAINT_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'item_employee_assignments' 
AND COLUMN_NAME = 'project_id' 
AND REFERENCED_TABLE_NAME = 'projects';

-- Then replace 'YOUR_CONSTRAINT_NAME' below with the name from above and run:

SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'item_employee_assignments' 
    AND COLUMN_NAME = 'project_id' 
    AND REFERENCED_TABLE_NAME = 'projects'
    LIMIT 1
);

SET @sql = CONCAT('ALTER TABLE `item_employee_assignments` DROP FOREIGN KEY `', @constraint_name, '`');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Make column nullable
ALTER TABLE `item_employee_assignments` MODIFY `project_id` BIGINT UNSIGNED NULL;

-- Re-add foreign key
ALTER TABLE `item_employee_assignments` 
ADD CONSTRAINT `item_employee_assignments_project_id_foreign` 
FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

