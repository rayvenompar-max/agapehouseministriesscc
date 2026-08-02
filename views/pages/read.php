<section class="page" id="page-read">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--read">
    <div class="page-hero-inner">
      <div class="eyebrow read-hero-eyebrow">Read</div>
      <h2 class="read-hero-title">Devotionals &amp; <em>reflections.</em></h2>
      <p class="lede read-hero-lede">Short, honest writing to carry with you through the day.</p>
    </div>

    <!-- Spark orb + animated rings -->
    <div class="read-spark-wrap" aria-hidden="true">
      <div class="read-spark-ring"></div>
      <div class="read-spark-ring read-spark-ring--delay"></div>
      <div class="read-spark"></div>
    </div>
  </div>

  <div class="read-content-bg">
    <div class="section-wrap" style="padding-top:40px; padding-bottom:80px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <h3 style="font-family:var(--display); font-size:18px; font-weight:500;">Latest articles</h3>
        <button class="btn btn-dark" id="openAddArticleBtn">+ Add Article</button>
      </div>

      <!-- Article list — populated by JS -->
      <div id="articleList">
        <p style="padding:20px 0; color:#6B6058;">Loading articles…</p>
      </div>
    </div>
  </div>

</section>

<!-- ── Article Reader Modal ──────────────────────────────────────────────── -->
<div class="article-modal" id="articleModal" role="dialog" aria-modal="true" aria-label="Article reader" hidden>
  <div class="article-modal-backdrop" id="articleModalBackdrop"></div>
  <div class="article-modal-box">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta" id="articleModalMeta"></div>
        <h2 class="article-modal-title" id="articleModalTitle"></h2>
      </div>
      <button class="article-modal-close" id="articleModalClose" aria-label="Close article">✕</button>
    </div>
    <div class="article-modal-body" id="articleModalBody"></div>
  </div>
</div>

<!-- ── Add Article Modal ─────────────────────────────────────────────────── -->
<div class="article-modal" id="addArticleModal" role="dialog" aria-modal="true" aria-label="Add article" hidden>
  <div class="article-modal-backdrop" id="addArticleModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <h2 class="article-modal-title">Add New Article</h2>
      <button class="article-modal-close" id="addArticleModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="addArticleForm" novalidate>
        <div class="form-group">
          <label for="artTitle">Title</label>
          <input type="text" id="artTitle" name="title" maxlength="255" placeholder="Article title" required>
        </div>
        <div class="form-group">
          <label for="artExcerpt">Excerpt <span class="form-hint">(short teaser shown on the list)</span></label>
          <textarea id="artExcerpt" name="excerpt" rows="2" maxlength="500" placeholder="One or two sentences…" required></textarea>
        </div>
        <div class="form-group">
          <label for="artBody">Full Article Body</label>
          <textarea id="artBody" name="body" rows="10" placeholder="Write the full article here…" required></textarea>
        </div>
        <div class="form-group">
          <label for="artDate">Publish Date</label>
          <input type="datetime-local" id="artDate" name="published_at">
        </div>
        <div class="form-msg" id="addArticleMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn-modal-publish" id="addArticleSubmitBtn">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
            Publish Article
          </button>
          <button type="button" class="btn btn-ghost-dark" id="addArticleCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
