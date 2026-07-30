<section class="page" id="page-bible">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--bible">
    <div class="page-hero-inner">
      <div class="eyebrow bible-hero-eyebrow">Scripture</div>
      <h2 class="bible-hero-title">Search the <em>Bible</em></h2>
      <p class="lede bible-hero-lede">Look up a reference like "John 3:16", or search by topic — love, fear, hope, and more.</p>
    </div>

    <!-- Sun disc + rays -->
    <div class="page-hero-horizon-stage" aria-hidden="true">
      <div class="sun-wrap">
        <div class="sun-ray sun-ray--r1"></div>
        <div class="sun-ray sun-ray--r2"></div>
        <div class="sun-ray sun-ray--r3"></div>
        <div class="sun-ray sun-ray--r4"></div>
        <div class="sun-ray sun-ray--r5"></div>
        <div class="sun-ray sun-ray--r6"></div>
        <div class="sun-ray sun-ray--r7"></div>
        <div class="sun-ray sun-ray--r8"></div>
        <div class="sun-ray sun-ray--r9"></div>
        <div class="sun-disc"></div>
      </div>
    </div>
  </div>

  <div class="section-wrap bible-layout" style="padding-top:40px; padding-bottom:80px;">

    <!-- ── Left sidebar: search + topics ─────────────────────────────────── -->
    <aside class="bible-sidebar">
      <div class="bible-search-bar">
        <input id="bibleQuery" type="text" placeholder='Try "John 3:16" or "peace"' autocomplete="off">
        <button id="bibleSearchBtn" class="btn btn-primary">Search</button>
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

    <!-- ── Main: verse of the day + results ──────────────────────────────── -->
    <main class="bible-main">
      <!-- Verse of the day -->
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

  </div>
</section>
