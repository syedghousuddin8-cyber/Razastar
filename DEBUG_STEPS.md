# Debug Steps for 404 Error

## Step 1: Test Basic Routing

Try this test URL first:
```
https://admin.razastar.in/admin/riders/test-live-status
```

**Expected:** Should show "Riders Controller - live_status method is accessible!"

**If this works:** Routing is fine, issue is in `live_status()` method
**If this fails:** Continue to Step 2

## Step 2: Verify Riders Controller Works

Try this existing URL:
```
https://admin.razastar.in/admin/riders/manage-rider
```

**If this works:** Riders controller is accessible
**If this fails:** There's a bigger issue with the Riders controller

## Step 3: Try Different URL Formats

Try these URLs one by one:

1. **With dash:**
   ```
   https://admin.razastar.in/admin/riders/live-status
   ```

2. **With underscore:**
   ```
   https://admin.razastar.in/admin/riders/live_status
   ```

3. **With index.php:**
   ```
   https://admin.razastar.in/index.php/admin/riders/live-status
   ```

4. **Direct:**
   ```
   https://admin.razastar.in/index.php/admin/riders/live_status
   ```

## Step 4: Check Permissions

The controller checks:
```php
if (!has_permissions('read', 'rider')) {
    redirect('admin/home', 'refresh');
}
```

**Make sure:**
- You are logged in as admin
- Admin user has `read` permission for `rider` module
- Check in database: `users_groups` table where `group_id = 1` (admin group)

## Step 5: Check Browser Console

1. Open browser developer tools (F12)
2. Go to Console tab
3. Look for any JavaScript errors
4. Go to Network tab
5. Try accessing the URL again
6. See what HTTP status code you get (404, 403, 500, etc.)

## Step 6: Enable Error Reporting

Temporarily in `index.php`:
```php
define('ENVIRONMENT', 'development');
```

This will show detailed error messages.

## Most Common Issues

1. **Permission Issue (Most Likely)**
   - Admin user doesn't have `read` permission for `rider`
   - Solution: Check `users_groups` and `permissions` tables

2. **Not Logged In**
   - Must be logged in as admin
   - Solution: Login first

3. **.htaccess Issue**
   - URL rewriting not working
   - Solution: Try with `index.php` in URL

4. **Cache Issue**
   - Browser or server cache
   - Solution: Clear browser cache, restart server

## Quick Fix - Direct Access

If nothing works, try accessing directly:
```
https://admin.razastar.in/index.php/admin/riders/live_status
```

---

**Please report:**
1. Does `/admin/riders/manage-rider` work?
2. Does `/admin/riders/test-live-status` work?
3. What exact error do you see?
4. Are you logged in as admin?
