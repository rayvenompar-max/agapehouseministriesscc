# Quick Start: Email Notifications

Get email notifications running in 5 minutes!

## Step 1: Install PHPMailer

```bash
composer install
```

## Step 2: Get Gmail App Password

1. Visit: https://myaccount.google.com/apppasswords
2. Select "Mail" → "Other" → Name it "Digital Evangelization"
3. Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)

## Step 3: Configure Email

Edit `config/email.php`:

```php
return [
    'enabled' => true, // Change to true
    'smtp_username' => 'yourchurch@gmail.com',
    'smtp_password' => 'abcdefghijklmnop', // Your App Password (no spaces)
    'from_email' => 'yourchurch@gmail.com',
    'site_url' => 'http://localhost/DigitalEvangelization', // Update for production
];
```

## Step 4: Test It

Create any notification in your app (like, comment, follow) and check the recipient's email!

## Need Help?

See `EMAIL_SETUP.md` for detailed instructions and troubleshooting.

## Common Issues

**Emails not sending?**
- Check `'enabled' => true` in config
- Verify App Password has no spaces
- Enable 2-Factor Auth on Gmail first

**Going to spam?**
- Normal for Gmail → Gmail
- Better with custom domain email in production
