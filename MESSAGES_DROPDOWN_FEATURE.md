# Messages Dropdown Feature

## Overview
Added a Messages icon (chat bubble) in the header that shows a dropdown list of the member's contact message threads. The icon appears to the left of the notification bell for logged-in members.

## Implementation Complete ✅

### Backend

**1. ContactRepository.php** - Added `findThreadsByMember()` method
- Returns all contact threads for a specific member
- Includes count of unread admin replies (replies since member's last message)
- Orders threads by most recent activity
- Fields: id, reason, message, created_at, status, unread_admin_replies, last_activity

**2. ContactService.php** - Added `getMemberThreads()` method
- Calls repository method to fetch member's threads
- Returns array of thread data

**3. ContactController.php** - Added `getThreads()` method
- Route: `GET /api/contact/threads`
- Requires member login
- Returns JSON response with member's threads

**4. router.php** - Added route
```php
} elseif ($method === 'GET' && matchRoute('/contact/threads', $path)) {
    $contactCtrl->getThreads();
```

### Frontend

**1. header.php** - HTML structure already added
- Messages icon with dropdown panel
- Positioned with `margin-right: 8px` to create spacing from notification bell
- Structure includes: msg-bell-wrap, msg-bell-btn, msg-dropdown, msg-badge, msg-list

**2. app.css** - Styles already added (lines ~4510-4640)
- Cloned from notification bell styles
- Classes: `.msg-bell-*`, `.msg-item-*`, `.msg-badge`, etc.
- Includes animations, hover states, unread indicators

**3. app.js** - Added `initMessagesDropdown()` IIFE
- **Dropdown toggle**: Opens/closes on click, closes on outside click or Escape
- **Fetch threads**: `GET /api/contact/threads` returns member's threads
- **Render threads**: Shows reason, message preview, timestamp, icon per reason type
- **Unread badge**: Shows count of threads with unread admin replies
- **Click handler**: Opens chat modal via `openMemberChatModal(threadId)`
- **Polling**: Refreshes every 30 seconds to check for new messages
- **Badge animations**: Pop and ring animations when count increases
- **Global refresh**: `window._refreshMessagesBadge()` can be called after sending messages

## Features

✅ **Messages icon** appears left of notification bell for logged-in members  
✅ **Badge counter** shows total unread admin replies across all threads  
✅ **Dropdown list** shows all member's contact threads, newest first  
✅ **Thread items** display: reason (with icon), message preview, timestamp, unread dot  
✅ **Click thread** opens live chat modal with full conversation  
✅ **Auto-refresh** polls for new messages every 30 seconds  
✅ **Animations** badge pop and icon ring when new replies arrive  
✅ **Empty state** shows friendly message when no threads exist  

## Reason Icons

Each thread item displays an icon based on the reason:
- **Just saying hi**: User icon
- **Prayer request**: Hands icon
- **Questions about faith**: Question circle icon
- **Volunteering**: Heart icon
- **Technical issue**: Alert circle icon

## User Flow

1. Member submits a message via Connect form
2. Admin replies in admin panel → notification sent to member
3. Member sees:
   - Notification bell badge increments (notification for admin reply)
   - Messages icon badge increments (unread admin reply count)
4. Member can:
   - Click notification → opens chat modal
   - OR click messages icon → see all threads → click specific thread → opens chat modal
5. Messages badge auto-refreshes every 30 seconds
6. After member reads/replies, unread count decreases on next refresh

## Testing

To test the complete flow:

1. **As Member**: Submit a message via Connect page
2. **As Admin**: Go to admin panel → Messages tab → click "Reply / Chat" → send a reply
3. **As Member**: 
   - Notification bell should show a badge
   - Messages icon should show a badge
   - Click messages icon → should see your thread with an unread dot
   - Click the thread → chat modal opens
   - Send a reply in the chat
4. **Verify**: Messages badge should clear after the member views/replies
5. **Test polling**: Wait 30 seconds → badge should update automatically

## Files Modified

- `src/Repository/ContactRepository.php` - Added `findThreadsByMember()` method
- `src/Service/ContactService.php` - Added `getMemberThreads()` method
- `src/Controller/ContactController.php` - Added `getThreads()` method
- `api/router.php` - Added `/contact/threads` route
- `public/js/app.js` - Added `initMessagesDropdown()` IIFE (after line 5501)
- `views/partials/header.php` - Already had HTML structure
- `public/css/app.css` - Already had CSS styles

## API Endpoints

### GET /api/contact/threads
**Auth**: Member login required  
**Returns**:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "reason": "Prayer request",
      "message": "Please pray for...",
      "created_at": "2026-08-01 10:00:00",
      "status": "replied",
      "unread_admin_replies": 2,
      "last_activity": "2026-08-02 14:30:00"
    }
  ]
}
```

## Notes

- Messages dropdown is only visible when member is logged in (same as notification bell)
- Unread count represents admin replies the member hasn't seen yet (since their last message)
- The dropdown automatically closes when clicking a thread to open the chat modal
- Badge counter maxes out at "99+" for large numbers
- Polling interval: 30 seconds (same as notifications)
- Empty state shows inbox icon + "No messages yet" text
