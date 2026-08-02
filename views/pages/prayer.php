<section class="page" id="page-prayer">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--prayer">
    <div class="page-hero-inner">
      <p class="eyebrow prayer-hero-eyebrow">Prayer Wall</p>
      <h2 class="prayer-hero-title">Carry it, or <em>lay it down.</em></h2>
      <p class="lede prayer-hero-lede">Post a need. Pray over someone else's. No request is too small.</p>
    </div>

    <!-- Spark orb animation -->
    <div class="prayer-spark-wrap" aria-hidden="true">
      <div class="prayer-spark-ring"></div>
      <div class="prayer-spark-ring prayer-spark-ring--delay"></div>
      <div class="prayer-spark"></div>
    </div>
  </div>

  <!-- ── Wall layout ────────────────────────────────────────────────────────── -->
  <div class="section-wrap" style="padding-top:44px; padding-bottom:80px;">
    <div class="prayer-layout">

      <!-- Submit form -->
      <div class="form-card prayer-form-card">
        <h3 style="font-family:var(--display); margin-bottom:20px; color:var(--white);">Submit a request</h3>
        <div class="field">
          <label for="pcat">Category</label>
          <div class="select-wrap">
            <select id="pcat">
              <option>Healing</option>
              <option>Family</option>
              <option>Guidance</option>
              <option>Provision</option>
              <option>Thanksgiving</option>
            </select>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
        </div>
        <div class="field">
          <label for="preq">
            Your request
            <span class="char-counter" id="preqCounter">0/1000</span>
          </label>
          <textarea id="preq" rows="5" maxlength="1000"
            placeholder="Share as much or as little as you'd like… (10 characters minimum)"></textarea>
        </div>
        <button class="submit-btn prayer-submit-btn" id="prayerSubmitBtn">Post to the wall</button>
        <p id="prayerFormMsg" class="form-note" style="display:none;"></p>
      </div>

      <!-- Wall right-side column -->
      <div>
        <div class="filter-row prayer-filter-row" id="prayerFilterRow" style="margin-bottom:20px;">
          <button class="filter-pill active" data-cat="">All</button>
          <button class="filter-pill" data-cat="Healing">Healing</button>
          <button class="filter-pill" data-cat="Family">Family</button>
          <button class="filter-pill" data-cat="Guidance">Guidance</button>
          <button class="filter-pill" data-cat="Provision">Provision</button>
          <button class="filter-pill" data-cat="Thanksgiving">Thanksgiving</button>
        </div>
        <div id="prayerWall">
          <p style="color:var(--ink-soft);">Loading requests…</p>
        </div>
      </div>

    </div>
  </div>
</section>
