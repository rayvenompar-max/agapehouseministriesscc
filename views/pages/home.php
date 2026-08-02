<section class="page active" id="page-home">

  <!-- ── Hero ─────────────────────────────────────────────────────────────── -->
  <div class="hero hero--warm">
    <!-- Animated sun glow -->
    <div class="hw-sun" aria-hidden="true"></div>


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

      <!-- Right: cycling verse carousel -->
      <div class="hero-verses hero-verses--carousel" id="heroVerseCarousel" aria-live="polite" aria-atomic="true">
        <div class="hero-verse-item hero-verse-item--active">
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
        <!-- Dot indicators -->
        <div class="hero-verse-dots" aria-hidden="true">
          <span class="hvd-dot hvd-dot--active"></span>
          <span class="hvd-dot"></span>
          <span class="hvd-dot"></span>
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
          <a href="https://www.facebook.com/agapnistries" class="sidebar-social-btn" title="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
            Facebook
          </a>
          <a href="#" class="sidebar-social-btn" title="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.45A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.97C5.12 20 12 20 12 20s6.88 0 8.59-.45a2.78 2.78 0 001.95-1.97A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
            YouTube
          </a>
          <a href="#" class="sidebar-social-btn" title="TikTok">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.75a4.85 4.85 0 0 1-1.01-.06z"/></svg>
            TikTok
          </a>
        </div>
        <button class="sidebar-see-all warm-see-all" data-page="connect" style="margin-top:10px;">Get in touch →</button>
      </div>

    </div><!-- /.feed-sidebar-inner -->
    </aside>

  </div><!-- /.feed-layout -->

</section>
