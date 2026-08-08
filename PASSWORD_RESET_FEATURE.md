# Password Reset Feature

## Overview
When a user forgets their password and submits a password reset request through the Connect/Contact page, the administrator can reset their password through the admin panel. The user is automatically notified via email with their new temporary password.

## How It Works

### For Users (Members)

1. **Forgot Password Request**
   - User goes to the Connect/Messages page
   - Selects "Technical Issue" as the subject
   - Writes a password reset request
   - Submits the message

2. **Receiving New Password**
   - After admin resets the password, the user receives an **automated email** containing:
     - Their new temporary password
     - A direct link to log in
     - Security recommendations
     - Instructions to change the password after logging in

3. **Email Notification Details**
   The email includes:
   - **Subject:** "Your Password Has Been Reset"
   - **New Password:** Clearly displayed in a highlighted box
   - **Login Link:** Direct link to the login page
   - **Security Warning:** Recommendation to change password immediately
   - **Security Tips:** Best practices for password management

### For Administrators

1. **View Password Reset Requests**
   - Go to Admin Panel → Messages
   - Look for "Technical Issue" or "Password Reset Request" messages
   - Click on the message to view details

2. **Reset User Password**
   - Click on the user's name or "View User" button
   - Click "Reset Password" button
   - Enter a new temporary password (minimum 8 characters)
   - Confirm the password
   - Click "Reset Password"

3. **Confirmation**
   - Admin sees a success message confirming:
     - Password was reset successfully
     - **Email notification was sent to the user**
     - User's email address
     - Copy of the temporary password (for admin records)
   - Admin can copy the password to clipboard if needed

## Email Configuration

The password reset email feature uses the email configuration in `config/email.php`.

### Requirements
- Email must be enabled in `config/email.php` (`'enabled' => true`)
- Valid SMTP credentials must be configured
- Gmail App Password recommended for Gmail accounts

### Email Template Features
- Professional HTML email design
- Responsive layout for mobile devices
- Clear password display with monospace font
- Security warnings and recommendations
- Fallback plain text version for email clients that don't support HTML

## Security Features

1. **Admin Authentication Required**
   - Only logged-in administrators can reset passwords
   - Prevents unauthorized password changes

2. **Email Verification**
   - Password is sent only to the registered email address
   - User must have access to their email account

3. **Temporary Password Recommendation**
   - Email explicitly recommends changing the password
   - Security tips included in the email

4. **Minimum Password Length**
   - Enforces 8-character minimum for security

## User Experience Flow

```
User Forgets Password
         ↓
User Sends Message via Connect Page
         ↓
Admin Receives Message in Admin Panel
         ↓
Admin Resets Password
         ↓
System Sends Email to User (AUTOMATIC)
         ↓
User Receives Email with New Password
         ↓
User Logs In with New Password
         ↓
User Changes Password (Recommended)
```

## Email Content Example

**Subject:** Your Password Has Been Reset

**Body:**
```
Hello [User Name],

Your password reset request has been processed by our administrator.

Your New Password: [temporary-password-123]

⚠️ IMPORTANT: For your security, please log in and change this 
temporary password immediately.

[Log In Now Button]

Security Tips:
• Change your password after logging in
• Use a strong, unique password
• Never share your password with anyone

---
This password reset was performed by an administrator at 
Agape House Ministries.
If you did not request this reset, please contact us immediately.
```

## Technical Implementation

### Files Modified
1. **api/router.php**
   - Added email notification to `/members/{id}/reset-password` endpoint
   - Sends HTML and plain text email with new password
   - Includes error handling for email failures

2. **admin/index.php**
   - Updated success message to indicate email was sent
   - Shows user's email address in confirmation
   - Includes note about automatic notification

### Email Service Integration
- Uses existing `Service\EmailService` class
- Follows same email template pattern as other notifications
- Graceful failure: Password reset succeeds even if email fails (logged for admin)

## Troubleshooting

### User Not Receiving Email

**Check:**
1. Email configuration in `config/email.php` is correct
2. SMTP credentials are valid
3. Email is enabled (`'enabled' => true`)
4. User's email address is correct in database
5. Check spam/junk folder
6. Check PHP error logs for email sending errors

**Admin can:**
- Manually send the password via the Messages chat
- Copy the password from the success screen
- Use an alternative contact method (phone, in-person)

### Email Sending Failed

- Password reset still succeeds (user account is updated)
- Error is logged in PHP error log
- Admin sees success message with password
- Admin can manually communicate the password to user

## Best Practices

### For Administrators
1. Always verify the user's identity before resetting password
2. Check that the request is from the actual user
3. Keep a record of password reset requests
4. Inform users to check their email for the new password

### For Users
1. Change temporary password immediately after logging in
2. Use a strong, unique password
3. Enable two-factor authentication if available
4. Contact admin immediately if you receive an unexpected password reset email

## Future Enhancements

Potential improvements:
- Self-service password reset with email verification link
- Password reset token system (time-limited)
- SMS notification option
- Password strength meter
- Password history to prevent reuse
- Automatic password expiration for temporary passwords

## Related Files
- `api/router.php` - Password reset endpoint
- `admin/index.php` - Admin UI for password reset
- `src/Service/EmailService.php` - Email sending service
- `config/email.php` - Email configuration
- `views/pages/connect.php` - User contact form
