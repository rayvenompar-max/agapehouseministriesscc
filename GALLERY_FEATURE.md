# Gallery Feature Implementation

## Overview
The Gallery feature allows members to share photos with the community. All submissions require admin approval before being published to the public feed.

## Components Created

### 1. Database Migration
**File:** `database/add_gallery.sql`
- Creates `gallery` table with fields:
  - `id`, `member_id`, `title`, `description`, `image_url`
  - `status` (pending/approved/rejected)
  - `created_at`, `updated_at`
- Indexes for performance
- Foreign key to members table

**Run migration:**
```bash
mysql -u root daybreak < database/add_gallery.sql
```

### 2. Repository Layer
**File:** `src/Repository/GalleryRepository.php`
- `getApproved()` - Get all approved gallery items
- `getPending()` - Get pending items (admin only)
- `findById()` - Get single item
- `create()` - Submit new photo
- `approve()` / `reject()` - Admin actions
- `delete()` - Remove item
- `getByMember()` - Get member's submissions
- `countByStatus()` - Count items by status

### 3. Controller Layer
**File:** `src/Controller/GalleryController.php`
- Handles all API endpoints
- Validates submissions
- Enforces authentication/authorization
- Sends notifications on approval/rejection

### 4. API Routes
**File:** `api/router.php`
- `GET /api/gallery` - Get approved photos
- `POST /api/gallery` - Submit new photo
- `POST /api/gallery/upload` - Upload image file
- `GET /api/gallery/{id}` - Get single photo
- `GET /api/gallery/pending` - Admin: get pending photos
- `POST /api/gallery/{id}/approve` - Admin: approve photo
- `POST /api/gallery/{id}/reject` - Admin: reject photo
- `DELETE /api/gallery/{id}` - Admin: delete photo

### 5. Frontend Views
**File:** `views/pages/gallery.php`
- Hero section with title and description
- Upload button (members only)
- Gallery grid displaying approved photos
- Upload modal with form
- View photo modal

### 6. Navigation
**File:** `views/partials/header.php`
- Added "Gallery" link to Community dropdown menu
- Uses "image" Lucide icon

### 7. Admin Panel
**File:** `admin/index.php`
- Added "Gallery" tab
- Displays pending photo submissions with preview
- Approve/Reject buttons
- Shows submitter name and timestamp

## Image Upload Specifications
- **Maximum size:** 10 MB
- **Allowed formats:** JPEG, PNG, GIF, WebP
- **Upload directory:** `public/uploads/gallery/`
- **File naming:** Random 32-character hex + extension
- **Security:** MIME type validation via finfo

## Approval Workflow

### Member Submission:
1. Member clicks "Share a Photo" button
2. Fills out form (title required, description optional)
3. Selects image file (preview shown)
4. Submits form
5. Image uploads to server
6. Gallery record created with status='pending'
7. Member sees success message

### Admin Approval:
1. Admin navigates to Gallery tab
2. Reviews pending submissions with image preview
3. Clicks "Approve" → status='approved', notification sent to member
4. OR clicks "Reject" → status='rejected', notification sent to member
5. Approved photos appear in public gallery feed

## Frontend JavaScript Implementation

Add this code to `public/js/app.js`:

```javascript
// ═══════════════════════════════════════════════════════════════════════════
// GALLERY PAGE
// ═══════════════════════════════════════════════════════════════════════════

// ── Gallery Page Loader ──────────────────────────────────────────────────────
async function loadGalleryPage() {
  const grid = document.getElementById('galleryGrid');
  if (!grid) return;
  
  try {
    const res = await fetch(BASE_URL + '/gallery');
    const json = await res.json();
    
    if (json.status !== 'success' || !json.data.length) {
      grid.innerHTML = '<div class="empty-state">No photos yet. Be the first to share!</div>';
      return;
    }
    
    grid.innerHTML = json.data.map(item => `
      <div class="gallery-item" data-id="${item.id}">
        <img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.title)}" loading="lazy">
        <div class="gallery-item-overlay">
          <h4>${escapeHtml(item.title)}</h4>
          <p>by ${escapeHtml(item.display_name || item.username)}</p>
        </div>
      </div>
    `).join('');
    
    // Add click handlers
    document.querySelectorAll('.gallery-item').forEach(item => {
      item.addEventListener('click', () => {
        openGalleryModal(parseInt(item.dataset.id));
      });
    });
  } catch (e) {
    grid.innerHTML = '<div class="error-state">Could not load gallery.</div>';
    console.error('Gallery load error:', e);
  }
}

// ── Open Gallery Modal ────────────────────────────────────────────────────────
async function openGalleryModal(id) {
  const modal = document.getElementById('viewGalleryModal');
  if (!modal) return;
  
  try {
    const res = await fetch(BASE_URL + `/gallery/${id}`);
    const json = await res.json();
    
    if (json.status !== 'success' || !json.data) {
      alert('Could not load photo.');
      return;
    }
    
    const item = json.data;
    
    document.getElementById('viewGalleryMeta').textContent = 
      `Posted by ${item.display_name || item.username} • ${new Date(item.created_at).toLocaleDateString()}`;
    document.getElementById('viewGalleryTitle').textContent = item.title;
    
    document.getElementById('viewGalleryImageWrap').innerHTML = 
      `<img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.title)}">`;
    
    document.getElementById('viewGalleryDescription').textContent = item.description || '';
    
    modal.hidden = false;
    lockScroll();
  } catch (e) {
    console.error('Gallery modal error:', e);
    alert('Could not load photo details.');
  }
}

// ── Upload Gallery Modal ──────────────────────────────────────────────────────
const uploadGalleryModal = document.getElementById('uploadGalleryModal');
const uploadGalleryForm = document.getElementById('uploadGalleryForm');
const galImageInput = document.getElementById('galImage');
const galImagePreview = document.getElementById('galImagePreview');
const galPreviewImg = document.getElementById('galPreviewImg');

// Open upload modal
document.getElementById('openUploadGalleryBtn')?.addEventListener('click', () => {
  uploadGalleryModal.hidden = false;
  lockScroll();
});

// Close upload modal
document.getElementById('uploadGalleryModalClose')?.addEventListener('click', () => {
  animatedModalClose(uploadGalleryModal, () => {
    uploadGalleryForm.reset();
    galImagePreview.style.display = 'none';
    unlockScroll();
  });
});

document.getElementById('uploadGalleryCancelBtn')?.addEventListener('click', () => {
  animatedModalClose(uploadGalleryModal, () => {
    uploadGalleryForm.reset();
    galImagePreview.style.display = 'none';
    unlockScroll();
  });
});

// Close view modal
document.getElementById('viewGalleryModalClose')?.addEventListener('click', () => {
  animatedModalClose(document.getElementById('viewGalleryModal'), unlockScroll);
});

document.getElementById('viewGalleryModalBackdrop')?.addEventListener('click', () => {
  animatedModalClose(document.getElementById('viewGalleryModal'), unlockScroll);
});

// Image preview
galImageInput?.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      galPreviewImg.src = e.target.result;
      galImagePreview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    galImagePreview.style.display = 'none';
  }
});

// Submit gallery photo
uploadGalleryForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const title = document.getElementById('galTitle').value.trim();
  const description = document.getElementById('galDescription').value.trim();
  const imageFile = galImageInput.files[0];
  const submitBtn = document.getElementById('uploadGallerySubmitBtn');
  const msg = document.getElementById('uploadGalleryMsg');
  
  if (!title || !imageFile) {
    msg.textContent = 'Title and image are required.';
    msg.className = 'form-msg';
    msg.style.color = 'var(--reject)';
    msg.hidden = false;
    return;
  }
  
  submitBtn.disabled = true;
  submitBtn.textContent = 'Uploading...';
  msg.hidden = true;
  
  try {
    // Upload image
    const formData = new FormData();
    formData.append('image', imageFile);
    
    const uploadRes = await fetch(BASE_URL + '/gallery/upload', {
      method: 'POST',
      body: formData
    });
    const uploadJson = await uploadRes.json();
    
    if (uploadJson.status !== 'success') {
      throw new Error(uploadJson.message || 'Upload failed');
    }
    
    const imageUrl = uploadJson.data.image_url;
    
    // Create gallery entry
    const createRes = await fetch(BASE_URL + '/gallery', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, description, image_url: imageUrl })
    });
    const createJson = await createRes.json();
    
    if (createJson.status === 'success') {
      showPendingApprovalModal(
        'Your photo has been submitted and is awaiting approval. It will appear in the gallery once reviewed.',
        { title: 'Photo Submitted!', icon: 'image' }
      );
      animatedModalClose(uploadGalleryModal, () => {
        uploadGalleryForm.reset();
        galImagePreview.style.display = 'none';
        unlockScroll();
      });
    } else {
      msg.textContent = createJson.message || 'Submission failed';
      msg.className = 'form-msg';
      msg.style.color = 'var(--reject)';
      msg.hidden = false;
    }
  } catch (e) {
    msg.textContent = e.message || 'An error occurred';
    msg.className = 'form-msg';
    msg.style.color = 'var(--reject)';
    msg.hidden = false;
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Submit for Approval';
  }
});

// ── Add Gallery to Page Router ───────────────────────────────────────────────
// In your existing showPage() function, add this case:
/*
case 'gallery':
  loadGalleryPage();
  // Trigger animation
  setTimeout(() => {
    document.getElementById('page-gallery')?.classList.add('gallery-animate');
  }, 50);
  break;
*/
```

**Integration Points:**

1. **Add to page router** - In your `showPage()` function, add the 'gallery' case
2. **Ensure helper functions exist**:
   - `escapeHtml()` - Should already exist for XSS protection
   - `lockScroll()` / `unlockScroll()` - Should already exist for modals
   - `animatedModalClose()` - Should already exist for modal transitions
   - `showPendingApprovalModal()` - Should already exist for submission feedback

3. **Lucide icons** - Make sure to call `lucide.createIcons()` after loading gallery page

## CSS Styling

✅ **Already Added to `public/css/app.css`**

The gallery CSS has been added to the end of your existing stylesheet. It includes:

- Page hero with warm gradient (matches Events/Announcements pages)
- Spark orb animation
- Responsive gallery grid layout
- Gallery item cards with hover overlays
- Modal styling
- Upload button styling
- Empty/error states

The design follows your existing "Horizon Design System" with warm palette colors:
- Plum (#20142A, #332039)
- Ember (#C1542E)
- Coral (#E08152)
- Gold (#D9A544)
- Cream (#FBF6EC)

All animations and transitions use your existing easing functions and timing.

## Security Considerations
1. **Authentication:** Upload requires member login
2. **File validation:** MIME type checked server-side
3. **File size limit:** 10 MB enforced
4. **Admin-only approval:** Only admins can approve/reject
5. **SQL injection:** Prepared statements used throughout
6. **XSS prevention:** HTML escaping in frontend

## Testing Checklist
- [ ] Run database migration
- [ ] Test member photo upload
- [ ] Verify file size/type restrictions
- [ ] Test admin approval workflow
- [ ] Test admin rejection workflow
- [ ] Verify notifications are sent
- [ ] Check gallery displays only approved photos
- [ ] Test navigation from header menu
- [ ] Verify responsive design
- [ ] Test with various image formats

## Future Enhancements
- Image compression/optimization
- Multiple images per submission
- Categories/tags for photos
- Search and filter functionality
- Member photo albums
- Comments on photos
- Like functionality (already supported via post_likes table)
- Full-screen lightbox viewer
- Download original image option
