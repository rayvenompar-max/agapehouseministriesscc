<section class="page active" id="page-home">

  <!-- ── Hero ─────────────────────────────────────────────────────────────── -->
  <div class="hero hero--warm">
    <!-- Animated sun glow -->
    <div class="hw-sun" aria-hidden="true"></div>
    <!-- Spinning ray SVG -->
    <svg class="hw-rays" viewBox="0 0 900 900" aria-hidden="true">
      <g fill="none" stroke="#D9A544" stroke-width="2" id="hwRayGroup"></g>
    </svg>

    <div class="hero-inner hero-inner--split">

      <!-- Left: main content -->
      <div class="hero-left">
        <p class="eyebrow">Gospel Ministry</p>
        <h1 class="hero-title">"The <em>Joy</em> of the Lord <br> is your <em>Strength</em>."</h1>
        <p class="hero-psalm">Nehemiah 8:10</p>
        <p class="hero-sub">
          Agape House Ministries — sermons, devotionals, and a community praying for one another.
        </p>
        <div class="hero-actions">
          <button class="btn btn-warm-solid" data-page="watch">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6 3 20 12 6 21 6 3"/></svg>
            Watch the latest message
          </button>
          <button class="btn btn-warm-ghost" data-page="read">Read today's devotional</button>
        </div>
      </div>

      <!-- Right: light verses -->
      <div class="hero-verses">
        <div class="hero-verse-item">
          <blockquote class="hero-verse-text">"You are the light of the world. A town built on a hill cannot be hidden."</blockquote>
          <cite class="hero-verse-ref">Matthew 5:14</cite>
        </div>
        <div class="hero-verse-item">
          <blockquote class="hero-verse-text">"Let your light shine before others, that they may see your good deeds and glorify your Father in heaven."</blockquote>
          <cite class="hero-verse-ref">Matthew 5:16</cite>
        </div>
        <div class="hero-verse-item">
          <blockquote class="hero-verse-text">"I am the light of the world. Whoever follows me will never walk in darkness."</blockquote>
          <cite class="hero-verse-ref">John 8:12</cite>
        </div>
      </div>

    </div>
  </div>


  <!-- ── Feed Layout (3-column) ───────────────────────────────────────────── -->
  <div class="feed-layout feed-layout--warm">

    <!-- ════════════════════════════════════════════════════════════════════
         LEFT SIDEBAR
         ════════════════════════════════════════════════════════════════════ -->
    <aside class="feed-sidebar feed-sidebar--left">

      <?php
        $isLoggedInMember = isset($memberAuth) && $memberAuth->isLoggedIn();
        $currentMember    = $isLoggedInMember ? $memberAuth->current() : null;
        $memberInitial    = $currentMember ? strtoupper(mb_substr($currentMember['display_name'] ?? $currentMember['username'] ?? 'M', 0, 1)) : null;
        $memberName       = $currentMember ? htmlspecialchars($currentMember['display_name'] ?? $currentMember['username']) : null;
        // Resolve profile picture — use session value if present, otherwise load from DB once
        if ($currentMember && !array_key_exists('profile_picture', $currentMember)) {
            $memberRecord = (new \Repository\MemberRepository(getDB()))->findById((int)$currentMember['id']);
            $_SESSION['member']['profile_picture'] = $memberRecord?->profilePicture;
            $currentMember['profile_picture']      = $memberRecord?->profilePicture;
        }
        $memberPicture = $currentMember && !empty($currentMember['profile_picture']) ? htmlspecialchars($currentMember['profile_picture']) : null;
      ?>

      <?php if ($isLoggedInMember): ?>
      <!-- ── Profile card — OUTSIDE scroll container so always visible ── -->
      <div class="sidebar-card sidebar-profile-card warm-profile-card">
        <div class="warm-profile-cover"></div>
        <div class="warm-profile-body">
          <div class="sidebar-avatar-lg" id="sidebarAvatarLg">
            <?php if ($memberPicture): ?>
              <img src="<?= $memberPicture ?>" alt="<?= $memberInitial ?>">
            <?php else: ?>
              <?= $memberInitial ?>
            <?php endif; ?>
          </div>
          <h3 class="warm-profile-name"><?= $memberName ?></h3>
          <div class="warm-profile-role">
            <span class="sidebar-role-dot"></span>Member · Agape House
          </div>
          <div class="warm-profile-stats">
            <div class="wps-item"><strong id="leftStatFollowing">—</strong><span>Following</span></div>
            <div class="wps-div"></div>
            <div class="wps-item"><strong id="leftStatFollowers">—</strong><span>Followers</span></div>
          </div>
          <div class="sidebar-profile-links">
            <button class="sidebar-profile-link" id="openMyPrayersBtn">
              <span class="spli"><i data-lucide="hand-heart"></i></span> My Prayer Requests
            </button>
            <button class="sidebar-profile-link" data-page="prayer">
              <span class="spli"><i data-lucide="users"></i></span> Prayer Wall
            </button>
            <button class="sidebar-profile-link" id="openMyProfileBtn">
              <span class="spli"><i data-lucide="user"></i></span> My Profile
            </button>
          </div>
        </div>
      </div>
      <?php else: ?>
      <!-- ── Guest sign-in prompt — also outside scroll container ── -->
      <div class="sidebar-card sidebar-signin-prompt warm-signin-prompt">
        <div class="warm-signin-glyph"></div>
        <strong>Welcome to Agape House</strong>
        <p>Sign in to like, comment, save posts, and join the community.</p>
        <a href="<?= BASE_URL ?>/member/login" class="btn btn-warm-solid" style="width:100%;justify-content:center;">Sign in</a>
        <a href="<?= BASE_URL ?>/member/register" class="sidebar-register-link">Create an account →</a>
      </div>
      <?php endif; ?>

      <!-- ── Scrollable area below profile ── -->
      <div class="feed-sidebar-inner">

      <!-- ── Quick nav ── -->
      <div class="sidebar-card">
        <div class="sidebar-section-title">Quick Access</div>
        <nav class="sidebar-nav">
          <button class="sidebar-nav-item" data-page="watch"><span class="sidebar-nav-icon"><i data-lucide="play-circle"></i></span>Watch &amp; Listen</button>
          <button class="sidebar-nav-item" data-page="read"><span class="sidebar-nav-icon"><i data-lucide="book-open"></i></span>Read</button>
          <button class="sidebar-nav-item" data-page="bible"><span class="sidebar-nav-icon"><i data-lucide="cross"></i></span>Bible</button>
          <button class="sidebar-nav-item" data-page="prayer"><span class="sidebar-nav-icon"><i data-lucide="heart-handshake"></i></span>Prayer Wall</button>
          <button class="sidebar-nav-item" data-page="events"><span class="sidebar-nav-icon"><i data-lucide="calendar"></i></span>Events</button>
          <button class="sidebar-nav-item" data-page="announcement"><span class="sidebar-nav-icon"><i data-lucide="megaphone"></i></span>Announcements</button>
        </nav>
      </div>

      <!-- ── Live service indicator ── -->
      <div class="sidebar-card sidebar-live-card">
        <div class="sidebar-section-title">Services</div>
        <div class="sidebar-live-status" id="sidebarLiveStatus">
          <span class="live-dot" id="sidebarLiveDot"></span>
          <span id="liveStatusText">Loading…</span>
        </div>
        <div id="sidebarUpcomingServices" class="sidebar-upcoming"></div>
      </div>

      <!-- ── Give / Donate ── -->
      <div class="sidebar-card sidebar-give-card">
        <div class="sidebar-give-inner">
          <div class="sidebar-give-icon"><i data-lucide="feather"></i></div>
          <div>
            <div class="sidebar-section-title" style="color:var(--sun-light);margin-bottom:4px;">Support the Ministry</div>
            <p style="font-size:12px;color:#A9BFD6;line-height:1.5;margin:0 0 12px;">Your generosity carries the Gospel further.</p>
          </div>
        </div>
        <button class="btn btn-primary" data-page="connect" style="width:100%;justify-content:center;font-size:13px;"><i data-lucide="heart"></i> Give / Donate</button>
      </div>

    </div><!-- /.feed-sidebar-inner -->
    </aside>
    <!-- ════════════════════════════════════════════════════════════════════
         MAIN FEED
         ════════════════════════════════════════════════════════════════════ -->
    <main class="feed-main">
      <div class="feed-main-header">
        <p class="eyebrow warm-eyebrow">What's New</p>
        <h2>Latest From <em>The Ministry</em></h2>
      </div>

      <div id="home-feed" class="home-feed">
        <div class="feed-loading">
          <span class="feed-spinner"></span>
          <span>Loading…</span>
        </div>
      </div>

      <div id="home-feed-empty" class="feed-empty" hidden>
        <p>Nothing posted yet. Check back soon!</p>
      </div>
    </main>

    <!-- ════════════════════════════════════════════════════════════════════
         RIGHT SIDEBAR
         ════════════════════════════════════════════════════════════════════ -->
    <aside class="feed-sidebar feed-sidebar--right">
    <div class="feed-sidebar-inner">

      <!-- ── Upcoming Events ── -->
      <div class="sidebar-card warm-sidebar-card">
        <div class="sidebar-section-title"><i data-lucide="calendar"></i> Upcoming Events</div>
        <div id="rsidebarEvents" class="sidebar-upcoming">
          <div class="sidebar-loading-sm"><span class="feed-spinner"></span></div>
        </div>
        <button class="sidebar-see-all warm-see-all" data-page="events">See all events →</button>
      </div>

      <!-- ── Verse of the Day ── -->
      <div class="sidebar-card sidebar-verse-card warm-verse-card">
        <div class="sidebar-section-title"><i data-lucide="sparkles"></i> Verse of the Day</div>
        <blockquote class="sidebar-verse-text">
          "<?= htmlspecialchars($votd['text']) ?>"
        </blockquote>
        <cite class="sidebar-verse-ref"><?= htmlspecialchars($votd['ref']) ?></cite>
      </div>

      <!-- ── Prayer Wall preview ── -->
      <div class="sidebar-card warm-sidebar-card">
        <div class="sidebar-section-title"><i data-lucide="heart-handshake"></i> Prayer Wall</div>
        <div id="rsidebarPrayers" class="rside-prayer-list">
          <div class="sidebar-loading-sm"><span class="feed-spinner"></span></div>
        </div>
        <button class="sidebar-see-all warm-see-all" data-page="prayer">Visit the wall →</button>
      </div>

      <!-- ── Latest Sermon Series ── -->
      <div class="sidebar-card warm-sidebar-card" id="rsidebarSeriesCard" hidden>
        <div class="sidebar-section-title"><i data-lucide="film"></i> Current Series</div>
        <div id="rsidebarSeries"></div>
      </div>

      <!-- ── Announcements digest ── -->
      <div class="sidebar-card warm-sidebar-card">
        <div class="sidebar-section-title"><i data-lucide="pin"></i> Announcements</div>
        <div id="rsidebarAnn" class="rside-ann-list">
          <div class="sidebar-loading-sm"><span class="feed-spinner"></span></div>
        </div>
        <button class="sidebar-see-all warm-see-all" data-page="announcement">See all →</button>
      </div>

      <!-- ── Connect / Contact card ── -->
      <div class="sidebar-card sidebar-connect-card warm-sidebar-card">
        <div class="sidebar-section-title"><i data-lucide="map-pin"></i> Find Us</div>
        <div class="sidebar-connect-info">
          <div class="sci-row"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px"><path d="M18 22V10L12 2 6 10v12"/><path d="M9 22V12h6v10"/><path d="M12 2v4"/><circle cx="12" cy="7" r="1" fill="currentColor" stroke="none"/></svg> <span>San Carlos City, Negros Occidental</span></div>
          <div class="sci-row"><i data-lucide="clock"></i> <span>Sun Service 1PM - 4PM · Wed 5PM · Fri 5PM · Sat 5PM</span></div>
        </div>
        <div class="sidebar-socials">
          <a href="#" class="sidebar-social-btn" title="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
            Facebook
          </a>
          <a href="#" class="sidebar-social-btn" title="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.45A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.97C5.12 20 12 20 12 20s6.88 0 8.59-.45a2.78 2.78 0 001.95-1.97A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
            YouTube
          </a>
          <a href="#" class="sidebar-social-btn" title="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
            Instagram
          </a>
        </div>
        <button class="sidebar-see-all warm-see-all" data-page="connect" style="margin-top:10px;">Get in touch →</button>
      </div>

    </div><!-- /.feed-sidebar-inner -->
    </aside>

  </div><!-- /.feed-layout -->


<!-- ── My Prayer Requests Drawer (left-side) ───────────────────────────────── -->
<div id="prayerDrawer" class="prayer-drawer" hidden>
  <div class="prayer-drawer-backdrop"></div>
  <div class="prayer-drawer-panel">

    <div class="prayer-drawer-header">
      <h3 class="prayer-drawer-title"><i data-lucide="hand-heart"></i> My Prayer Requests</h3>
      <button class="prayer-drawer-close" id="prayerDrawerClose" aria-label="Close">✕</button>
    </div>

    <!-- Submit form -->
    <div class="prayer-drawer-form">
      <div class="prayer-field">
        <label for="drawerPcat">Category</label>
        <select id="drawerPcat">
          <option>Healing</option>
          <option>Family</option>
          <option>Guidance</option>
          <option>Provision</option>
          <option>Thanksgiving</option>
        </select>
      </div>
      <div class="prayer-field">
        <label for="drawerPreq">
          Your request
          <span class="char-counter" id="drawerPreqCounter">0/1000</span>
        </label>
        <textarea id="drawerPreq" rows="4" maxlength="1000"
          placeholder="Share your prayer need… (10 characters minimum)"></textarea>
      </div>
      <button class="prayer-drawer-submit-btn" id="drawerPrayerSubmitBtn">Post to the wall</button>
      <p id="drawerPrayerMsg" class="prayer-drawer-form-note" style="display:none;"></p>
    </div>

    <div class="prayer-drawer-divider">
      <span>Your submitted requests</span>
    </div>

    <!-- List of this member's past requests -->
    <div class="prayer-drawer-list" id="myPrayerList">
      <p class="prayer-drawer-empty">You haven't submitted any requests yet.</p>
    </div>

  </div>
</div>

<!-- ── My Profile Drawer (left-side) ──────────────────────────────────────── -->
<div id="profileDrawer" class="profile-drawer" hidden>
  <div class="profile-drawer-backdrop"></div>
  <div class="profile-drawer-panel">

    <div class="profile-drawer-header">
      <h3 class="profile-drawer-title">My Profile</h3>
      <button class="profile-drawer-close" id="profileDrawerClose" aria-label="Close">✕</button>
    </div>

    <div class="profile-drawer-body">

      <!-- Avatar + name block -->
      <div class="pd-hero">
        <div class="pd-avatar-wrap">
          <div class="pd-avatar" id="pdAvatar"><?= $memberInitial ?? '?' ?></div>
          <label class="pd-avatar-upload-btn" for="pdAvatarInput" title="Change photo" aria-label="Upload profile picture">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="11" height="11">
              <path d="M11 1l4 4L5 15H1v-4L11 1z"/>
            </svg>
          </label>
          <input type="file" id="pdAvatarInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">
        </div>
        <div class="pd-hero-info">
          <div class="pd-display-name" id="pdDisplayName"><?= $memberName ?? '' ?></div>
          <div class="pd-username" id="pdUsername"></div>
          <div class="pd-badge">
            <span class="sidebar-role-dot"></span>Member · Agape House
          </div>
        </div>
      </div>

      <!-- Info rows -->
      <div class="pd-info-grid">
        <div class="pd-info-row">
          <span class="pd-info-icon"><i data-lucide="mail"></i></span>
          <div>
            <div class="pd-info-label">Email</div>
            <div class="pd-info-value" id="pdEmail">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon"><i data-lucide="id-card"></i></span>
          <div>
            <div class="pd-info-label">Username</div>
            <div class="pd-info-value" id="pdUsernameVal">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon"><i data-lucide="calendar"></i></span>
          <div>
            <div class="pd-info-label">Member since</div>
            <div class="pd-info-value" id="pdMemberSince">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon"><i data-lucide="clock"></i></span>
          <div>
            <div class="pd-info-label">Last login</div>
            <div class="pd-info-value" id="pdLastLogin">—</div>
          </div>
        </div>
      </div>

      <div class="pd-divider"></div>

      <!-- ── Edit Profile (single form) ── -->
      <div class="pd-section-title">Edit Profile</div>
      <form id="pdEditForm" novalidate autocomplete="off">

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdNameInput">Display Name</label>
          <input type="text" id="pdNameInput" maxlength="120"
                 placeholder="Your display name" autocomplete="off">
        </div>

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdUsernameInput">Username</label>
          <div class="pd-input-prefix-wrap">
            <span class="pd-input-prefix">@</span>
            <input type="text" id="pdUsernameInput" maxlength="60"
                   placeholder="yourhandle" autocomplete="off" spellcheck="false">
          </div>
        </div>

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdEmailInput">Email</label>
          <input type="email" id="pdEmailInput" maxlength="160"
                 placeholder="you@example.com" autocomplete="off">
        </div>

        <div class="pd-divider" style="margin:18px 0 14px;"></div>
        <div class="pd-section-title" style="margin-bottom:12px;">Change Password <span style="font-weight:400;font-size:10px;color:var(--ink-soft);">(leave blank to keep current)</span></div>

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdCurrentPass">Current Password</label>
          <input type="password" id="pdCurrentPass" maxlength="255"
                 placeholder="Required to save any changes" autocomplete="current-password">
        </div>

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdNewPass">New Password</label>
          <input type="password" id="pdNewPass" maxlength="255"
                 placeholder="Min 8 characters" autocomplete="new-password">
          <div class="pd-pass-strength" id="pdPassStrength" hidden>
            <div class="pd-pass-bar"><div class="pd-pass-fill" id="pdPassFill"></div></div>
            <span class="pd-pass-label" id="pdPassLabel"></span>
          </div>
        </div>

        <div class="pd-field-group">
          <label class="pd-field-label" for="pdConfirmPass">Confirm New Password</label>
          <input type="password" id="pdConfirmPass" maxlength="255"
                 placeholder="Repeat new password" autocomplete="new-password">
        </div>

        <p id="pdSaveMsg" class="pd-save-msg" style="display:none;"></p>
        <button type="submit" class="pd-save-btn" id="pdSaveBtn">Save changes</button>

      </form>

      <div class="pd-divider"></div>

      <!-- Stats -->
      <div class="pd-section-title">Activity</div>
      <div class="pd-stats-row">
        <div class="pd-stat">
          <span id="pdStatFollowing">0</span>
          <small>Following</small>
        </div>
        <div class="pd-stat-div"></div>
        <div class="pd-stat">
          <span id="pdStatFollowers">0</span>
          <small>Followers</small>
        </div>
      </div>

      <div class="pd-divider"></div>

      <!-- Sign out -->
      <div class="pd-signout-wrap">
        <button type="button" class="pd-signout-btn" id="pdSignOutBtn">Sign out of Agape House</button>
      </div>

    </div><!-- /.profile-drawer-body -->
  </div>
</div>

<!-- ── Comment Drawer ──────────────────────────────────────────────────────── --><div id="commentDrawer" class="comment-drawer" hidden>
  <div class="comment-drawer-backdrop"></div>
  <div class="comment-drawer-panel">
    <div class="comment-drawer-header">
      <h3 class="comment-drawer-title">Comments</h3>
      <button class="comment-drawer-close" id="commentDrawerClose" aria-label="Close">✕</button>
    </div>

    <div class="comment-list" id="commentList">
      <div class="comment-loading">
        <span class="feed-spinner"></span> Loading…
      </div>
    </div>

    <div class="comment-form-wrap" id="commentFormWrap">
      <!-- injected by JS based on login state -->
    </div>
  </div>
</div>

<!-- ── Warm hero ray generation ── -->
<script>
(function() {
  var g = document.getElementById('hwRayGroup');
  if (!g) return;
  var count = 20;
  for (var i = 0; i < count; i++) {
    var angle = (360 / count) * i;
    var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', '450'); line.setAttribute('y1', '450');
    line.setAttribute('x2', '450'); line.setAttribute('y2', '30');
    line.setAttribute('transform', 'rotate(' + angle + ' 450 450)');
    g.appendChild(line);
  }
})();
</script>

</section>
