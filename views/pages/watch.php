<section class="page" id="page-watch">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--watch">
    <div class="page-hero-inner">
      <div class="eyebrow watch-hero-eyebrow">Watch &amp; Listen</div>
      <h2 class="watch-hero-title">Faith that speaks. <br><em>Hope that lasts.</em></br></h2>
      <p class="lede watch-hero-lede">Watch inspiring sermons, read daily devotionals, and grow together in God's Word.</p>
    </div>

    <!-- Spark orb + animated rings + audio waves -->
    <div class="watch-spark-wrap" aria-hidden="true">
      <div class="watch-spark-ring"></div>
      <div class="watch-spark-ring watch-spark-ring--delay"></div>
      <div class="watch-spark"></div>
      <div class="watch-waves">
        <span></span><span></span><span></span>
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>

  <!-- ── Grid ─────────────────────────────────────────────────────────────── -->
  <div class="watch-content-bg">
    <div class="section-wrap" style="padding-top:40px;">
      <!-- Filter rail -->
      <div class="filter-row" id="mediaFilterRow">
        <button class="filter-pill active" data-type="">All</button>
        <button class="filter-pill" data-type="sermon">Sermons</button>
        <button class="filter-pill" data-type="devotional">Devotionals</button>
        <button class="filter-pill" data-type="testimony">Testimonies</button>
        <button class="filter-pill" data-type="worship">Worship</button>
        <button class="btn btn-dark" id="openAddVideoBtn" style="margin-left:auto;">+ Add Video</button>
      </div>

      <!-- Card grid -->
      <div class="card-grid" id="mediaGrid" style="padding-bottom:80px;">
        <p style="color:#6B6058; padding:20px 0;">Loading media…</p>
      </div>
    </div>
  </div>

</section>

<!-- ── Delete Video Confirmation Modal ──────────────────────────────────────── -->
<div class="article-modal" id="deleteVideoModal" role="dialog" aria-modal="true" aria-label="Delete video" hidden>
  <div class="article-modal-backdrop" id="deleteVideoModalBackdrop"></div>
  <div class="article-modal-box delete-confirm-modal">
    <div class="delete-confirm-icon"><i data-lucide="trash-2"></i></div>
    <h2 class="delete-confirm-title">Delete Video?</h2>
    <p class="delete-confirm-body">
      You're about to delete <strong id="deleteVideoTitle"></strong>.<br>
      This action cannot be undone.
    </p>
    <div id="deleteVideoMsg" hidden></div>
    <div class="delete-confirm-actions">
      <button type="button" class="btn-modal-cancel" id="deleteCancelBtn">Cancel</button>
      <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Yes, Delete</button>
    </div>
  </div>
</div>

<!-- ── Edit Video Modal ───────────────────────────────────────────────────── -->
<div class="article-modal" id="editVideoModal" role="dialog" aria-modal="true" aria-label="Edit video" hidden>
  <div class="article-modal-backdrop" id="editVideoModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Watch &amp; Listen</div>
        <h2 class="article-modal-title">Edit / Replace Video</h2>
      </div>
      <button class="article-modal-close" id="editVideoModalClose" aria-label="Close"><i data-lucide="x"></i></button>
    </div>
    <div class="article-modal-body">
      <form id="editVideoForm" novalidate>
        <div class="form-group">
          <label for="editVidTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="editVidTitle" placeholder="e.g. What It Means to Abide" maxlength="160">
        </div>
        <div class="form-group">
          <label for="editVidType">Type <span class="form-hint">(required)</span></label>
          <div class="select-wrap">
            <select id="editVidType">
              <option value="">— choose —</option>
              <option value="sermon">Sermon</option>
              <option value="devotional">Devotional</option>
              <option value="testimony">Testimony</option>
              <option value="worship">Worship</option>
            </select>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
        </div>
        <div class="form-group">
          <label for="editVidSeries">Series <span class="form-hint">(optional)</span></label>
          <input type="text" id="editVidSeries" placeholder="e.g. Roots">
        </div>
        <div class="form-group">
          <label for="editVidDesc">Description <span class="form-hint">(optional)</span></label>
          <textarea id="editVidDesc" rows="3" placeholder="A short description…"></textarea>
        </div>
        <div class="form-group">
          <label for="editVidUrl">YouTube / Video URL <span class="form-hint">(replace video)</span></label>
          <input type="text" id="editVidUrl" placeholder="https://youtu.be/…">
        </div>
        <div id="editVideoMsg" hidden></div>
      </form>
    </div>
    <div class="article-modal-foot">
      <button type="button" class="btn-modal-cancel" id="editVideoCancelBtn">Cancel</button>
      <button type="submit" form="editVideoForm" class="btn-modal-publish" id="editVideoSubmitBtn">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
        Save Changes
      </button>
    </div>
  </div>
</div>

<!-- ── Add Video Modal ──────────────────────────────────────────────────────── -->
<div class="article-modal" id="addVideoModal" role="dialog" aria-modal="true" aria-label="Add video" hidden>
  <div class="article-modal-backdrop" id="addVideoModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Watch &amp; Listen</div>
        <h2 class="article-modal-title">Add a Video</h2>
      </div>
      <button class="article-modal-close" id="addVideoModalClose" aria-label="Close"><i data-lucide="x"></i></button>
    </div>
    <div class="article-modal-body">
      <form id="addVideoForm" novalidate>
        <div class="form-group">
          <label for="vidTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="vidTitle" placeholder="e.g. What It Means to Abide" maxlength="160">
        </div>
        <div class="form-group">
          <label for="vidType">Type <span class="form-hint">(required)</span></label>
          <div class="select-wrap">
            <select id="vidType">
              <option value="">— choose —</option>
              <option value="sermon">Sermon</option>
              <option value="devotional">Devotional</option>
              <option value="testimony">Testimony</option>
              <option value="worship">Worship</option>
            </select>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
        </div>
        <div class="form-group">
          <label for="vidSeries">Series <span class="form-hint">(optional)</span></label>
          <input type="text" id="vidSeries" placeholder="e.g. Roots">
        </div>
        <div class="form-group">
          <label for="vidDesc">Description <span class="form-hint">(optional)</span></label>
          <textarea id="vidDesc" rows="3" placeholder="A short description of this video…"></textarea>
        </div>
        <div class="form-group">
          <span class="form-label">Video Source <span class="form-hint">(optional)</span></span>
          <div class="vid-source-tabs" role="tablist">
            <button type="button" class="vid-tab active" id="vidTabUrl" role="tab" aria-selected="true" aria-controls="vidPanelUrl">YouTube / URL</button>
            <button type="button" class="vid-tab" id="vidTabFile" role="tab" aria-selected="false" aria-controls="vidPanelFile">Upload File</button>
          </div>
          <div id="vidPanelUrl" role="tabpanel">
            <input type="text" id="vidUrl" placeholder="https://youtu.be/…">
          </div>
          <div id="vidPanelFile" role="tabpanel" hidden>
            <div class="vid-upload-area" id="vidUploadArea">
              <input type="file" id="vidFile" accept="video/*" style="display:none;">
              <div class="vid-upload-placeholder" id="vidUploadPlaceholder">
                <span class="vid-upload-icon"><i data-lucide="clapperboard"></i></span>
                <p>Drag &amp; drop a video file here, or <button type="button" class="vid-browse-btn" id="vidBrowseBtn">browse</button></p>
                <p class="vid-upload-hint">MP4, MOV, WebM · Max 500 MB</p>
              </div>
              <div class="vid-upload-progress" id="vidUploadProgress" hidden>
                <div class="vid-progress-bar"><div class="vid-progress-fill" id="vidProgressFill"></div></div>
                <p class="vid-upload-status" id="vidUploadStatus">Uploading…</p>
              </div>
              <div class="vid-upload-done" id="vidUploadDone" hidden>
                <span><i data-lucide="check-circle"></i></span> <span id="vidUploadFileName"></span>
                <button type="button" class="vid-remove-btn" id="vidRemoveFile"><i data-lucide="x"></i> Remove</button>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:4px;">
          <label for="vidDate">Published</label>
          <input type="datetime-local" id="vidDate">
        </div>
        <div id="addVideoMsg" hidden></div>
      </form>
    </div>
    <div class="article-modal-foot">
      <button type="button" class="btn-modal-cancel" id="addVideoCancelBtn">Cancel</button>
      <button type="submit" form="addVideoForm" class="btn-modal-publish" id="addVideoSubmitBtn">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
        Publish Video
      </button>
    </div>
  </div>
</div>

<!-- ── Video Player Modal ─────────────────────────────────────────────────── -->
<div class="video-modal" id="videoModal" role="dialog" aria-modal="true" aria-label="Video player" hidden>
  <div class="video-modal-backdrop" id="videoModalBackdrop"></div>
  <div class="video-modal-box">
    <div class="video-modal-header">
      <div>
        <div class="video-modal-meta" id="videoModalMeta"></div>
        <h3 class="video-modal-title" id="videoModalTitle"></h3>
      </div>
      <button class="video-modal-close" id="videoModalClose" aria-label="Close video"><i data-lucide="x"></i></button>
    </div>
    <div class="video-modal-player" id="videoModalPlayer"></div>
    <p class="video-modal-desc" id="videoModalDesc"></p>
  </div>
</div>
