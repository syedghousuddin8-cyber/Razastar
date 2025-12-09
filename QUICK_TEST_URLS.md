# Quick Test - Try These URLs

## Test URLs (Try in order)

1. **With dash (should work):**
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

4. **Direct method call:**
   ```
   https://admin.razastar.in/index.php/admin/riders/live_status
   ```

5. **Test if Riders controller works:**
   ```
   https://admin.razastar.in/admin/riders/manage-rider
   ```
   (This should work - if it doesn't, there's a bigger issue)

## Debug Steps

1. **First, verify Riders controller works:**
   - Try: `/admin/riders/manage-rider`
   - If this gives 404, then the issue is with the Riders controller itself
   - If this works, then the issue is specific to the `live_status` method

2. **Check browser console:**
   - Open browser developer tools (F12)
   - Check Console tab for JavaScript errors
   - Check Network tab to see what URL is actually being called

3. **Check server logs:**
   - Look for PHP errors
   - Check Apache error logs

## What to Report Back

Please tell me:
1. Does `/admin/riders/manage-rider` work? (Yes/No)
2. What exact error message do you see?
3. Which URL did you try?
4. Are you logged in as admin?
