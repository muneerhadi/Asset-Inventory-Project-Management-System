# Excel/CSV Import Format Guide

This guide explains the format required for importing employees and items into projects.

## Employees Import Format

### Required Columns:
- **employee_code** *(Required)* - Unique identifier for the employee (e.g., "EMP001")
- **name** *(Required)* - Employee's full name (e.g., "John Doe")

### Optional Columns:
- **position** - Job title (e.g., "Manager", "Developer")
- **email** - Email address (e.g., "john@example.com")
- **phone** - Phone number (e.g., "+1-555-0123")
- **location** - Work location/office (e.g., "New York Office")
- **address** - Home or office address

### Example Employee CSV:
```
employee_code,name,position,email,phone,location,address
EMP001,John Doe,Project Manager,john.doe@example.com,+1-555-0101,New York,123 Main St
EMP002,Jane Smith,Developer,jane.smith@example.com,+1-555-0102,New York,456 Oak Ave
EMP003,Bob Johnson,QA Engineer,bob.johnson@example.com,+1-555-0103,Boston,789 Pine Rd
```

**Behavior:**
- Rows with missing `employee_code` or `name` will be skipped
- If an employee with the same `employee_code` exists, their details will be updated
- New employees will be created
- Employees will be automatically attached to the project if not already attached

---

## Items Import Format

### Required Columns:
- **item_code** *(Required)* - Unique identifier for the item (e.g., "ITEM-001")
- **name** *(Required)* - Item name/description

### Optional Columns:
- **tag_number** - Asset tag number (e.g., "AT-12345")
- **description** - Detailed description of the item
- **category** - Item category name (e.g., "Electronics", "Furniture")
  - If the category doesn't exist, it will be created automatically
- **status** - Item status (e.g., "active", "inactive", "damaged")
  - Should match existing status slugs or names
  - If not provided or not found, the default status will be used
- **price** - Unit price as a decimal number (e.g., "1500.50")
- **currency** - Currency code (e.g., "USD", "EUR", "GBP")
  - Must match an existing currency code in the system
- **purchase_date** - Date in YYYY-MM-DD format (e.g., "2024-01-15")
- **voucher_number** - Receipt or voucher reference number
- **location** - Physical location (e.g., "Warehouse A", "Office Building")
- **sublocation** - Specific sublocation within the location (e.g., "Shelf B3")
- **model** - Model number or name (e.g., "Dell XPS 13")
- **depreciation_rate** - Annual depreciation percentage (e.g., "15" for 15%)
- **remarks** - Additional notes or comments

### Example Item CSV:
```
item_code,tag_number,name,description,category,status,price,currency,purchase_date,location,model,remarks
ITEM-001,AT-00001,Dell Laptop,Business laptop,Electronics,active,1500.00,USD,2024-01-15,Office,Dell XPS 13,In good condition
ITEM-002,AT-00002,Office Chair,Executive chair,Furniture,active,450.00,USD,2024-02-01,Warehouse A,Steelcase Leap,Scratch on armrest
ITEM-003,AT-00003,Printer,Network printer,Electronics,inactive,2500.00,USD,2023-06-10,Office,HP LaserJet,Not in use
```

**Behavior:**
- Rows with missing `item_code` or `name` will be skipped
- If an item with the same `item_code` exists, it will be updated (except project assignment)
- New items will be created
- Items will be automatically assigned to the project
- Unknown categories will be created automatically
- Invalid status will default to the system's default status

---

## Supported File Formats

- **CSV (.csv)** - Comma-separated values
- **Excel (.xlsx, .xls)** - Microsoft Excel workbooks
- **Text (.txt)** - Tab or comma-separated text files

## Important Notes

1. **Column Names**: Column names are case-insensitive and will be trimmed of whitespace
2. **Empty Cells**: Empty cells in optional columns will be treated as null/missing
3. **Duplicate Prevention**: 
   - For employees: existing employee_code will be updated, not duplicated
   - For items: existing item_code will be updated, not duplicated
4. **Activity Logging**: All imports are logged in the activity log with import counts
5. **Project Assignment**:
   - Imported employees are automatically attached to the project
   - Imported items are automatically assigned to the project

## Tips for Successful Import

1. **Use Headers**: Always include column headers in the first row
2. **Data Validation**: Ensure required fields are never empty
3. **Format Dates**: Use YYYY-MM-DD format for dates (e.g., 2024-01-15)
4. **Match Existing Data**: For category and status, use exact names or slugs that exist in your system
5. **Currency Codes**: Use standard ISO 4217 currency codes (USD, EUR, GBP, etc.)
6. **Test First**: Create a small test file with a few rows before importing large datasets

## Troubleshooting

| Issue | Solution |
|-------|----------|
| File upload fails | Ensure file is .csv, .xlsx, .xls, or .txt |
| Rows are skipped | Check that `employee_code`/`name` or `item_code`/`name` are not empty |
| Data not imported | Verify column names match exactly (case-insensitive) |
| Items show wrong category | Create the category in Settings first, then import |
| Wrong status applied | Check that status name/slug matches existing statuses |
