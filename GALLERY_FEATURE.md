# Gallery Feature Documentation

## Overview
The Gallery feature allows members to share photos with the community. All submissions require admin approval before being published to the public feed.

## Features

### Multi-Image Upload (Up to 10 Images)
- Select multiple images in a single submission
- Preview all selected images before uploading
- Remove individual images from selection
- Progress indicator during upload
- Single title and description for the entire post

### Smart Collage Layouts
- **1 image**: Full-size display
- **2 images**: Side-by-side layout
- **3 images**: One large image with two smaller ones
- **4+ images**: 2x2 grid with "+X" indicator for additional photos

### Edit & Delete
- Three-dot menu appears on your own posts
- Edit title and description
- Delete posts with confirmation
- Admins can delete any post

### Modal View
- Click any gallery post to see all images in full size
- Scroll through all images in the post
- Shows submitter name and date

## Database Schema

### `gallery` table
Stores gallery post metadata:
- `id` - Unique post ID
- `member_id` - Who submitted it
- `title` - Post title (required)
- `description` - Post description (optional)
- `status` - pending/approved/rejected
- `created_at`, `updated_at` - Timestamps

### `gallery_images` table
Stores multiple images per post:
- `id` - Unique image ID
- `gallery_id` - Links to gallery post
- `image_url` - Path to image file
- `sort_order` - Display order (0, 1, 2, ...)
- `created_at` - Upload timestamp

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/gallery` | Get all approved photos | Public |
| POST | `/api/gallery` | Submit new photo(s) | Member |
| POST | `/api/gallery/upload` | Upload image file | Member |
| GET | `/api/gallery/{id}` | Get single photo with all images | Public |
| GET | `/api/gallery/pending` | Get pending photos (admin) | Admin |
| POST | `/api/gallery/{id}/approve` | Approve photo | Admin |
| POST | `/api/gallery/{id}/reject` | Reject photo | Admin |
| PUT | `/api/gallery/{id}` | Update title/description | Owner/Admin |
| DELETE | `/api/gallery/{id}` | Delete photo | Owner/Admin |

## Image Upload Specifications
- **Maximum size:** 10 MB per image
- **Maximum images:** 10 per post
- **Allowed formats:** JPEG, PNG, GIF, WebP
- **Upload directory:** `public/uploads/gallery/`
- **File naming:** Random 32-character hex + extension
- **Security:** MIME type validation via finfo

## Approval Workflow

### Member Submission:
1. Click "Share a Photo" button
2. Fill out form (title required, description optional)
3. Select one or more image files (preview shown)
4. Submit form
5. Images upload to server
6. Gallery record created with status='pending'
7. Member sees success message

### Admin Approval:
1. Admin navigates to Gallery tab in admin panel
2. Reviews pending submissions with image preview
3. Clicks "Approve" → status='approved', notification sent to member
4. OR clicks "Reject" → status='rejected', notification sent to member
5. Approved photos appear in public gallery feed

## Security Considerations
1. **Authentication:** Upload requires member login
2. **File validation:** MIME type checked server-side
3. **File size limit:** 10 MB per image enforced
4. **Admin-only approval:** Only admins can approve/reject
5. **SQL injection:** Prepared statements used throughout
6. **XSS prevention:** HTML escaping in frontend
7. **Ownership validation:** Backend verifies ownership before edit/delete

## Frontend Components

### JavaScript Functions (in `public/js/app.js`)
- `loadGalleryPage()` - Fetches and displays gallery grid
- `openGalleryModal(id)` - Opens full-size view with all images
- Upload modal with image preview and multi-select
- Form submission with progress indication

### CSS Classes (in `public/css/app.css`)
- `.gallery-item` - Grid item with hover overlay
- `.gallery-item-collage` - Multi-image layouts
- `.gallery-item-menu` - Three-dot menu dropdown
- Modal styling for full-size view
- Responsive layouts for mobile

## Testing Checklist
- [ ] Upload single image
- [ ] Upload multiple images (2-10)
- [ ] Verify file size/type restrictions
- [ ] Test admin approval workflow
- [ ] Test admin rejection workflow
- [ ] Verify notifications are sent
- [ ] Check gallery displays only approved photos
- [ ] Test edit functionality (own posts)
- [ ] Test delete functionality (own posts + admin)
- [ ] Test responsive design on mobile

## Future Enhancements
- Image compression/optimization
- Categories/tags for photos
- Search and filter functionality
- Member photo albums
- Comments on photos
- Like functionality
- Full-screen lightbox viewer
- Download original image option
- Drag-and-drop upload

---

**Installation:** Run `database/add_gallery.sql` and `database/add_gallery_multi_images.sql`  
**Backend:** Complete in `src/Controller/GalleryController.php` and `src/Repository/GalleryRepository.php`  
**Frontend:** Complete in `views/pages/gallery.php`, `public/js/app.js`, and `public/css/app.css`
