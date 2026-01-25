# Fix: Login 500 Internal Server Error on cPanel

When login returns **500 (Internal Server Error)** with no visible error, the cause is on the server. Follow these steps to find and fix it.

---

## 1. See the real error

### A. Enable debug temporarily (fastest)

On the server, edit `.env`:

```env
APP_DEBUG=true
```

Try to log in again. The page will show the real error (e.g. DB, missing table, permissions).  
**Set `APP_DEBUG=false` again after you fix it.**

### B. Check Laravel log on the server

Open **File Manager** or SFTP and read:

```
storage/logs/laravel.log
```

Scroll to the bottom. The last `[date] production.ERROR:` block is usually the one for the 500. Look for lines like:

- `SQLSTATE` / `QueryException` → database
- `PDOException` → database connection
- `failed to open stream` / `Permission denied` → filesystem
- `Class not found` → autoload / deployment

---

## 2. Typical causes and fixes

### Database connection (.env)

cPanel uses MySQL, not SQLite. Your `.env` must look like:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password
```

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` must match the MySQL database and user created in cPanel.
- `DB_HOST` is often `localhost`. If it fails, try the exact host shown in cPanel’s **Remote MySQL** or **MySQL** section (e.g. `localhost` or a hostname).

### APP_KEY

`.env` must have a key:

```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

If it’s empty, generate one locally and put it in the server’s `.env`:

```bash
php artisan key:generate --show
```

### Migrations not run on the server

Login needs at least: `users`, `sessions`, `cache`, `cache_locks`, and `settings`.

In cPanel **Terminal** (or SSH) run, from the project root (where `artisan` is):

```bash
php artisan migrate --force
```

`--force` is required when `APP_ENV=production`.

If a migration fails, the error in the terminal (or in `storage/logs/laravel.log`) will tell you what’s wrong (e.g. missing table, duplicate column).

### Session and cache tables

This app uses **database** sessions and **database** cache by default. These tables must exist:

- `sessions` (from `0001_01_01_000000_create_users_table`)
- `cache` and `cache_locks` (from `0001_01_01_000001_create_cache_table`)

If you never ran `php artisan migrate` on the server, they will be missing and login can 500. Run:

```bash
php artisan migrate --force
```

### Storage and cache permissions

Laravel needs to write to `storage/` and `bootstrap/cache/`. On the server:

```bash
chmod -R 775 storage bootstrap/cache
```

If your web user is different from your SSH user (e.g. `nobody` or `apache`), you may need to set the owner or use 777 only if your host allows it. 775 is preferred when the web server runs as a user in the same group as the files.

### Document root must be `public/`

The site’s document root in cPanel must point to the **`public`** folder of your project, e.g.:

- `public_html` → `your-project/public`  
  or  
- `public_html/yourapp` → `your-project/public`

If it points to the project root (where `artisan` and `.env` are), routing and `index.php` may not work correctly and you can get 500 or “login” as a file.

### PHP version

Laravel 11 requires PHP 8.2 or higher. In cPanel → **Select PHP Version** (or **MultiPHP Manager**), set the app’s domain to PHP 8.2+.

### "This password does not use the Bcrypt algorithm"

This happens when a user’s `password` in the database is **not** a Bcrypt hash (e.g. plain text, MD5, or imported from another system). Laravel expects Bcrypt (`$2y$...`).

**1. Find affected users** (in cPanel Terminal, from the project root):

```bash
php artisan user:list-invalid-passwords
```

**2. Reset password for a user**:

```bash
php artisan user:reset-password "user@example.com" "NewSecurePassword123"
```

Then have the user log in with the new password. They can change it later in the app.

---

## 3. Optional: use file session and file cache

If the database or `sessions`/`cache` tables are misconfigured or missing and you can’t fix them immediately, you can switch to file-based session and cache.

In `.env`:

```env
SESSION_DRIVER=file
CACHE_STORE=file
```

Then ensure `storage/framework/sessions` and `storage/framework/cache` exist and are writable (same as above: `chmod -R 775 storage`).

---

## 4. Checklist

- [ ] `.env` exists in the project root on the server (not only `.env.example`)
- [ ] `APP_KEY` is set in `.env`
- [ ] `APP_DEBUG=true` temporarily to see the error, or you checked `storage/logs/laravel.log`
- [ ] `DB_CONNECTION=mysql` and `DB_*` match the cPanel MySQL database
- [ ] `php artisan migrate --force` has been run on the server
- [ ] `storage` and `bootstrap/cache` are writable (`chmod -R 775 storage bootstrap/cache`)
- [ ] Document root points to the `public` directory
- [ ] PHP 8.2+ is selected for the domain

---

## 5. After fixing

1. Set `APP_DEBUG=false` again.
2. Clear config and cache on the server:

   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. Try logging in again.

If it still returns 500, the exact message from `APP_DEBUG=true` or the latest entry in `storage/logs/laravel.log` will point to the next fix (e.g. another missing table, permission, or PHP/extension issue).
