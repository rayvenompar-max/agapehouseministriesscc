<section class="page" id="page-prayer">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--prayer">
    <div class="page-hero-inner">
      <div class="eyebrow prayer-hero-eyebrow">Prayer Wall</div>
      <h2 class="prayer-hero-title">Carry it, or <em>lay it down</em></h2>
      <p class="lede prayer-hero-lede">Post a need. Pray over someone else's. No request is too small.</p>
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

  <div class="section-wrap" style="padding-top:44px; padding-bottom:80px;">
    <div class="prayer-layout">

      <!-- Submit form -->
      <div class="form-card">
        <h3 style="font-family:var(--display); margin-bottom:20px; color:var(--white);">Submit a request</h3>
        <div class="field">
          <label for="pcat">Category</label>
          <select id="pcat">
            <option>Healing</option>
            <option>Family</option>
            <option>Guidance</option>
            <option>Provision</option>
            <option>Thanksgiving</option>
          </select>
        </div>
        <div class="field">
          <label for="preq">
            Your request
            <span class="char-counter" id="preqCounter">0/1000</span>
          </label>
          <textarea id="preq" rows="5" maxlength="1000"
            placeholder="Share as much or as little as you'd like… (10 characters minimum)"></textarea>
        </div>
        <button class="submit-btn" id="prayerSubmitBtn">Post to the wall</button>
        <p id="prayerFormMsg" class="form-note" style="display:none;"></p>
      </div>

      <!-- Wall right-side column -->
      <div>
        <div class="filter-row" id="prayerFilterRow" style="margin-bottom:20px;">
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
