<section class="page" id="page-events">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--events">
    <div class="page-hero-inner">
      <div class="eyebrow events-hero-eyebrow">Events</div>
      <h2 class="events-hero-title">Livestreams &amp; <em>gatherings</em></h2>
      <p class="lede events-hero-lede">Join from wherever you are, or find a seat in a room nearby.</p>
    </div>

    <!-- Sun disc + rays (same as watch hero) -->
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

  <div class="section-wrap" style="padding-top:40px; padding-bottom:80px;">

    <h3 style="font-family:var(--display); font-size:18px; color:var(--ink); margin-bottom:6px;">
      <span class="live-dot"></span>Live this week
    </h3>
    <div id="weeklySchedule">
      <p style="color:var(--ink-soft);">Loading schedule…</p>
    </div>

    <h3 style="font-family:var(--display); font-size:18px; color:var(--ink); margin-top:48px; margin-bottom:20px;">
      Upcoming gatherings
    </h3>
    <div class="event-cards" id="upcomingEvents">
      <p style="color:var(--ink-soft);">Loading events…</p>
    </div>

  </div>
</section>

<!-- ── Join Event Modal ───────────────────────────────────────────────────── -->
<div class="article-modal" id="joinEventModal" role="dialog" aria-modal="true" aria-label="Join event" hidden>
  <div class="article-modal-backdrop" id="joinEventModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta" id="joinEventMeta"></div>
        <h2 class="article-modal-title" id="joinEventTitle"></h2>
      </div>
      <button class="article-modal-close" id="joinEventModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body" id="joinEventBody"></div>
  </div>
</div>

<!-- ── Add Event Modal ────────────────────────────────────────────────────── -->
<div class="article-modal" id="addEventModal" role="dialog" aria-modal="true" aria-label="Add event" hidden>
  <div class="article-modal-backdrop" id="addEventModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <h2 class="article-modal-title">Add New Event</h2>
      <button class="article-modal-close" id="addEventModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="addEventForm" novalidate>
        <div class="form-group">
          <label for="evtTitle">Title</label>
          <input type="text" id="evtTitle" maxlength="255" placeholder="e.g. Sunday Morning Service" required>
        </div>
        <div class="form-group">
          <label for="evtDesc">Description</label>
          <textarea id="evtDesc" rows="3" placeholder="Brief description of the event…"></textarea>
        </div>
        <div class="form-group">
          <label for="evtLocation">Location</label>
          <input type="text" id="evtLocation" maxlength="255" placeholder="e.g. Main Site or Online">
        </div>
        <div class="form-group form-group--half">
          <div>
            <label for="evtTime">Start Time</label>
            <input type="time" id="evtTime" required>
          </div>
          <div>
            <label for="evtLivestream" style="flex-direction:row;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" id="evtLivestream" style="width:auto;margin:0;">
              Livestream available
            </label>
          </div>
        </div>
        <div class="form-group">
          <label style="flex-direction:row;align-items:center;gap:8px;cursor:pointer;">
            <input type="checkbox" id="evtRecurring" style="width:auto;margin:0;">
            This is a recurring weekly event
          </label>
        </div>
        <div class="form-group" id="evtRecurDayGroup" style="display:none;">
          <label for="evtRecurDay">Day of week</label>
          <select id="evtRecurDay">
            <option>Sunday</option><option>Monday</option><option>Tuesday</option>
            <option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option>
          </select>
        </div>
        <div class="form-group" id="evtDateGroup">
          <label for="evtDate">Event Date</label>
          <input type="date" id="evtDate">
        </div>
        <div class="form-msg" id="addEventMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn btn-primary" id="addEventSubmitBtn">Save Event</button>
          <button type="button" class="btn btn-ghost-dark" id="addEventCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
