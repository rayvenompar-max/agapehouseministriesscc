# Gallery Comments & Notifications Fix

## Problem
Comments on gallery posts were not being saved to the database because the `comments` and `post_likes` tables were missing 'gallery' in their `target_type` ENUM columns.

## Solution
Run the migration to add 'gallery' support to both tables.

## How to Fix

### Option 1: Run via Browser (Easiest)
1. Make sure XAMPP MySQL is running
2. Open your browser and go to:
   ```
   http://localhost/DigitalEvangelization/fix_gallery_migration.php
   ```
3. You should see a success message
4. **Delete the file** `fix_gallery_migration.php` after running (for security)

### Option 2: Run via phpMyAdmin
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `daybreak` database
3. Click on the **SQL** tab
4. Copy and paste this SQL:
   ```sql
   USE daybreak;
   
   ALTER TABLE comments 
   MODIFY COLUMN target_type ENUM('media','article','announcement','gallery') NOT NULL;
   
   ALTER TABLE post_likes 
   MODIFY COLUMN target_type ENUM('article','media','announcement','gallery') NOT NULL;
   ```
5. Click **Go**

### Option 3: Run via Command Line
```bash
cd d:\xampp\htdocs\DigitalEvangelization\database
mysql -u root daybreak < fix_gallery_support.sql
```

## Verify the Fix
1. Go to your app and navigate to a gallery post
2. Add a comment (e.g., "test comment")
3. Refresh the page
4. ✅ The comment should still be there!
5. ✅ Notifications should now work for gallery posts

## What This Fixes
- ✅ Comments on gallery posts now save to database
- ✅ Comments persist after page refresh
- ✅ Gallery notifications work properly
- ✅ Users can like gallery posts
- ✅ Comment notifications for gallery posts work
- ✅ The Post Detail Modal shows all comments

## Technical Details
The migration adds 'gallery' to the ENUM values in:
- `comments.target_type` column
- `post_likes.target_type` column

This allows the database to accept and store gallery-related comments and likes.
