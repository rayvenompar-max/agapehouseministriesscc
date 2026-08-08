<?php
/**
 * Email Configuration Example
 * 
 * Copy this file to email.php and configure your Gmail SMTP settings.
 * 
 * IMPORTANT: For Gmail, you need to:
 * 1. Enable 2-Factor Authentication on your Google account
 * 2. Generate an App Password at: https://myaccount.google.com/apppasswords
 * 3. Use the App Password (not your regular password) in smtp_password
 */

return [
    // Enable/disable email notifications
    'enabled' => false, // Set to true once configured
    
    // SMTP Configuration for Gmail
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    
    // Your Gmail credentials
    'smtp_username' => 'carletsrapmo@gmail.com',
    'smtp_password' => 'azjtpocsgrfmhvlz',
    
    // From address
    'from_email' => 'carletsrapmo@gmail.com',
    'from_name' => 'Agape House Ministries',
    
    // Site URL
    'site_url' => 'http://localhost/DigitalEvangelization',
];
