# Direct Messages (Member-to-Member) Feature

## Overview
Members can now send direct messages to each other through their profile pages. This is separate from the contact chat system (member-to-admin), providing peer-to-peer communication within the community.

---

## How It Works

### For Members

1. **Starting a Conversation**
   - View another member's profile (click their name/avatar anywhere)
   - Profile modal shows a "Message" button below Follow/Unfollow
   - Click "Message" to start a direct conversation
   - System creates a conversation between the two members (or opens existing one)

2. **Sending Messages**
   - Messages appear in real-time chat interface
   - Your messages appear on the right (orange background)
   - Other member's messages appear on the left (paper background)
   - Shows member avatar, name, timestamp for each message
   - Press Ctrl+Enter or click Send button to send
   - Maximum 3000 characters per message

3. **Message Interface**
   - Clean, modern chat UI matching the app's design
   - Avatar bubbles for visual identification
   - Timestamps for context
   - Auto-scrolls to latest message
   - Messages marked as read when conversation is viewed

---

## Database Schema

### `direct_message_conversations`
Stores conversation records between two members:
- `id` - Unique conversation ID
- `member_one_id` - First member (always the lower ID for uniqueness)
- `member_two_id` - Second member (always the higher ID)
- `last_message_at` - Timestamp of most recent message
- `created_at` - When conversation was created

**Unique constraint** ensures only one conversation exists between any two members.

### `direct_messages`
Stores individual messages within conversations:
- `id` - Unique message ID
- `conversation_id` - Links to conversation
- `sender_id` - Member who sent the message
- `body` - Message text content
- `is_read` - Whether recipient has viewed the message
- `created_at` - When message was sent

---

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/messages/start/{memberId}` | Start or get conversation with a member | Member |
| GET | `/api/messages/conversations` | List all conversations for current member | Member |
| GET | `/api/messages/conversation/{id}` | Get messages in a conversation | Member (participant) |
| POST | `/api/messages/conversation/{id}` | Send a message in a conversation | Member (participant) |
| POST | `/api/messages/conversation/{id}/read` | Mark conversation as read | Member (participant) |

### Example: Start a Conversation
```javascript
POST /api/messages/start/5
Response:
{
  "status": "success",
  "data": {
    "conversation_id": 12,
    "other_member": {
      "id": 5,
      "display_name": "Carlita Ompar",
      "username": "carl",
      "profile_picture": "/public/uploads/avatars/avatar_5.jpg"
    }
  }
}
```

### Example: Send a Message
```javascript
POST /api/messages/conversation/12
Body: { "body": "Hello! How are you?" }
Response:
{
  "status": "success",
  "data": {
    "id": 45,
    "conversation_id": 12,
    "sender_id": 3,
    "body": "Hello! How are you?",
    "is_read": false,
    "created_at": "2026-08-02 14:30:00",
    "sender_name": "John Doe",
    "sender_username": "john",
    "sender_picture": null
  }
}
```

---

## Frontend Implementation

### JavaScript Functions

**`window.openMemberDirectChat(memberId, memberName)`**
- Called when clicking "Message" button on member profile
- Starts/fetches conversation with target member
- Opens direct message modal

**`openDirectMessageModal(conversationId, otherMember)`**
- Creates and displays the chat modal
- Loads conversation messages
- Handles sending and real-time updates

**`loadDirectMessages(conversationId, threadEl, otherMember)`**
- Fetches messages from API
- Renders message bubbles in thread
- Auto-scrolls to latest message

**`buildDirectMessageBubble(message, isNew)`**
- Generates HTML for a single message bubble
- Different styles for sent vs received messages
- Includes avatar, name, body, timestamp

### CSS Classes

- `.dm-message` - Container for each message
- `.dm-bubble-mine` - Messages sent by current user
- `.dm-bubble-other` - Messages from other member
- `.dm-avatar` - Member avatar circle
- `.dm-bubble` - Message bubble background
- `.dm-sender-name` - Name above message
- `.dm-body` - Message text content
- `.dm-time` - Timestamp below message

---

## Security & Privacy

✅ **Access Control**
- Only conversation participants can view messages
- Cannot start conversation with yourself
- Member must be logged in to send messages

✅ **Validation**
- Message body required (cannot be empty)
- Maximum 3000 characters per message
- Conversation ownership verified on every request

✅ **Data Protection**
- Conversations deleted when either member is deleted (CASCADE)
- Messages deleted when conversation is deleted (CASCADE)
- Personal data (emails, passwords) never exposed

---

## Files Created

### Backend
- `database/add_direct_messages.sql` - Database migration
- `src/Model/DirectMessage.php` - Message model
- `src/Model/DirectMessageConversation.php` - Conversation model
- `src/Repository/DirectMessageRepository.php` - Data access layer
- `src/Controller/DirectMessageController.php` - API endpoints

### Frontend
- Updated `views/member_profile.php` - Added "Message" button
- Updated `public/js/app.js` - Added messaging functions
- Updated `public/css/app.css` - Added messaging styles

### Configuration
- Updated `api/router.php` - Added direct message routes

---

## Installation

1. **Run Database Migration**
   ```sql
   -- Run this in phpMyAdmin or MySQL CLI
   SOURCE database/add_direct_messages.sql;
   ```

2. **Files Already Updated**
   - All backend and frontend files have been created/modified
   - No additional configuration needed

3. **Test the Feature**
   - Log in as a member
   - View another member's profile
   - Click "Message" button
   - Start chatting!

---

## Future Enhancements

🔮 **Potential Features**
- Unread message badge in header (similar to notifications)
- Message dropdown showing recent conversations
- Push notifications for new messages
- Typing indicators
- Message reactions/emojis
- File/image attachments
- Conversation search
- Delete messages
- Block/report users

---

## Differences from Contact Chat

| Feature | Contact Chat (Member ↔ Admin) | Direct Messages (Member ↔ Member) |
|---------|-------------------------------|-----------------------------------|
| Purpose | Support, prayer requests, questions | Peer communication |
| Parties | Member + Admin team | Two members |
| Access | Admin can see all | Only participants |
| Notifications | Push notifications to member | None (future enhancement) |
| Interface | Formal with reason/category | Casual chat bubbles |
| History | Tied to contact form submission | Standalone conversations |

---

## Testing Checklist

- ✅ Member can click "Message" on another member's profile
- ✅ Conversation modal opens with member info
- ✅ Can send messages back and forth
- ✅ Messages display correctly (mine vs theirs)
- ✅ Avatars and names show properly
- ✅ Timestamps are accurate
- ✅ Cannot message yourself
- ✅ Messages persist across page refresh
- ✅ Conversation marked as read when viewed
- ✅ Proper error handling (network failures, invalid IDs)
- ✅ Responsive design on mobile devices

---

## Notes

- Direct messages are **private** between two members
- Admins can access conversations via database if needed for moderation
- Consider adding reporting/blocking features for abuse prevention
- Messages have no retention policy (kept indefinitely unless deleted)
- No character limit enforcement on frontend (backend validates)

