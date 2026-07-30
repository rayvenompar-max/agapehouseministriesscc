# Announcement Page — Add Button Update

## Changes Made

### 1. **Toolbar Layout** (`views/pages/announcement.php`)
- Moved the announcement count and "New Announcement" button into a flex container
- Keeps filter pills, count, and button all on **one horizontal line**
- No wrapping on desktop or tablet sizes

**Before:**
```html
<div class="ann-toolbar">
  <div class="filter-row">...</div>
  <span class="ann-count">12 announcements</span>
</div>
```

**After:**
```html
<div class="ann-toolbar">
  <div class="filter-row">...</div>
  <div style="display:flex; align-items:center; gap:16px; white-space:nowrap;">
    <span class="ann-count">12 announcements</span>
    <button class="btn btn-dark" id="openAddAnnBtn">+ New Announcement</button>
  </div>
</div>
```

---

### 2. **Add Announcement Modal** (`views/pages/announcement.php`)
- Full modal form matching the design pattern
- Fields:
  - **Title** (required, 255 char max)
  - **Body** (required, textarea)
  - **Category** (dropdown: Ministry/Events/Community/Urgent)
  - **Pin to top** (checkbox)
- Submit → POST `/api/announcements`
- Success → closes modal, reloads list + pinned banner
- Error → shows inline error message

---
 

**Toolbar now enforces single-line layout:**
```css
.ann-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: nowrap; /* ← key change */
  gap: 12px;
}

.ann-toolbar .filter-row {
  flex: 1;
  min-width: 0;
  flex-wrap: nowrap;
  overflow-x: auto; /* horizontal scroll on mobile if needed */
  scrollbar-width: none; /* hide scrollbar */
}
```

On mobile, if filter pills don't fit, they scroll horizontally (invisible scrollbar) instead of wrapping to a new line.

---

### 4. **JavaScript** (`public/js/app.js`)

**Three new functions:**

```javascript
function openAddAnnModal()  
  - Resets form
  - Opens modal
  - Focuses title input

function closeAddAnnModal()
  - Closes modal
  - Restores body scroll

async function submitAddAnn(e)
  - Validates title + body
  - POST /api/announcements
  - On success:
    - Shows success message
    - Reloads pinned banner + list
    - Resets filter to "All"
  - On error: shows error message
```

**Event bindings:**
- `#openAddAnnBtn` → opens modal
- `#addAnnModalClose` → closes modal
- `#addAnnModalBackdrop` → closes modal on backdrop click
- `#addAnnCancelBtn` → closes modal
- `#addAnnForm` submit → calls `submitAddAnn()`
- **Escape key** → closes whichever modal is open (detail or add)

---

## Visual Result

### Desktop/Tablet Layout
```
┌─────────────────────────────────────────────────────────────┐
│  [All] [Ministry] [Events] [Community] [Urgent]   12 announcements  [+ New Announcement] │
└─────────────────────────────────────────────────────────────┘
```

### Mobile Layout (≤480px wide)
```
┌───────────────────────────────────────────┐
│  ← [All] [Ministry] [Events] →    12 ann  [+ New] │
└───────────────────────────────────────────┘
```
Filter pills scroll horizontally (no wrapping).

---

## User Flow

1. User clicks **"+ New Announcement"**
2. Modal opens with blank form
3. User fills:
   - Title: "Parking lot closed next Sunday"
   - Body: "Main lot will be under repair. Use the overflow lot on 5th Street."
   - Category: "Urgent"
   - Pin to top: ☑ (checked)
4. User clicks **"Post Announcement"**
5. API call succeeds
6. Modal shows: "✓ Announcement posted!"
7. Modal closes after 0.8s
8. Page reloads: new announcement appears at top (pinned banner + in list)
9. Filter resets to "All"

---

## Admin vs Public Difference

| Feature | Public Page | Admin Panel |
|---------|------------|-------------|
| Add announcement | ✅ Yes, via modal | ✅ Yes, via modal |
| Edit announcement | ❌ No | ✅ Yes |
| Delete announcement | ❌ No | ✅ Yes |
| Pin toggle | ✅ Yes (checkbox in form) | ✅ Yes (checkbox in form) |
| Approval workflow | ❌ No (posts immediately) | N/A |

**Note:** Currently anyone can post announcements from the public page. If you want moderation, you can:
1. Add a `status` field to the DB (pending/approved)
2. Set new announcements to `pending` by default
3. Filter public list to only show `approved`
4. Add an admin tab to approve pending announcements

---

## Testing Checklist

- [x] Toolbar stays on one line (desktop)
- [x] Filter pills scroll horizontally on mobile
- [x] "+ New Announcement" button opens modal
- [x] Form validation (title + body required)
- [x] Submit posts announcement via API
- [x] Success message appears
- [x] Modal closes automatically
- [x] List refreshes with new announcement
- [x] Pinned banner updates if announcement was pinned
- [x] Filter resets to "All" after submit
- [x] Escape key closes modal
- [x] Backdrop click closes modal
- [x] Cancel button closes modal
- [x] API returns proper JSON (status, data)
- [x] No console errors

---

## Files Modified

1. `views/pages/announcement.php` — toolbar + modal HTML
2. `public/css/app.css` — toolbar flex layout, no-wrap, horizontal scroll
3. `public/js/app.js` — open/close/submit functions + event bindings

**Lines changed:** ~100 (HTML: 40, CSS: 20, JS: 40)

---

## Next Steps (Optional)

- [ ] Add approval workflow (pending → approved)
- [ ] Add edit button on each announcement card (public view)
- [ ] Add image/thumbnail upload for announcements
- [ ] Add rich text editor (TinyMCE, Quill)
- [ ] Add scheduled publishing (publish_at in future)
- [ ] Add character counter on body field (like prayer form)
- [ ] Add search/filter by keyword
- [ ] Add sorting (newest, oldest, pinned first)

---

**Status:** ✅ Complete and tested  
**Deployment:** Ready to use immediately (no DB changes required)
