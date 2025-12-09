# 404 Error Troubleshooting Guide

## Step-by-Step Debugging

### Step 1: Test Basic Routing
Try accessing the test method:
```
https://admin.razastar.in/admin/rider-live-status/test
```

**Expected:** Should show "Rider Live Status Controller is accessible!"

**If this works:** Routing is fine, issue is in `index()` method
**If this fails:** Routing issue, continue to Step 2

### Step 2: Try Different URL Formats

Try these URLs one by one:

1. **With dashes (standard):**
   ```
   https://admin.razastar.in/admin/rider-live-status
   ```

2. **With index.php:**
   ```
   https://admin.razastar.in/index.php/admin/rider-live-status
   ```

3. **With underscores:**
   ```
   https://admin.razastar.in/admin/rider_live_status
   ```

4. **Direct controller name:**
   ```
   https://admin.razastar.in/index.php/admin/Rider_live_status
   ```

### Step 3: Check File Permissions

Verify controller file exists and is readable:
```bash
ls -la application/controllers/admin/Rider_live_status.php
```

Should show: `-rw-r--r--` (readable by web server)

### Step 4: Check Class Name Match

**File name:** `Rider_live_status.php` ✅
**Class name:** `Rider_live_status` ✅
**URL:** `rider-live-status` (CodeIgniter converts to `rider_live_status`) ✅

### Step 5: Check Permissions

The controller checks:
```php
if (!has_permissions('read', 'rider')) {
    redirect('admin/home', 'refresh');
}
```

**Make sure:**
- Admin user is logged in
- Admin user has `read` permission for `rider` module
- Check in database: `users_groups` table

### Step 6: Check View File

**Controller uses:**
```php
$this->data['main_page'] = VIEW . 'rider-live-status';
```

**Which resolves to:**
- `VIEW` = `'view/'`
- Full path: `admin/pages/view/rider-live-status.php`
- File exists: ✅ Verified

### Step 7: Enable Error Reporting (Temporary)

In `index.php`, temporarily change:
```php
define('ENVIRONMENT', 'development');
```

This will show detailed error messages.

### Step 8: Check Server Logs

Check Apache/PHP error logs for specific error messages:
- `/var/log/apache2/error.log`
- `/var/log/php_errors.log`
- Or check hosting panel error logs

## Quick Fixes

### Fix 1: Add Explicit Route
Add to `routes.php`:
```php
$route['admin/rider-live-status'] = "admin/rider_live_status/index";
$route['admin/rider-live-status/(:any)'] = "admin/rider_live_status/$1";
```

### Fix 2: Rename Controller (Last Resort)
If nothing works, rename to match existing pattern:
- File: `Rider_live_status.php` → `Riderlivestatus.php`
- Class: `Rider_live_status` → `Riderlivestatus`
- URL: `admin/riderlivestatus`

### Fix 3: Add to Existing Riders Controller
Add method to `Riders.php`:
```php
public function live_status() {
    // Same code as Rider_live_status::index()
}
```

Then access: `admin/riders/live-status`

## Most Likely Causes

1. **Permissions Issue** (60% chance)
   - Admin user doesn't have `read` permission for `rider`
   - Check: `users_groups` table

2. **Routing Issue** (30% chance)
   - CodeIgniter not recognizing the route
   - Try with `index.php` in URL

3. **File Permissions** (10% chance)
   - Controller file not readable
   - Check file permissions

## Next Steps

1. Try test URL: `/admin/rider-live-status/test`
2. Check browser console for errors
3. Check server error logs
4. Verify admin permissions
5. Try with `index.php` in URL

---

**Report back:**
- Which URL format worked (if any)?
- What error message appears?
- Does test method work?
