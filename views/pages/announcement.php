<section class="page" id="page-announcement">

  <!-- ── Hero ─────────────────────────────────────────────────────────────── -->
  <div class="page-hero ann-hero page-hero--ann">
    <div class="page-hero-inner">
      <div class="eyebrow ann-hero-eyebrow">Announcement</div>
      <h2 class="ann-hero-title">What's happening <em>around<br>the church.</em></h2>
      <p class="lede ann-hero-lede">Ministry updates, upcoming events, and things worth knowing — all in one place.</p>
    </div>

    <!-- Spark orb decoration (mirrors Events page) -->
    <div class="ann-spark-wrap" aria-hidden="true">
      <div class="ann-spark-ring"></div>
      <div class="ann-spark-ring ann-spark-ring--delay"></div>
      <div class="ann-spark"></div>
    </div>
  </div>

  <!-- ── Pinned ───────────────────────────────────────────────────────────── -->
  <div class="section-wrap" style="padding-top:36px; padding-bottom:0;">
    <div id="annPinned"></div>
  </div>

  <!-- ── Filter + list ────────────────────────────────────────────────────── -->
  <div class="section-wrap" style="padding-top:32px; padding-bottom:72px;">
    <div class="ann-toolbar">
      <div class="filter-row" id="annFilterRow">
        <button class="filter-pill active" data-cat="">All</button>
        <button class="filter-pill" data-cat="Ministry">Ministry</button>
        <button class="filter-pill" data-cat="Events">Events</button>
        <button class="filter-pill" data-cat="Community">Community</button>
        <button class="filter-pill" data-cat="Urgent">Urgent</button>
      </div>
      <span class="ann-count" id="annCount"></span>
    </div>

    <div id="annList"></div>
  </div>

</section>
