# Troubleshooting Message Button

The message button still isn't working. Let's diagnose the issue step by step.

## Step 1: Test the Button in Isolation

Visit this test page to see if the button mechanism works at all:

```
http://localhost/DigitalEvangelization/test_message_button.html
```

**What to look for:**
- The button should be visible
- When you click it, you'll see detailed logs
- This will tell us if the JavaScript itself works

## Step 2: Check Browser Console

1. Open the actual member profile page where the button doesn't work
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Look for these messages:

### Expected Console Messages:

```
=== Member Profile Script Loaded ===
BASE_URL: /DigitalEvangelization/api
Current member logged in: true
Is own profile: false
Profile data ID: 2
Message button found, attaching listener
```

### If you see:

**"Message button NOT found in DOM"**
- The button isn't being rendered
- Check if you're logged in
- Check if you're viewing someone else's profile (not your own)

**"Uncaught SyntaxError" or similar**
- There's a JavaScript error
- Take a screenshot and share it

**Nothing at all**
- The script isn't loading
- Check if there's a PHP error

## Step 3: Check if Button is Visible

Right-click on the page where the button should be, select **Inspect Element**, and search for `messageBtn` in the HTML.

### If button is NOT in the HTML:

Check the HTML comments to see why:
```html
<!-- This is your own profile -->
<!-- You are not logged in -->
```

This tells you why the button isn't showing.

### Solutions:

1. **"You are not logged in"**
   - Go to `/member/login` and log in
   - Then visit another member's profile

2. **"This is your own profile"**
   - You can't message yourself
   - Visit another member's profile URL: `/member/USERNAME`

## Step 4: Verify Database Tables

Run this to check if the database tables exist:

```
http://localhost/DigitalEvangelization/verify_dm_tables.php
```

**Expected output:**
```
✓ Table 'direct_message_conversations' exists
✓ Table 'direct_messages' exists
```

**If tables don't exist:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select your database (usually `daybreak`)
3. Go to SQL tab
4. Paste the contents of `database/add_direct_messages.sql`
5. Click **Go**

## Step 5: Check PHP Session

Add this temporary debug code at the top of `views/member_profile.php` (line 18, after the PHP tag):

```php
// TEMPORARY DEBUG - REMOVE AFTER TESTING
echo '<pre style="background:#000;color:#0f0;padding:10px;font-size:11px;">';
echo 'Is Own Profile: ' . ($isOwnProfile ? 'YES' : 'NO') . "\n";
echo 'Member Logged In: ' . ($memberAuth->isLoggedIn() ? 'YES' : 'NO') . "\n";
echo 'Current User: ';
var_dump($_SESSION['member'] ?? null);
echo 'Profile Data: ';
var_dump($profileData);
echo '</pre>';
```

This will show you session and profile information at the top of the page.

## Step 6: Test API Endpoint Directly

Use this cURL command to test if the API works:

```bash
curl -X POST http://localhost/DigitalEvangelization/api/messages/start/2 \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -c cookies.txt
```

Or use a browser extension like **Postman** or **Thunder Client**.

## Step 7: Check Apache Error Logs

Look at the PHP error log for any server-side errors:

```
xampp/apache/logs/error.log
```

Look for recent errors related to DirectMessage or member_profile.

## Common Issues & Fixes

### Issue: "Button does nothing when clicked"

**Check:**
1. Open Console (F12) - any JavaScript errors?
2. Is the button actually clickable? Try right-clicking it
3. Is there a CSS issue covering the button? (z-index problem)

**Fix:**
- The button now has `position: relative; z-index: 1;` to ensure it's clickable

### Issue: "Network error" when clicking

**Check:**
1. Is XAMPP Apache running?
2. Is MySQL running?
3. Is the URL correct in console? (Should be `/DigitalEvangelization/api/messages/start/{id}`)

**Fix:**
- Start XAMPP services
- Check BASE_URL in `index.php` matches your installation path

### Issue: "401 Unauthorized"

**Check:**
- Are you logged in as a MEMBER (not admin)?
- Has your session expired?

**Fix:**
- Log in again at `/member/login`
- Check session settings in `index.php`

### Issue: "404 Not Found"

**Check:**
- Is the route registered in `api/router.php`?
- Is the URL correct?

**Fix:**
- Search for `messages/start` in `api/router.php` - it should be there

### Issue: "500 Internal Server Error"

**Check:**
- PHP error log (see Step 7)
- Database connection
- Table existence (see Step 4)

**Fix:**
- Create missing tables
- Fix database credentials in `config/database.php`

## What to Report

If none of these steps work, please report:

1. **Console output** (screenshot or copy/paste)
2. **Test page result** (http://localhost/DigitalEvangelization/test_message_button.html)
3. **Verify tables result** (http://localhost/DigitalEvangelization/verify_dm_tables.php)
4. **Are you logged in?** (Yes/No)
5. **Whose profile are you viewing?** (Your own or someone else's)
6. **Any PHP errors** from error log

## Quick Checklist

Before reporting an issue, verify:

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] You're logged in as a member (not admin)
- [ ] You're viewing someone else's profile (not your own)
- [ ] Browser console is open (F12)
- [ ] Database tables exist (verify_dm_tables.php)
- [ ] No JavaScript errors in console
- [ ] Button is visible in HTML (Inspect Element)

---

**Need help?** Share the outputs from Steps 1-4 above.
