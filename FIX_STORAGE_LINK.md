# Fix Storage Symlink Issue

## Problem
The storage symlink in `public/storage` is pointing to the wrong location, which is why images are not displaying.

## Solution

Run this command in your project root directory:

```bash
php artisan storage:link
```

If that doesn't work, you can manually fix it:

### Windows (PowerShell as Administrator):
1. Open PowerShell as Administrator
2. Navigate to your project directory
3. Run:
   ```powershell
   Remove-Item "public\storage" -Force -ErrorAction SilentlyContinue
   New-Item -ItemType SymbolicLink -Path "public\storage" -Target "$PWD\storage\app\public"
   ```

### Alternative: Use Junction (Windows)
```powershell
Remove-Item "public\storage" -Force -ErrorAction SilentlyContinue
cmd /c mklink /J "public\storage" "storage\app\public"
```

## Verify
After creating the symlink, verify it points to the correct location:
```powershell
Get-Item "public\storage" | Format-List FullName, LinkType, Target
```

The Target should be: `C:\Users\hadim\project-asset-inventory-system\project-asset-inventory-system\Hadi project\storage\app\public`

## After Fixing
1. Clear browser cache
2. Refresh the page
3. Images should now display correctly

