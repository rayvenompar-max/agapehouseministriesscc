# Message Button Debug Guide

The message button is now instrumented with comprehensive logging to help diagnose why the modal isn't appearing.

## How It Should Work

1. Click "Message" button on member profile
2. API call creates/retrieves conversation
3. Conversation data saved to sessionStorage
4. Page redirects to `/#home`
5. Main app detects sessionStorage data
6. Direct message modal opens automatically

## Testing Steps

### Step 1: Open Console
Press **F12** and go to **Console** tab. Keep it open during testing.

### Step 2: Visit a Member Profile
Go to `/member/{username}` (not your own profile)

### Step 3: Check Initial Logs
You should see:
```
=== Member Profile Script Loaded ===
BASE_URL: /DigitalEvangelization/api
Current member logged in: true
Is own profile: false
Profile data ID: 2
SessionStorage test: Available
Message button found, attaching listener
```

### Step 4: Click Message Button
You should see:
```
Button clicked via onclick!
Message button clicked for member: 2, Test User
Fetching: /DigitalEvangelization/api/messages/start/2
Response status: 200
Response data: {status: 'success', data: {...}}
✓ Conversation created successfully
Conversation ID: 1
Other member: {id: 2, display_name: '...', ...}
Storing in sessionStorage: {...}
Redirecting to: /DigitalEvangelization/#home
```

### Step 5: After Redirect
After the page redirects to `/#home`, you should see:
```
[DM Auto-open] Initializing...
[DM Auto-open] DOM loaded, waiting 800ms for app init...
[DM Auto-open] Checking sessionStorage...
[DM Auto-open] pendingMessage: {"conversationId":1,"otherMember":{...}}
[DM Auto-open] Parsed data: {conversationId: 1, otherMember: {...}}
[DM Auto-open] Removed from sessionStorage
[DM Auto-open] Opening modal...
[openDirectMessageModal] Called with: {conversationId: 1, otherMember: {...}}
[openDirectMessageModal] Existing modal: Not found
[openDirectMessageModal] Creating modal...
[openDirectMessageModal] Modal created: Success
```

Then the modal should appear!

## Common Issues

### Issue 1: "No pending message found"
**Symptom:** After redirect, console shows:
```
[DM Auto-open] No pending message found
```

**Cause:** sessionStorage was not set or was cleared

**Fix:** 
- Check if Step 4 logs showed "Storing in sessionStorage"
- Try again - click message button and watch carefully
- Check if browser is blocking sessionStorage (privacy mode)

### Issue 2: Modal Not Created
**Symptom:** Console shows:
```
[openDirectMessageModal] Modal created: Failed
```

**Cause:** `createDirectMessageModal()` function is failing

**Debug:**
1. Check for JavaScript errors in console
2. Look for "Uncaught" or "TypeError" messages
3. The modal might already exist with a different ID

### Issue 3: API Call Fails
**Symptom:** Console shows error instead of success

**Possible errors and fixes:**

**"Response status: 401"**
- You're not logged in
- Session expired
- **Fix:** Log in again at `/member/login`

**"Response status: 404"**
- API endpoint not found
- **Fix:** Check `api/router.php` has the `/messages/start/{memberId}` route

**"Response status: 500"**
- Server error
- **Fix:** Check `xampp/apache/logs/error.log` for PHP errors

**"Network error"**
- XAMPP not running
- Wrong URL
- **Fix:** Ensure XAMPP Apache and MySQL are running

### Issue 4: Redirect Not Happening
**Symptom:** Button click doesn't redirect

**Check:**
- Is there a JavaScript error before the redirect?
- Is the `window.location.href` line being reached?
- Check console for "Redirecting to:" message

### Issue 5: Database Tables Don't Exist
**Test:** Visit `http://localhost/DigitalEvangelization/verify_dm_tables.php`

**If tables missing:**
1. Open phpMyAdmin
2. Select your database
3. Go to SQL tab
4. Run: `database/add_direct_messages.sql`

## Manual Test

If auto-open isn't working, you can manually test the modal:

### In Browser Console (on home page):
```javascript
// Test if modal creation works
createDirectMessageModal();
console.log('Modal exists:', document.getElementById('directMessageModal') !== null);

// Test opening with dummy data
openDirectMessageModal(1, {
  id: 2,
  display_name: 'Test User',
  username: 'testuser',
  profile_picture: null
});
```

This should manually open the modal. If it works, the issue is with the auto-open timing or sessionStorage.

## SessionStorage Test

### In Browser Console (on profile page):
```javascript
// Test sessionStorage
sessionStorage.setItem('test', 'hello');
console.log('Can write:', sessionStorage.getItem('test') === 'hello');
sessionStorage.removeItem('test');

// Test the actual data structure
const testData = {
  conversationId: 999,
  otherMember: {id: 2, display_name: 'Test', username: 'test'}
};
sessionStorage.setItem('openDirectMessage', JSON.stringify(testData));
console.log('Stored:', sessionStorage.getItem('openDirectMessage'));

// Now navigate to home
window.location.href = '/DigitalEvangelization/#home';
```

The modal should open when you arrive at home page.

## What to Report

If it still doesn't work, please provide:

1. **All console output** from Step 1-5 (copy/paste or screenshot)
2. **Network tab** - Check if API call is successful (F12 > Network > filter by "messages")
3. **Application tab** - Check sessionStorage (F12 > Application > Storage > Session Storage)
4. **Any error messages** in console (red text)
5. **verify_dm_tables.php output** to confirm tables exist

## Expected Behavior Video

1. Click Message button
2. Button shows "Opening..." with spinner (2 seconds)
3. Page redirects to home
4. After 800ms, modal slides up from bottom
5. Modal shows conversation with the member
6. You can type and send messages

---

**Note:** All the diagnostic logging will be removed once the issue is resolved. These logs are temporary to help identify the problem.
