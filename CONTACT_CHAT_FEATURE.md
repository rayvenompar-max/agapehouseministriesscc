# Contact Chat Feature — Admin Reply & Member Live Chat

## Overview
When a member submits a message via the Connect form, the admin can reply via a live chat interface. The member receives a **notification** and can click it to open the conversation thread in a modal.

---

## How It Works

### For Members (Logged In)

1. **Submit a Message**
   - Go to Connect page
   - No need to fill name/email (already shown from account)
   - Choose reason + write message → Submit
   - Backend stores `member_id` automatically

2. **Receive Notification**
   - When admin replies, member gets a notification: "Admin replied to your message"
   - Notification has an orange chat bubble icon
   - Click the notification bell → notification list → click "Admin replied to your message"

3. **Live Chat Modal Opens**
   - Shows original message + full reply thread
   - Admin bubbles appear on the left (with church logo + warm background)
   - Member's bubbles appear on the right (orange background)
   - Can send follow-up messages

### For Guests (Not Logged In)

1. **Submit a Message**
   - Go to Connect page
   - Fill in name + email + reason + message → Submit
   - Backend stores `member_id` as `NULL`

2. **Admin Reply (No Notification)**
   - Admin can still reply via the chat thread
   - Guest won't receive a notification (no account)
   - Reply is saved in the database

### For Admin

1. **View Messages**
   - Admin panel → **Messages** tab
   - Each card shows sender name, email, reason, message, status
   - Status colors:
     - **Orange border** = unread
     - **Green border** = replied
     - **Gray** = read

2. **Reply to Message**
   - Click **Reply / Chat** button on any card
   - Modal opens with original message + thread
   - Type reply + click **Send reply** (or Ctrl+Enter)
   - If the message is linked to a member account:
     - Status changes to "replied" (green)
     - Member gets a push notification
   - If guest submitted (no `member_id`):
     - Shows warning: "This visitor submitted without a member account — they won't receive a push notification"
     - Reply is still saved

---

## Database Schema

### `contact_messages` (updated)
- `member_id` INT UNSIGNED NULL — links to `members.id` (null for guests)
- `status` ENUM('unread','read','replied')

### `contact_chat_messages` (new table)
```sql
id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
contact_message_id INT UNSIGNED NOT NULL        -- parent message
sender_type        ENUM('member','admin')
sender_id          INT UNSIGNED NOT NULL DEFAULT 0  -- member.id or 0 for admin
body               TEXT NOT NULL
created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

### `notifications` (extended)
- `type` now includes `'contact_reply'`
- `target_type` now includes `'contact_message'`

---

## API Endpoints

| Method | Endpoint                        | Description                        | Auth       |
|--------|---------------------------------|------------------------------------|------------|
| POST   | `/api/contact`                  | Submit connect form                | None       |
| GET    | `/api/contact`                  | List all messages                  | Admin only |
| GET    | `/api/contact/{id}/thread`      | Get full chat thread               | Admin OR owner member |
| POST   | `/api/contact/{id}/reply`       | Admin sends a reply                | Admin only |
| POST   | `/api/contact/{id}/message`     | Member sends follow-up             | Member (owner) only |
| POST   | `/api/contact/{id}/read`        | Mark as read                       | Admin only |

---

## Files Changed

### Backend
- `src/Model/ContactMessage.php` — added `memberId`
- `src/Repository/ContactRepository.php` — `addChatMessage()`, `getChatMessages()`, `markReplied()`
- `src/Service/ContactService.php` — `adminReply()`, `memberReply()`, `getThread()`
- `src/Controller/ContactController.php` — new endpoints
- `api/router.php` — wired new routes

### Frontend (Member)
- `views/pages/connect.php` — conditionally hide name/email for logged-in members
- `views/layout.php` — added member live chat modal HTML
- `public/js/app.js` — notification handler + `openMemberChatModal()`, form submission updated
- `public/css/app.css` — `.member-chat-*` styles

### Admin Panel
- `admin/index.php` — Messages panel updated, admin chat modal HTML + JS

### Database
- `database/add_contact_chat.sql` — migration script (already applied)

---

## Testing

1. **As a member**:
   - Sign in → Go to Connect → Submit message (note: name/email fields are hidden)
   - Wait for admin reply
   - Check notification bell → Click "Admin replied to your message"
   - Live chat modal opens with full thread
   - Send a follow-up message

2. **As admin**:
   - Go to Admin panel → Messages tab
   - Click **Reply / Chat** on any message
   - Type reply → Send
   - Check member notification was fired (if member is linked)

3. **As a guest**:
   - (Not signed in) Go to Connect → Fill name + email + submit
   - Admin can reply via chat
   - Guest won't get notification (no account)

---

## Screenshots Reference

- Member sees: notification bell → "Admin replied to your message" → live chat modal
- Admin sees: Messages tab → card with "Reply / Chat" button → admin chat modal with bubbles
- Connect form (member): profile card shown, no name/email fields
- Connect form (guest): name + email fields required
