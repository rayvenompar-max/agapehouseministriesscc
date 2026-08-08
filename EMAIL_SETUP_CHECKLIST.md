# Email Setup Checklist ✅

Follow this checklist to set up email notifications in 5 minutes!

## Prerequisites
- [ ] XAMPP/Apache is running
- [ ] MySQL database is configured
- [ ] Composer is installed on your system
- [ ] You have a Gmail account

---

## Setup Steps

### 1. Install Dependencies
- [ ] Open terminal/command prompt
- [ ] Navigate to project: `cd d:\xampp\htdocs\DigitalEvangelization`
- [ ] Run: `composer install`
- [ ] Verify `vendor/` folder was created
- [ ] Verify `vendor/phpmailer/` exists

### 2. Get Gmail App Password
- [ ] Visit https://myaccount.google.com/apppasswords
- [ ] Log in to your Gmail account
- [ ] Enable 2-Factor Authentication (if not already enabled)
- [ ] Click "Generate" under App Passwords
- [ ] Select "Mail" as the app
- [ ] Select "Other (Custom name)" as the device
- [ ] Name it: "Digital Evangelization"
- [ ] Click "Generate"
- [ ] Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)
- [ ] Save it somewhere safe (you'll need it in step 3)

### 3. Configure Email Settings
- [ ] Open file: `config/email.php` in your editor
- [ ] Set `'enabled' => true` (line 6)
- [ ] Set `'smtp_username'` to your Gmail address (line 13)
- [ ] Set `'smtp_password'` to your App Password from step 2 (line 14) - remove spaces!
- [ ] Set `'from_email'` to your Gmail address (line 17)
- [ ] Update `'site_url'` if needed (line 21)
- [ ] Save the file

**Example Configuration:**
```php
return [
    'enabled' => true, // Changed from false to true
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'yourchurch@gmail.com', // Your Gmail
    'smtp_password' => 'abcdefghijklmnop', // App Password (no spaces!)
    'from_email' => 'yourchurch@gmail.com',
    'from_name' => 'Agape House Ministries',
    'site_url' => 'http://localhost/DigitalEvangelization',
];
```

### 4. Test Email Setup
- [ ] Open file: `test_email.php` in your editor
- [ ] Change line 31: `$testEmail = 'your-email@example.com';` to your actual email
- [ ] Save the file
- [ ] Run in terminal: `php test_email.php`
- [ ] Check output for "✅ SUCCESS! Email sent successfully."
- [ ] Check your email inbox (may take a few seconds)
- [ ] Verify you received the test email

### 5. Verify in Application
- [ ] Open the app in browser: `http://localhost/DigitalEvangelization/`
- [ ] Log in as a member (or create two test accounts)
- [ ] Perform an action (like a post, comment, follow someone)
- [ ] Check the recipient's email
- [ ] Verify notification email was received
- [ ] Click the "View Now" button in email
- [ ] Verify it links to the correct page

---

## Troubleshooting

### ❌ "Composer dependencies not installed"
**Solution**: Run `composer install` in the project directory

### ❌ "SMTP ERROR: Username and Password not accepted"
**Solutions**:
- Verify 2-Factor Auth is enabled on Google account
- Make sure you used the App Password (not your regular password)
- Remove all spaces from the App Password
- Check that `smtp_username` is your full Gmail address

### ❌ "SMTP ERROR: Failed to connect"
**Solutions**:
- Check your internet connection
- Verify `smtp_host` is `smtp.gmail.com`
- Verify `smtp_port` is `587`

### ❌ Test script runs but no email received
**Solutions**:
- Check spam/junk folder
- Verify `'enabled' => true` in config
- Check the email address is correct
- Look at PHP error log for details

### ❌ Emails going to spam
**Expected**: Gmail → Gmail emails often go to spam initially
**Solutions**:
- Mark as "Not Spam" a few times
- Use a custom domain email for production (more professional)
- Set up SPF and DKIM records for your domain

---

## Success Criteria

You're all set when:
- ✅ `composer install` completed successfully
- ✅ `vendor/phpmailer/` folder exists
- ✅ `config/email.php` has `'enabled' => true`
- ✅ `config/email.php` has your Gmail credentials
- ✅ `php test_email.php` shows success message
- ✅ Test email received in your inbox
- ✅ In-app notifications trigger emails
- ✅ Email links work correctly

---

## Quick Reference

### File Locations
- **Config**: `config/email.php`
- **Test Script**: `test_email.php`
- **Email Service**: `src/Service/EmailService.php`
- **Notification Repo**: `src/Repository/NotificationRepository.php`

### Important Links
- Gmail App Passwords: https://myaccount.google.com/apppasswords
- Google 2FA Setup: https://myaccount.google.com/security
- PHPMailer Docs: https://github.com/PHPMailer/PHPMailer

### Commands
```bash
# Install dependencies
composer install

# Test email
php test_email.php

# Check vendor folder
dir vendor\phpmailer
```

---

## Need More Help?

📖 **Detailed Guide**: See `EMAIL_SETUP.md`  
📋 **Summary**: See `EMAIL_NOTIFICATION_SUMMARY.md`  
🚀 **Quick Start**: See `QUICK_START_EMAIL.md`  

---

**Last Updated**: August 8, 2026  
**Version**: 2.1.0
