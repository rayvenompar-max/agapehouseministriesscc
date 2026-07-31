# Daybreak — Agape House Ministries

A PHP 8 MVC + SPA hybrid for the Daybreak digital evangelization platform.

## Project Structure

```
DigitalEvangelization/
│
├── index.php                  # Front controller (all web requests)
├── .htaccess                  # URL rewriting
│
├── config/
│   ├── app.php                # App constants & error settings
│   ├── database.php           # PDO singleton (getDB()) — gitignored
│   └── database.example.php  # Template — copy to database.php and fill in credentials
│
├── src/
│   ├── Model/                 # Pure data classes (no DB logic)
│   ├── Repository/            # Data access layer (SQL via PDO)
│   ├── Service/               # Business logic & orchestration
│   └── Controller/            # HTTP layer — validates input, calls Service, returns JSON
│
├── api/
│   └── router.php             # Maps HTTP routes → Controllers
│
├── views/
│   ├── layout.php             # SPA HTML shell
│   ├── login.php              # Admin + member auth page
│   ├── portal.php             # Member portal (protected)
│   ├── member_profile.php     # Public member profile page
│   ├── partials/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── member_profile_modal.php
│   └── pages/
│       ├── home.php, watch.php, read.php, prayer.php
│       ├── events.php, connect.php, about.php
│       ├── announcement.php, bible.php, quizzes.php
│
├── public/
│   ├── css/app.css            # All styles (Horizon Design System)
│   ├── js/app.js              # SPA routing + API calls
│   └── uploads/               # User-uploaded avatars and videos
│
├── admin/
│   └── index.php              # Admin panel (prayer, media, announcements, events)
│
└── database/
    ├── schema.sql             # Core tables + seed data (run first)
    ├── admins.sql             # admins table
    ├── members.sql            # members table
    ├── add_profile_picture.sql
    ├── announcements.sql
    ├── comments.sql
    ├── event_registrations.sql
    ├── member_follows.sql
    ├── notifications.sql
    ├── add_broadcast_notifications.sql
    ├── posts_shares.sql
    ├── add_posted_by.sql
    ├── add_media_member_id_v2.sql
    └── add_article_member_id.sql
```

## Setup

1. Start Apache + MySQL in XAMPP.
2. Copy `config/database.example.php` to `config/database.php` and set credentials.
3. Run migrations in order:
   ```
   mysql -u root daybreak < database/schema.sql
   mysql -u root daybreak < database/admins.sql
   mysql -u root daybreak < database/members.sql
   mysql -u root daybreak < database/add_profile_picture.sql
   mysql -u root daybreak < database/announcements.sql
   mysql -u root daybreak < database/comments.sql
   mysql -u root daybreak < database/event_registrations.sql
   mysql -u root daybreak < database/member_follows.sql
   mysql -u root daybreak < database/notifications.sql
   mysql -u root daybreak < database/add_broadcast_notifications.sql
   mysql -u root daybreak < database/posts_shares.sql
   mysql -u root daybreak < database/add_posted_by.sql
   mysql -u root daybreak < database/add_media_member_id_v2.sql
   mysql -u root daybreak < database/add_article_member_id.sql
   ```
4. Ensure `mod_rewrite` is enabled in Apache.
5. Visit: `http://localhost/DigitalEvangelization/`

Default admin: `admin` / `admin123` — **change immediately after first login**.

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/media` | All media (optional `?type=sermon\|devotional\|...`) |
| GET | `/api/media/featured` | Currently featured message |
| POST | `/api/media` | Create media (admin) |
| PATCH | `/api/media/{id}` | Update media (admin) |
| DELETE | `/api/media/{id}` | Delete media (admin) |
| GET | `/api/articles` | All articles |
| GET | `/api/articles/{id}` | Single article |
| POST | `/api/articles` | Create article (admin) |
| GET | `/api/prayers` | Approved prayer requests |
| POST | `/api/prayers` | Submit a prayer request |
| POST | `/api/prayers/{id}/pray` | Increment pray count |
| POST | `/api/prayers/{id}/approve` | Approve prayer (admin) |
| POST | `/api/prayers/{id}/reject` | Reject prayer (admin) |
| GET | `/api/events/weekly` | Recurring weekly schedule |
| GET | `/api/events/upcoming` | Upcoming one-off events |
| GET | `/api/events/all` | All events with registration counts |
| POST | `/api/events/{id}/register` | Register for event |
| DELETE | `/api/events/{id}/register` | Cancel registration |
| GET | `/api/announcements` | All announcements |
| POST | `/api/announcements` | Create announcement (admin) |
| GET | `/api/comments/{type}/{id}` | Comments for a post/article/media |
| POST | `/api/comments` | Post a comment |
| GET | `/api/notifications` | Member notifications |
| GET | `/api/notifications/unread-count` | Unread notification count |
| GET | `/api/member/profile` | Current member profile |
| PATCH | `/api/member/profile` | Update display name |
| POST | `/api/member/profile/update` | Full profile update |
| POST | `/api/member/{id}/follow` | Follow a member |
| DELETE | `/api/member/{id}/follow` | Unfollow a member |
| POST | `/api/contact` | Send a contact message |
