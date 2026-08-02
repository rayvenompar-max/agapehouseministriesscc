<section class="page" id="page-events">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero page-hero--events">
    <div class="page-hero-inner">
      <div class="eyebrow events-hero-eyebrow">Events</div>
      <h2 class="events-hero-title">Livestreams &amp; <em>Gatherings.</em></h2>
      <p class="lede events-hero-lede">Join from wherever you are, or find a seat in a room nearby.</p>
    </div>

    <!-- Spark orb decoration -->
    <div class="events-spark-wrap" aria-hidden="true">
      <div class="events-spark-ring"></div>
      <div class="events-spark-ring events-spark-ring--delay"></div>
      <div class="events-spark"></div>
    </div>
  </div>

  <div class="section-wrap events-content-wrap">

    <div class="events-section-head">
      <span class="events-section-mark"></span>
      <h3 class="events-section-title">Live this week</h3>
    </div>
    <div id="weeklySchedule">
      <p style="color:var(--ink-soft);">Loading schedule…</p>
    </div>

    <div class="events-section-head events-section-head--upcoming">
      <span class="events-section-mark events-section-mark--upcoming"></span>
      <h3 class="events-section-title">Upcoming gatherings</h3>
    </div>
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
