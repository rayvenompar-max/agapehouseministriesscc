# Digital Evangelization Platform

A comprehensive church community platform built with PHP, featuring member management, content sharing, event coordination, and real-time messaging.

## Features

### 📱 Core Functionality
- **Progressive Web App (PWA)** - Install on mobile devices
- **Member Authentication** - Secure login and profile management
- **Admin Panel** - Content moderation and community management
- **Responsive Design** - Works on all devices

### 🎥 Media & Content
- **Watch** - Sermons, devotionals, testimonies, worship videos
- **Articles** - Faith-based articles with reading time estimates
- **Gallery** - Photo sharing with approval workflow
  - Multi-image upload support (up to 10 images per post)
  - Smart collage layouts
  - Edit and delete your own posts

### 🙏 Community Features
- **Prayer Requests** - Submit and pray for community requests
- **Events** - Calendar with livestream support
- **Announcements** - Church-wide updates
- **Member Profiles** - Follow members, view activity

### 💬 Communication
- **Direct Messages** - Private member-to-member messaging
- **Contact Chat** - Live chat between members and admin team
- **Messages Dropdown** - Quick access to conversations
- **Notifications** - Real-time updates with badge counters
- **Email Notifications** - Gmail notifications for all activities

### 📖 Bible Resources
- **Daily Verse** - Verse of the day with reflection
- **ESV Bible Integration** - Full Bible text with search

## Tech Stack

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL/MariaDB** - Database
- **MVC Architecture** - Clean separation of concerns
  - Models: Data structures
  - Repositories: Database access
  - Services: Business logic
  - Controllers: API endpoints

### Frontend
- **Vanilla JavaScript** - No framework dependencies
- **CSS3** - Custom design system ("Horizon Design")
- **Lucide Icons** - Beautiful icon set
- **Service Worker** - Offline support and caching

### Design System
**Color Palette (Warm)**
- Plum: `#20142A`, `#332039`
- Ember: `#C1542E`
- Coral: `#E08152`
- Gold: `#D9A544`
- Cream: `#FBF6EC`

**Typography**
- Display: Fraunces (headlines)
- Body: Work Sans
- Mono: IBM Plex Mono (metadata)

## Installation

### Prerequisites
- XAMPP (Apache + MySQL) or similar stack
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer (for email notifications)
- Gmail account (for email notifications)

### Setup Steps

1. **Clone/Copy Project**
   ```bash
   cd d:\xampp\htdocs
   # Copy DigitalEvangelization folder here
   ```

2. **Database Setup**
   ```bash
   # Create database
   mysql -u root -e "CREATE DATABASE daybreak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Import main schema
   mysql -u root daybreak < database/schema.sql
   
   # Run migrations (in order)
   mysql -u root daybreak < database/members.sql
   mysql -u root daybreak < database/admins.sql
   mysql -u root daybreak < database/announcements.sql
   mysql -u root daybreak < database/comments.sql
   mysql -u root daybreak < database/notifications.sql
   mysql -u root daybreak < database/member_follows.sql
   mysql -u root daybreak < database/posts_shares.sql
   mysql -u root daybreak < database/post_likes.sql
   mysql -u root daybreak < database/event_registrations.sql
   mysql -u root daybreak < database/add_profile_picture.sql
   mysql -u root daybreak < database/add_posted_by.sql
   mysql -u root daybreak < database/add_content_approval.sql
   mysql -u root daybreak < database/add_gallery.sql
   mysql -u root daybreak < database/add_gallery_multi_images.sql
   mysql -u root daybreak < database/add_direct_messages.sql
   mysql -u root daybreak < database/add_contact_chat_safe.sql
   mysql -u root daybreak < database/add_broadcast_notifications.sql
   mysql -u root daybreak < database/add_media_member_id_v2.sql
   mysql -u root daybreak < database/add_article_member_id.sql
   mysql -u root daybreak < database/add_announcement_member_id.sql
   ```

3. **Configure Database**
   - Copy `config/database.example.php` to `config/database.php`
   - Update credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'daybreak');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Install Dependencies (for email notifications)**
   ```bash
   cd d:\xampp\htdocs\DigitalEvangelization
   composer install
   ```

5. **Configure Email Notifications (Optional but Recommended)**
   - See **[QUICK_START_EMAIL.md](QUICK_START_EMAIL.md)** for 5-minute setup
   - Or see **[EMAIL_SETUP.md](EMAIL_SETUP.md)** for detailed guide
   - Copy `config/email.example.php` to `config/email.php`
   - Get Gmail App Password and configure settings
   - Test with: `php test_email.php`

6. **Create Upload Directories**
   ```bash
   mkdir public\uploads\gallery
   mkdir public\uploads\avatars
   mkdir public\uploads\videos
   ```

7. **Start XAMPP**
   - Start Apache and MySQL
   - Visit: `http://localhost/DigitalEvangelization/`

## Email Notifications

The platform sends email notifications through Gmail SMTP for all in-app notifications:

### Notification Types
- **Post Interactions**: Likes, comments, shares
- **Comment Activity**: Comment likes and replies
- **Social Actions**: New followers, follow-backs
- **System Announcements**: New events, announcements
- **Gallery Updates**: Photo approvals/rejections
- **Contact Replies**: Admin responses to messages

### Quick Setup
1. **Install dependencies**: `composer install`
2. **Get Gmail App Password**: Visit [Google App Passwords](https://myaccount.google.com/apppasswords)
3. **Configure**: Edit `config/email.php` with your credentials
4. **Test**: Run `php test_email.php`

📖 **Full Guide**: See [EMAIL_SETUP.md](EMAIL_SETUP.md) for detailed instructions

### Features
- ✅ Beautiful HTML emails with church branding
- ✅ Direct links to view notifications
- ✅ Automatic deduplication (no spam)
- ✅ Plain text fallback for all email clients
- ✅ Graceful error handling (notifications still save if email fails)
- ✅ Easy to disable (set `enabled => false` in config)

## Project Structure

```
DigitalEvangelization/
├── admin/                    # Admin panel
│   └── index.php            # Admin dashboard
├── api/                     # API layer
│   └── router.php          # Route definitions
├── config/                  # Configuration
│   ├── app.php             # App constants
│   ├── database.php        # DB credentials
│   └── email.php           # Email/SMTP settings
├── database/               # SQL migrations
│   ├── schema.sql         # Main schema
│   └── add_*.sql          # Feature migrations
├── public/                 # Public assets
│   ├── css/
│   │   └── app.css        # Main stylesheet
│   ├── js/
│   │   └── app.js         # Main JavaScript
│   ├── images/            # Static images
│   └── uploads/           # User uploads
├── src/                    # Backend code
│   ├── Controller/        # API controllers
│   ├── Model/             # Data models
│   ├── Repository/        # Database access
│   └── Service/           # Business logic (includes EmailService)
├── views/                  # Frontend views
│   ├── pages/             # Page templates
│   ├── partials/          # Reusable components
│   └── layout.php         # Main layout
├── index.php              # Entry point
├── manifest.json          # PWA manifest
├── service-worker.js      # Service worker
├── composer.json          # PHP dependencies
├── test_email.php         # Email testing script
├── QUICK_START_EMAIL.md   # Quick email setup guide
└── EMAIL_SETUP.md         # Detailed email setup guide
```

## API Endpoints

### Authentication
- `POST /api/member/register` - Create account
- `POST /api/member/login` - Sign in
- `POST /api/member/logout` - Sign out

### Content
- `GET /api/media` - List videos
- `GET /api/articles` - List articles
- `GET /api/prayer` - List prayers
- `POST /api/prayer` - Submit prayer
- `GET /api/events` - List events
- `GET /api/announcements` - List announcements

### Gallery
- `GET /api/gallery` - Get approved photos
- `POST /api/gallery` - Submit photo
- `POST /api/gallery/upload` - Upload image
- `POST /api/gallery/{id}/approve` - Approve (admin)
- `DELETE /api/gallery/{id}` - Delete photo

### Messaging
- `POST /api/messages/start/{memberId}` - Start conversation
- `GET /api/messages/conversations` - List conversations
- `GET /api/messages/conversation/{id}` - Get messages
- `POST /api/messages/conversation/{id}` - Send message
- `POST /api/messages/conversation/{id}/read` - Mark as read

### Contact
- `POST /api/contact` - Submit contact form
- `GET /api/contact` - List messages (admin)
- `GET /api/contact/{id}/thread` - Get chat thread
- `POST /api/contact/{id}/reply` - Admin reply
- `POST /api/contact/{id}/message` - Member follow-up
- `GET /api/contact/threads` - Member's threads

### Notifications
- `GET /api/notifications` - List notifications
- `POST /api/notifications/{id}/read` - Mark as read
- `POST /api/notifications/read-all` - Mark all as read

## Feature Documentation

### Gallery Multi-Image Upload
- Upload up to 10 images in one post
- Smart collage layouts (1-4+ images)
- Click any post to view full gallery
- Edit/delete your own posts (3-dot menu)

### Direct Messages
- Private member-to-member chat
- Message button on member profiles
- Real-time conversation interface
- Read tracking

### Contact Chat
- Members can message admin team
- Live chat interface with threaded replies
- Admin reply notifications
- Messages dropdown for quick access

### Content Approval Workflow
- Gallery photos require approval
- Prayer requests moderated
- Status tracking (pending/approved/rejected)
- Notification on approval/rejection

## Development Guidelines

### Adding a New Feature

1. **Database** - Create migration in `database/`
2. **Model** - Add model class in `src/Model/`
3. **Repository** - Add data access in `src/Repository/`
4. **Service** - Add business logic in `src/Service/`
5. **Controller** - Add API endpoints in `src/Controller/`
6. **Routes** - Register routes in `api/router.php`
7. **Frontend** - Add page in `views/pages/`
8. **JavaScript** - Add logic in `public/js/app.js`
9. **Styling** - Add CSS in `public/css/app.css`

### Code Style

**PHP**
- PSR-12 coding standard
- Type hints where possible
- Prepared statements for SQL
- Consistent error handling

**JavaScript**
- ES6+ syntax
- Async/await for async operations
- Consistent naming (camelCase)
- Comments for complex logic

**SQL**
- Uppercase keywords
- Snake_case for table/column names
- Foreign key constraints
- Proper indexes

## Security Features

✅ **Authentication** - Session-based with secure cookies  
✅ **Authorization** - Role-based access control (member/admin)  
✅ **Input Validation** - Server-side validation for all inputs  
✅ **SQL Injection Protection** - Prepared statements  
✅ **XSS Prevention** - HTML escaping in templates  
✅ **File Upload Security** - MIME type validation, size limits  
✅ **CSRF Protection** - Session validation  
✅ **Password Hashing** - bcrypt for password storage  

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Android)

## Troubleshooting

### Database Connection Failed
- Check XAMPP MySQL is running
- Verify credentials in `config/database.php`
- Ensure database "daybreak" exists

### Images Not Uploading
- Check `public/uploads/` exists and is writable
- Verify PHP `upload_max_filesize` and `post_max_size`
- Check file permissions (755 for directories, 644 for files)

### 404 Errors on API Calls
- Verify `.htaccess` is present
- Check Apache `mod_rewrite` is enabled
- Ensure `AllowOverride All` in Apache config

### Styles Not Loading
- Hard refresh browser (Ctrl+F5)
- Check `public/css/app.css` exists
- Verify file permissions

### Email Notifications Not Sending
- Run `php test_email.php` to diagnose
- Check `'enabled' => true` in `config/email.php`
- Verify Gmail App Password (no spaces, 16 characters)
- Enable 2-Factor Auth on Google account first
- See [EMAIL_SETUP.md](EMAIL_SETUP.md) for troubleshooting

## License

Proprietary - All rights reserved

## Support

For issues or questions, contact the development team.

---

**Version:** 2.0  
**Last Updated:** August 2026
