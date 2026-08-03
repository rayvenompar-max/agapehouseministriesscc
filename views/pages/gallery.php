<section class="page" id="page-gallery">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--gallery">
    <div class="page-hero-inner">
      <div class="eyebrow gallery-hero-eyebrow">Gallery</div>
      <h2 class="gallery-hero-title">Community <em>Moments.</em></h2>
      <p class="lede gallery-hero-lede">Celebrating faith, fellowship, and life together through pictures.</p>
    </div>

    <!-- Spark orb decoration -->
    <div class="gallery-spark-wrap" aria-hidden="true">
      <div class="gallery-spark-ring"></div>
      <div class="gallery-spark-ring gallery-spark-ring--delay"></div>
      <div class="gallery-spark"></div>
    </div>
  </div>

  <!-- ── Content ───────────────────────────────────────────────────────────── -->
  <div class="section-wrap" style="padding-top:44px; padding-bottom:80px;">

    <?php
    $isMemberLoggedIn = isset($memberAuth) && $memberAuth->isLoggedIn();
    if ($isMemberLoggedIn):
    ?>
    <div class="gallery-upload-section">
      <button class="btn btn-primary" id="openUploadGalleryBtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Share a Photo
      </button>
    </div>
    <?php endif; ?>

    <div class="gallery-grid" id="galleryGrid">
      <p style="color:var(--ink-soft);padding:40px 0;text-align:center;">Loading gallery…</p>
    </div>

  </div>
</section>

<!-- ── Upload Photo Modal ─────────────────────────────────────────────────── -->
<div class="article-modal" id="uploadGalleryModal" role="dialog" aria-modal="true" aria-label="Upload photo" hidden>
  <div class="article-modal-backdrop" id="uploadGalleryModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Gallery</div>
        <h2 class="article-modal-title">Share a Photo</h2>
      </div>
      <button class="article-modal-close" id="uploadGalleryModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="uploadGalleryForm" novalidate>
        <div class="form-group">
          <label for="galImages">Choose Images <span class="form-hint">(required, max 10 photos)</span></label>
          <input type="file" id="galImages" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
          <div style="font-size:12px;color:var(--ink-soft);margin-top:6px;">
            Max 10 MB per image. Up to 10 images at once. Supported formats: JPEG, PNG, GIF, WebP
          </div>
        </div>
        <div id="galImagesPreview" style="display:none;margin-top:16px;">
          <div style="font-weight:600;margin-bottom:12px;color:var(--ink);">Selected Photos (<span id="photoCount">0</span>)</div>
          <div id="galPreviewGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;"></div>
        </div>
        <div class="form-group" style="margin-top:20px;">
          <label for="galTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="galTitle" maxlength="200" placeholder="Give this post a title" required>
        </div>
        <div class="form-group">
          <label for="galDescription">Description <span class="form-hint">(optional)</span></label>
          <textarea id="galDescription" rows="3" maxlength="1000" placeholder="Tell us about these moments…"></textarea>
        </div>
        <div class="form-msg" id="uploadGalleryMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn btn-primary" id="uploadGallerySubmitBtn">
            <span id="uploadBtnText">Submit for Approval</span>
          </button>
          <button type="button" class="btn btn-ghost-dark" id="uploadGalleryCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── View Photo Modal ───────────────────────────────────────────────────── -->
<div class="article-modal" id="viewGalleryModal" role="dialog" aria-modal="true" aria-label="View photo" hidden>
  <div class="article-modal-backdrop" id="viewGalleryModalBackdrop"></div>
  <div class="article-modal-box" style="max-width:900px;">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta" id="viewGalleryMeta"></div>
        <h2 class="article-modal-title" id="viewGalleryTitle"></h2>
      </div>
      <button class="article-modal-close" id="viewGalleryModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body" id="viewGalleryBody">
      <div id="viewGalleryImageWrap"></div>
      <div id="viewGalleryDescription"></div>
      <div id="viewGalleryActions"></div>
    </div>
  </div>
</div>

<!-- ── Edit Photo Modal ───────────────────────────────────────────────────── -->
<div class="article-modal" id="editGalleryModal" role="dialog" aria-mod al="true" aria-label="Edit photo" hidden>
  <div class="article-modal-backdrop" id="editGalleryModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Gallery</div>
        <h2 class="article-modal-title">Edit Post</h2>
      </div>
      <button class="article-modal-close" id="editGalleryModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="editGalleryForm" novalidate>
        <input type="hidden" id="editGalleryId">
        <div class="form-group">
          <label for="editGalTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="editGalTitle" maxlength="200" placeholder="Post title" required>
        </div>
        <div class="form-group">
          <label for="editGalDescription">Description <span class="form-hint">(optional)</span></label>
          <textarea id="editGalDescription" rows="3" maxlength="1000" placeholder="Tell us about these moments…"></textarea>
        </div>
        <div class="form-msg" id="editGalleryMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn btn-primary" id="editGallerySubmitBtn">Save Changes</button>
          <button type="button" class="btn btn-ghost-dark" id="editGalleryCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Delete Confirmation Modal ──────────────────────────────────────────── -->
<div class="article-modal" id="deleteGalleryModal" role="dialog" aria-modal="true" aria-label="Delete photo" hidden>
  <div class="article-modal-backdrop" id="deleteGalleryModalBackdrop"></div>
  <div class="article-modal-box" style="max-width:480px;">
    <div class="article-modal-header">
      <div>
        <h2 class="article-modal-title">Delete Post?</h2>
      </div>
      <button class="article-modal-close" id="deleteGalleryModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <p style="color:var(--ink-soft);margin-bottom:24px;" id="deleteGalleryMessage">
        Are you sure you want to delete this post? This action cannot be undone.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button type="button" class="btn btn-ghost-dark" id="deleteGalleryCancelBtn">Cancel</button>
        <button type="button" class="btn" id="deleteGalleryConfirmBtn" style="background:#e53935;color:#fff;border:none;">Delete</button>
      </div>
    </div>
  </div>
</div>
