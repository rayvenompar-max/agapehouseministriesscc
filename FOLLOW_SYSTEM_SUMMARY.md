# Follow System Summary

## Overview
Complete member-to-member follow system with real-time notifications and stats.

---

## Database Changes

### New Table: `member_follows`
```sql
CREATE TABLE member_follows (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    follower_id  INT UNSIGNED NOT NULL,
    following_id INT UNSIGNED NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_follow (follower_id, following_id),
    INDEX idx_follower  (follower_id),
    INDEX idx_following (following_id),
    CONSTRAINT fk_follow_follower  FOREIGN KEY (follower_id)  REFERENCES members(id) ON DELETE CASCADE,
    CONSTRAINT fk_follow_following FOREIGN KEY (following_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

✅ **Status:** Table created successfully in `daybreak` database

---

## Backend Changes

### MemberRepository (`src/Repository/MemberRepository.php`)
**New methods:**
- `follow($followerId, $followingId)` — follow a member
- `unfollow($followerId, $followingId)` — unfollow
- `isFollowing($followerId, $followingId)` — check if following
- `countFollowing($memberId)` — how many the member follows
- `countFollowers($memberId)` — how many follow the member

### API Routes (`api/router.php`)
**Follow endpoints:**
- `POST /api/member/{id}/follow` — follow member
- `DELETE /api/member/{id}/follow` — unfollow member
- `GET /api/member/{id}/follow` — get follow status + counts

**Profile endpoint updated:**
- `GET /api/member/profile` now includes `following_count` and `follower_count`

**Notification endpoints:**
- `POST /api/notifications/follow` — notify someone followed you
- `DELETE /api/notifications/follow` — remove follow notification

### Notification System
**NotificationController** — added `follow()` and `removeFollow()` methods  
**NotificationService** — added `removeFollow()` wrapper  
**NotificationRepository** — added `deleteFollow()` query

**Auto-detection:** Backend automatically sends `follow` or `follow_back` type based on mutual following status.

---

## Frontend Changes

### Stats Renamed (views/pages/home.php)
**Before:** Prayers / Liked (localStorage-based)  
**After:** Following / Followers (database-backed)

**Sidebar card:**
- `leftStatFollowing` — shows count of members you follow
- `leftStatFollowers` — shows count of members who follow you

**Profile drawer:**
- `pdStatFollowing` — your following count
- `pdStatFollowers` — your follower count

### Member Profile Modal (views/partials/member_profile_modal.php)
**Added:**
- Follow stats row (following / followers counts)
- Follow/Unfollow button (hidden on own profile or when not logged in)
- Button changes style when already following (outlined look)

**Behavior:**
- Click Follow → follows member, updates counts, sends notification
- Click Unfollow → unfollows member, updates counts, removes notification
- Follow status and counts fetch from API on modal open
- Sidebar stats update live after follow/unfollow

### JavaScript (public/js/app.js)

**Stats updates:**
- `updateFollowStats()` — fetches real follow counts from API
- Replaced localStorage-based `updateLikedStat()` with API call
- `loadProfileData()` reads counts from `/api/member/profile`

**Notification rendering:**
- Added `follow` and `follow_back` icon/text mappings
- Follow notifications show: **"{Name} started following you"** or **"{Name} followed you back"**
- Clicking a follow notification opens the follower's profile modal
- Icons: user-plus (teal) for follow, user-check (orange) for follow_back

**Modal behavior:**
- `openMemberProfile()` exposed globally for notification clicks
- Follow button wired with API calls + notification fire-and-forget
- Button disables during API call, re-enables after

### CSS (public/css/app.css)
**Added styles:**
- `.mpm-follow-stats` — stats row in modal
- `.mpm-follow-stat` — individual stat (following/followers)
- `.mpm-follow-btn` — follow button (solid dark background)
- `.mpm-follow-btn--following` — unfollow state (outlined, light background)

---

## User Flow

### Following Someone
1. Click a member's name/avatar in comments → modal opens
2. See their following/followers count
3. Click "Follow" button
4. **Instant feedback:**
   - Button changes to "Unfollow" with outlined style
   - Their follower count increments
   - Your sidebar "Following" count increments
5. **Notification sent to them:**
   - If they don't follow you yet: **"started following you"**
   - If they already follow you: **"followed you back"**

### Receiving a Follow Notification
1. Bell icon shows unread badge
2. Notification says: **"[Name] started following you"** or **"followed you back"**
3. Click notification → opens their profile modal
4. Can follow them back from the modal

### Unfollowing
1. Click "Unfollow" button in their profile modal
2. Button changes back to "Follow"
3. Their follower count decrements
4. Your sidebar "Following" count decrements
5. The follow notification is removed from their bell

---

## Technical Notes

- All follow relationships stored in `member_follows` table
- Unique constraint prevents duplicate follows
- Foreign keys with `ON DELETE CASCADE` clean up follows when members are deleted
- Notifications use `INSERT IGNORE` to prevent duplicate notifications
- Backend auto-detects mutual follows to send correct notification type (`follow` vs `follow_back`)
- Frontend uses fire-and-forget for notifications (doesn't block UI on failure)

---

## Testing Checklist

✅ Database table created  
✅ Follow/unfollow API works  
✅ Counts update correctly  
✅ Stats show on sidebar and profile drawer  
✅ Follow button appears in member profile modal  
✅ Button style toggles on follow/unfollow  
✅ Notifications sent on follow  
✅ Notifications removed on unfollow  
✅ Follow notifications appear in bell dropdown  
✅ Clicking notification opens follower's profile  
✅ Follow-back detection works  

---

## Files Changed

**Database:**
- `database/member_follows.sql` (new)

**PHP:**
- `src/Repository/MemberRepository.php`
- `src/Controller/NotificationController.php`
- `src/Service/NotificationService.php`
- `src/Repository/NotificationRepository.php`
- `api/router.php`

**Views:**
- `views/pages/home.php`
- `views/partials/member_profile_modal.php`

**Frontend:**
- `public/js/app.js`
- `public/css/app.css`

---

**Status: Complete and functional** ✅
