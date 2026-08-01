<section class="page" id="page-bible">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--bible">
    <div class="page-hero-inner">
      <div class="eyebrow bible-hero-eyebrow">Scripture</div>
      <h2 class="bible-hero-title">Find God's <em>Promise.</em></h2>
      <p class="lede bible-hero-lede">Find Bible verses by reference, keyword, or topic to deepen your understanding of God's Word.</p>
    </div>

    <!-- Spark orb + animated rings + wave bars -->
    <div class="bible-spark-wrap" aria-hidden="true">
      <div class="bible-spark-ring"></div>
      <div class="bible-spark-ring bible-spark-ring--delay"></div>
      <div class="bible-spark"></div>
      
    </div>
  </div>

  <!-- ── Two-column body ───────────────────────────────────────────────────── -->
  <div class="bible-content-bg">
  <div class="bible-body">

    <!-- Left sidebar: search + topic pills -->
    <aside class="bible-sidebar">
      <div class="bible-search-bar">
        <input id="bibleQuery" type="text" placeholder='Try "John 3:16" or "peace"' autocomplete="off">
        <button id="bibleSearchBtn" class="btn btn-dark">Search</button>
      </div>
      <p class="bible-hint">Showing the World English Bible (public domain).</p>

      <div class="bible-topic-row" id="bibleTopicRow">
        <button class="topic-pill active" data-topic="">All</button>
        <button class="topic-pill" data-topic="love">Love</button>
        <button class="topic-pill" data-topic="hope">Hope</button>
        <button class="topic-pill" data-topic="faith">Faith</button>
        <button class="topic-pill" data-topic="fear anxiety">Fear &amp; Anxiety</button>
        <button class="topic-pill" data-topic="strength">Strength</button>
        <button class="topic-pill" data-topic="peace">Peace</button>
        <button class="topic-pill" data-topic="forgiveness">Forgiveness</button>
        <button class="topic-pill" data-topic="salvation">Salvation</button>
      </div>
    </aside>

    <!-- Right main: VOTD + results -->
    <main class="bible-main">

      <!-- Verse of the Day -->
      <div class="votd-card reveal" id="votdCard">
        <span class="votd-label">Verse of the Day</span>
        <blockquote id="votdText">Loading…</blockquote>
        <cite id="votdRef"></cite>
      </div>

      <!-- Results -->
      <div id="bibleResultsMeta" style="display:none;">
        <span id="bibleResultCount"></span>
      </div>
      <div id="bibleResults"></div>

    </main>

  </div><!-- /.bible-body -->
  </div><!-- /.bible-content-bg -->

</section>
