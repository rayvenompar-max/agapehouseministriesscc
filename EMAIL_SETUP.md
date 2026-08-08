# Email Notification Setup Guide

This guide will help you configure Gmail email notifications for your Digital Evangelization application.

## Prerequisites

- PHP 8.1 or higher
- Composer (PHP dependency manager)
- A Gmail account or Google Workspace account

## Installation Steps

### 1. Install PHPMailer via Composer

Open your terminal/command prompt in the project directory and run:

```bash
composer install
```

This will install PHPMailer and its dependencies in the `vendor/` folder.

### 2. Configure Gmail App Password

For security reasons, Gmail requires you to use an "App Password" instead of your regular password when sending emails from applications.

#### Steps to Generate Gmail App Password:

1. **Enable 2-Factor Authentication** on your Google account:
   - Go to [Google Account Security](https://myaccount.google.com/security)
   - Enable "2-Step Verification" if not already enabled

2. **Generate App Password**:
   - Visit [Google App Passwords](https://myaccount.google.com/apppasswords)
   - Select "Mail" and "Other (Custom name)"
   - Name it "Digital Evangelization" or similar
   - Click "Generate"
   - Copy the 16-character password (shown as: `xxxx xxxx xxxx xxxx`)

### 3. Configure Email Settings

1. Open the file: `config/email.php`

2. Update the following settings:

```php
return [
    // Enable email notifications
    'enabled' => true, // Change from false to true
    
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    
    // Your Gmail credentials
    'smtp_username' => 'yourchurch@gmail.com', // Your Gmail address
    'smtp_password' => 'abcd efgh ijkl mnop', // The 16-char App Password
    
    // From address
    'from_email' => 'yourchurch@gmail.com', // Same as username
    'from_name' => 'Agape House Ministries',
    
    // Site URL (update for production)
    'site_url' => 'https://yourdomain.com', // Update this!
];
```

3. Save the file.

### 4. Update Autoloader (if needed)

If your project doesn't already include Composer's autoloader, add this line at the top of your main files (like `index.php`, `api/router.php`):

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## How Email Notifications Work

Once configured, the system will automatically send email notifications to users for:

- **Post Interactions**: Likes, comments, shares on articles/media
- **Comment Activity**: Likes and replies on comments
- **Social Features**: New followers, follow-backs
- **System Announcements**: New events, announcements, gallery approvals/rejections
- **Contact Replies**: When admin responds to contact messages

### Email Features:

- ✅ HTML formatted emails with attractive design
- ✅ Plain text fallback for email clients that don't support HTML
- ✅ Direct links to view the notification in the app
- ✅ Automatic deduplication (won't spam users)
- ✅ Only sends if recipient email exists in database
- ✅ Graceful error handling (notification still saves if email fails)

## Testing the Setup

### Test Email Sending:

Create a test script: `test_email.php`

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Service\EmailService;

$emailService = new Service\EmailService();

$result = $emailService->sendNotificationEmail(
    'test@example.com',           // Your test email
    'Test User',                   // Recipient name
    'Test Notification',           // Subject
    '<h1>Hello!</h1><p>This is a test email.</p>', // HTML body
    'Hello! This is a test email.' // Text body
);

if ($result) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email. Check your config/email.php settings.";
}
```

Run the test:
```bash
php test_email.php
```

## Troubleshooting

### Common Issues:

1. **"SMTP ERROR: Failed to connect"**
   - Check your internet connection
   - Verify `smtp_host` is `smtp.gmail.com`
   - Ensure port is `587` for TLS

2. **"SMTP ERROR: Username and Password not accepted"**
   - Make sure 2-Factor Authentication is enabled
   - Regenerate your App Password
   - Copy the password without spaces: `abcdefghijklmnop`
   - Check that `smtp_username` is your full Gmail address

3. **"Email not sending but no errors"**
   - Check if `'enabled' => true` in `config/email.php`
   - Verify recipient email exists in database
   - Check PHP error log for details

4. **Emails going to spam**
   - Add a custom domain email (not Gmail) for production
   - Set up SPF and DKIM records for your domain
   - Use a verified sender domain

### Check Error Logs:

Look for errors in:
- `php_error.log` (location depends on your server config)
- Browser console for API errors
- Check `error_log()` calls in the code

## Production Recommendations

For production use:

1. **Use a dedicated email service**:
   - Gmail free accounts have sending limits (500 emails/day)
   - Consider: SendGrid, AWS SES, Mailgun, or Google Workspace

2. **Use a custom domain**:
   - `notifications@agapehouse.org` looks more professional
   - Better deliverability than Gmail addresses

3. **Set up email preferences**:
   - Allow users to opt-in/opt-out of email notifications
   - Add preference controls in user settings

4. **Monitor email delivery**:
   - Track bounce rates
   - Monitor spam complaints
   - Keep email lists clean

## Disabling Email Notifications

To disable email notifications without removing the code:

Set `'enabled' => false` in `config/email.php`:

```php
'enabled' => false,
```

Notifications will still be saved to the database but no emails will be sent.

## Security Notes

- **Never commit** `config/email.php` to version control
- Use `config/email.example.php` as a template
- App Passwords are safer than regular passwords
- Rotate App Passwords periodically
- Use environment variables for production credentials

## Support

For issues with:
- Gmail setup: [Google Support](https://support.google.com/)
- PHPMailer: [PHPMailer Docs](https://github.com/PHPMailer/PHPMailer)
- This app: Contact your development team
