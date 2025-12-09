# Routing Debug Guide

## Current Setup

### Controller File
- **File:** `application/controllers/admin/Rider_live_status.php`
- **Class:** `Rider_live_status`
- **Method:** `index()`

### View File  
- **File:** `application/views/admin/pages/view/rider-live-status.php`
- **Path in controller:** `VIEW . 'rider-live-status'` = `view/rider-live-status`

### Routes
- **translate_uri_dashes:** TRUE
- **No custom route** (using default CodeIgniter routing)

## How CodeIgniter Routing Works

With `translate_uri_dashes = TRUE`:
- URL: `admin/rider-live-status` 
- Converts to: `admin/rider_live_status`
- Looks for: `application/controllers/admin/Rider_live_status.php`
- Calls: `index()` method

## Test URLs

Try these URLs in order:

1. **Standard URL (should work):**
   ```
   https://admin.razastar.in/admin/rider-live-status
   ```

2. **With index.php:**
   ```
   https://admin.razastar.in/index.php/admin/rider-live-status
   ```

3. **Direct controller:**
   ```
   https://admin.razastar.in/index.php/admin/Rider_live_status
   ```

4. **Lowercase:**
   ```
   https://admin.razastar.in/admin/rider_live_status
   ```

## Common Issues

### Issue 1: Case Sensitivity
- Controller file: `Rider_live_status.php` ✅
- Class name: `Rider_live_status` ✅  
- URL: `rider-live-status` (lowercase) ✅

### Issue 2: Permissions
Check if admin user has `read` permission for `rider` module.

### Issue 3: View Path
- Controller uses: `VIEW . 'rider-live-status'`
- Should resolve to: `admin/pages/view/rider-live-status.php`
- File exists: ✅

### Issue 4: .htaccess
Check if `.htaccess` is working:
- Try: `https://admin.razastar.in/index.php/admin/rider-live-status`
- If this works but without index.php doesn't, then .htaccess issue

## Quick Fix Test

Create a simple test method in controller:

```php
public function test() {
    echo "Controller is working!";
    exit;
}
```

Then access: `https://admin.razastar.in/admin/rider-live-status/test`

If this works, then routing is fine and issue is in `index()` method.
