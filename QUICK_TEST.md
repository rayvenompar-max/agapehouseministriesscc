# Quick Message Button Test

## 1. Verify Database Tables

Visit: `http://localhost/DigitalEvangelization/verify_dm_tables.php`

Should say: ✓ Both tables exist

## 2. Test the Button

1. Make sure you're logged in at: `/member/login`
2. Visit another member's profile: `/member/carl` (or any username)
3. Open console (F12)
4. Click "Message" button
5. Watch the console logs
6. Page should redirect to home
7. Modal should appear after 800ms

## 3. What You Should See

**In Console:**
- ✓ Button clicked
- ✓ API call success (status 200)
- ✓ Redirect message
- ✓ Auto-open triggered
- ✓ Modal opened

**On Screen:**
- Button shows spinner briefly
- Redirects to home page
- Modal slides up with conversation
- Can type and send messages

## 4. If It Doesn't Work

Check console and tell me which message you DON'T see:

- [ ] "Button clicked via onclick!"
- [ ] "Response status: 200"
- [ ] "Storing in sessionStorage"
- [ ] "[DM Auto-open] Opening modal..."
- [ ] "[openDirectMessageModal] Modal created: Success"

This will help identify where the problem is.

## Quick Fix Checklist

- [ ] XAMPP Apache running
- [ ] XAMPP MySQL running  
- [ ] Logged in as member (not admin)
- [ ] Viewing someone else's profile (not your own)
- [ ] Database tables exist
- [ ] Console shows no errors
- [ ] sessionStorage works (not in private mode)

---

**Need more details?** See `MESSAGE_BUTTON_DEBUG_GUIDE.md`
