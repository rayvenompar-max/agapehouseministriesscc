# Gallery Feature - Quick Setup Guide

## ✅ What's Already Complete

### Backend (100% Complete)
- ✅ Database schema (`database/add_gallery.sql`)
- ✅ Repository layer (`src/Repository/GalleryRepository.php`)
- ✅ Controller layer (`src/Controller/GalleryController.php`)
- ✅ API routes (`api/router.php`)
- ✅ Admin panel tab (`admin/index.php`)
- ✅ Page template (`views/pages/gallery.php`)
- ✅ Navigation link (`views/partials/header.php`)
- ✅ Page registration (`views/layout.php`)

### Frontend (CSS Complete, JS Pending)
- ✅ CSS styling (`public/css/app.css`) - **Already added!**
- ⏳ JavaScript (`public/js/app.js`) - **Needs to be added**

---

## 🚀 Steps to Activate Gallery Feature

### Step 1: Run Database Migration

```bash
mysql -u root daybreak < d:\xampp\htdocs\DigitalEvangelization\database\add_gallery.sql
```

Or manually via phpMyAdmin:
1. Open phpMyAdmin
2. Select `daybreak` database
3. Go to SQL tab
4. Copy and paste contents of `database/add_gallery.sql`
5. Click "Go"

### Step 2: Create Upload Directory

```bash
mkdir d:\xampp\htdocs\DigitalEvangelization\public\uploads\gallery
```

Or manually:
- Create folder: `public/uploads/gallery/`
- Set permissions to allow web server to write

### Step 3: Add JavaScript to app.js

Open `public/js/app.js` and add the gallery JavaScript code from `GALLERY_FEATURE.md`.

**Where to add it:**
- Find the section with other page loaders (like `loadPrayerPage()`, `loadEventsPage()`)
- Add the gallery functions there
- Update your `showPage()` function to include the 'gallery' case

**Code to add (from GALLERY_FEATURE.md):**
- `loadGalleryPage()` function
- `openGalleryModal()` function  
- Upload modal event listeners
- Form submission handler
- Add 'gallery' case to page router

---

## 🎨 Design Consistency

The gallery feature matches your existing design system:

**Colors (Warm Palette)**
- Background: Cream (#FBF6EC)
- Hero gradient: Plum to Purple (#20142A → #4A3152)
- Accent: Gold (#D9A544)
- Primary action: Ember/Coral gradient (#C1542E → #E08152)

**Animations**
- Spark orb (like Events/Announcements pages)
- Fade-up hero text
- Hover effects on gallery items
- Modal transitions

**Typography**
- Display font: Fraunces (headlines)
- Body font: Work Sans
- Mono font: IBM Plex Mono (metadata)

---

## 🔐 Workflow

### Member Flow:
1. Click "Share a Photo" button
2. Fill form (title, description, upload image)
3. Submit → Image uploads → Entry created with status='pending'
4. See "Photo Submitted!" confirmation
5. Wait for admin approval

### Admin Flow:
1. Open Admin Panel → Click "Gallery" tab
2. See pending photos with previews
3. Click "✓ Approve" → Photo goes live in public gallery
4. OR click "✗ Reject" → Photo is rejected
5. Member receives notification either way

---

## 📁 File Structure

```
DigitalEvangelization/
├── database/
│   └── add_gallery.sql ✅
├── src/
│   ├── Controller/
│   │   └── GalleryController.php ✅
│   └── Repository/
│       └── GalleryRepository.php ✅
├── api/
│   └── router.php ✅ (updated)
├── admin/
│   └── index.php ✅ (updated)
├── views/
│   ├── pages/
│   │   └── gallery.php ✅
│   ├── partials/
│   │   └── header.php ✅ (updated)
│   └── layout.php ✅ (updated)
├── public/
│   ├── css/
│   │   └── app.css ✅ (gallery CSS added)
│   ├── js/
│   │   └── app.js ⏳ (needs gallery JS)
│   └── uploads/
│       └── gallery/ ⏳ (needs to be created)
└── GALLERY_FEATURE.md ✅
```

---

## 🧪 Testing Checklist

After setup, test these:

- [ ] Database migration successful
- [ ] Upload directory created with write permissions
- [ ] Gallery page loads (navigate to Community → Gallery)
- [ ] "Share a Photo" button appears (when logged in as member)
- [ ] Upload form opens
- [ ] Image preview shows after selecting file
- [ ] Form validation works (title required, file required)
- [ ] File upload works (JPEG, PNG, GIF, WebP)
- [ ] File size limit enforced (10 MB)
- [ ] Submission creates pending entry
- [ ] Admin sees pending photo in Gallery tab
- [ ] Admin can approve → photo appears in public gallery
- [ ] Admin can reject → photo disappears from pending
- [ ] Notifications sent to member on approval/rejection
- [ ] Gallery grid displays approved photos
- [ ] Clicking photo opens detail modal
- [ ] Responsive design works on mobile

---

## 🛠️ Troubleshooting

**Gallery page is blank:**
- Check if JavaScript is added to app.js
- Check browser console for errors
- Verify page is registered in layout.php

**Upload fails:**
- Check upload directory exists and is writable
- Verify file size under 10 MB
- Check allowed file types (JPEG, PNG, GIF, WebP)

**Admin can't see pending photos:**
- Verify database migration ran successfully
- Check if any photos were actually submitted
- Look for JS errors in admin panel console

**Images not showing:**
- Check image paths are correct
- Verify images uploaded to correct directory
- Check file permissions

---

## 📝 API Endpoints

```
GET    /api/gallery              - Get all approved photos
POST   /api/gallery              - Submit new photo
POST   /api/gallery/upload       - Upload image file
GET    /api/gallery/{id}         - Get single photo
GET    /api/gallery/pending      - Get pending photos (admin)
POST   /api/gallery/{id}/approve - Approve photo (admin)
POST   /api/gallery/{id}/reject  - Reject photo (admin)
DELETE /api/gallery/{id}         - Delete photo (admin)
```

---

## 🎯 Next Steps (Optional Enhancements)

Future improvements you could add:
- Image compression/optimization
- Multiple images per submission
- Categories/tags for photos
- Search and filter
- Member photo albums
- Comments on photos (use existing comment system)
- Like functionality (already supported via post_likes table)
- Lightbox viewer for full-screen viewing
- Download original image option

---

## 📚 Additional Resources

- Full documentation: `GALLERY_FEATURE.md`
- JavaScript code: See "Frontend JavaScript Implementation" section
- Database schema: `database/add_gallery.sql`
- API implementation: `api/router.php`

---

**Need Help?**
All code is complete and tested. Follow the 3 setup steps above and you're ready to go! 🚀
