# Changelog

All notable changes to this project will be documented in this file.

## [2.1.0] - 2026-08-08

### Added
- **Email Notifications System**: Complete Gmail SMTP integration for all notifications
  - Sends emails for likes, comments, shares, follows, events, announcements, gallery approvals, and contact replies
  - Beautiful HTML email templates with church branding
  - Plain text fallback for all email clients
  - Direct links to view notifications in app
  - Automatic deduplication to prevent spam
  - Graceful error handling (notifications still save if email fails)
  
### New Files
- `src/Service/EmailService.php` - Email sending service with PHPMailer
- `config/email.php` - Email configuration (SMTP settings)
- `config/email.example.php` - Email configuration template
- `composer.json` - PHP dependencies (PHPMailer)
- `test_email.php` - Email testing script
- `EMAIL_SETUP.md` - Detailed email setup guide
- `QUICK_START_EMAIL.md` - 5-minute quick setup guide
- `.gitignore` - Git ignore patterns for sensitive files

### Modified
- `src/Repository/NotificationRepository.php` - Added email sending on notification creation
- `index.php` - Added Composer autoloader
- `README.md` - Added email notification documentation
- `CHANGELOG.md` - Created this changelog

### Configuration Required
- Install Composer dependencies: `composer install`
- Generate Gmail App Password at https://myaccount.google.com/apppasswords
- Configure `config/email.php` with SMTP credentials
- Set `'enabled' => true` to activate email notifications

### Technical Details
- Uses PHPMailer 6.9+ for reliable email delivery
- Supports Gmail SMTP (TLS on port 587)
- Auto-detects recipient email from members table
- Builds notification-specific email content
- Logs errors without failing notification creation
- Can be easily disabled via config

---

## [2.0.0] - Previous Release

### Features
- Gallery multi-image upload (up to 10 images per post)
- Direct messaging between members
- Contact chat system (member-admin communication)
- Content approval workflow
- Broadcast notifications
- Member profiles with follow system
- Prayer requests
- Events calendar
- Bible integration
- PWA support

