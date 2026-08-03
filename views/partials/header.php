<header class="site site--warm">
  <div class="nav-wrap">

    <!-- Brand -->
    <div class="brand">
      <img class="brand-glyph" src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House">
      <div>
        <span class="brand-name">Agape House Ministries</span>
        <span class="brand-tag">San Carlos</span>
      </div>
    </div>

    <?php
    $isAdmin = (!empty($_SESSION['admin']['id']))
            || (!empty($_SESSION['admin_logged_in']))
            || (isset($authService) && $authService->isLoggedIn());
    $isMember = isset($memberAuth) && $memberAuth->isLoggedIn();
    ?>

    <nav class="primary" id="primaryNav">

      <!-- Home -->
      <button data-page="home" class="nav-item nav-icon-only active" aria-label="Home">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="nav-tip">Home</span>
      </button>

      <!-- Media dropdown -->
      <div class="nav-group" id="navGroupMedia">
        <button class="nav-item nav-icon-only nav-group-toggle" aria-haspopup="true" aria-expanded="false" aria-label="Media">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="23 7 16 12 23 17 23 7"/>
            <rect x="1" y="5" width="15" height="14" rx="2"/>
          </svg>
          <span class="nav-tip">Media</span>
        </button>
        <div class="nav-dropdown" role="menu">
          <button data-page="watch"   role="menuitem"><i data-lucide="play-circle"></i> Watch &amp; Listen</button>
          <button data-page="read"    role="menuitem"><i data-lucide="book-open"></i> Read</button>
          <button data-page="bible"   role="menuitem"><i data-lucide="cross"></i> Bible Reading</button>
          <button data-page="quizzes" role="menuitem"><i data-lucide="circle-help"></i> Quizzes</button>
        </div>
      </div>

      <!-- Community dropdown -->
      <div class="nav-group" id="navGroupCommunity">
        <button class="nav-item nav-icon-only nav-group-toggle" aria-haspopup="true" aria-expanded="false" aria-label="Community">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span class="nav-tip">Community</span>
        </button>
        <div class="nav-dropdown" role="menu">
          <button data-page="gallery"      role="menuitem"><i data-lucide="image"></i> Gallery</button>
          <button data-page="prayer"       role="menuitem"><i data-lucide="heart-handshake"></i> Prayer Wall</button>
          <button data-page="events"       role="menuitem"><i data-lucide="calendar"></i> Events</button>
          <button data-page="announcement" role="menuitem"><i data-lucide="megaphone"></i> Announcements</button>
          
        </div>
      </div>

      <!-- About dropdown -->
      <div class="nav-group" id="navGroupAbout">
        <button class="nav-item nav-icon-only nav-group-toggle" aria-haspopup="true" aria-expanded="false" aria-label="About">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
          <span class="nav-tip">About</span>
        </button>
        <div class="nav-dropdown" role="menu">
          <button data-page="about"   role="menuitem"><i data-lucide="home"></i> About Us</button>
          <button data-page="connect" role="menuitem"><i data-lucide="link"></i> Connect</button>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/admin" class="nav-item nav-admin-mobile"><i data-lucide="settings"></i> Admin</a>
        <form method="POST" action="<?= BASE_URL ?>/logout" style="margin:0;">
          <button type="submit" class="nav-item nav-logout-mobile">Logout</button>
        </form>
      <?php endif; ?>

    </nav>

    <div class="nav-end">
      <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/admin" class="admin-link"><i data-lucide="settings"></i> Admin</a>
        <form method="POST" action="<?= BASE_URL ?>/logout" style="margin:0;">
          <button type="submit" class="logout-btn">Logout</button>
        </form>

      <?php elseif ($isMember):
        $m = $memberAuth->current();
        if (!array_key_exists('profile_picture', $m)) {
            $memberRecord = (new \Repository\MemberRepository(getDB()))->findById((int)$m['id']);
            $_SESSION['member']['profile_picture'] = $memberRecord?->profilePicture;
            $m['profile_picture'] = $memberRecord?->profilePicture;
        }
        $dispName   = htmlspecialchars($m['display_name'] ?? $m['username'] ?? 'Member');
        $initial    = strtoupper(mb_substr($m['display_name'] ?? $m['username'] ?? 'M', 0, 1));
        $navPicture = !empty($m['profile_picture']) ? htmlspecialchars($m['profile_picture']) : null;
      ?>
        <!-- Messages Icon -->
        <div class="msg-bell-wrap" id="msgBellWrap">
          <button class="msg-bell-btn" id="msgBellBtn" aria-label="Messages" aria-haspopup="true" aria-expanded="false">
            <i data-lucide="message-circle"></i>
            <span class="msg-badge" id="msgBadge" hidden>0</span>
          </button>
          <div class="msg-dropdown" id="msgDropdown" role="dialog" aria-label="Messages">
            <div class="msg-panel-head">
              <h3 class="msg-panel-title" id="msgPanelTitle">Messages</h3>
              <div class="msg-search-wrap">
                <input type="text" class="msg-search-input" id="msgSearchInput" placeholder="Search members to message..." aria-label="Search members">
                <i data-lucide="search" class="msg-search-icon"></i>
              </div>
            </div>
            <div class="msg-list" id="msgList">
              <div class="msg-empty">Loading…</div>
            </div>
          </div>
        </div>

        <!-- Notification Bell -->
        <div class="notif-bell-wrap" id="notifBellWrap">
          <button class="notif-bell-btn" id="notifBellBtn" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
            <i data-lucide="bell"></i>
            <span class="notif-badge" id="notifBadge" hidden>0</span>
          </button>
          <div class="notif-dropdown" id="notifDropdown" role="dialog" aria-label="Notifications">
            <div class="notif-panel-head">
              <h3 class="notif-panel-title">Notifications</h3>
              <button class="notif-mark-all-btn" id="notifMarkAllBtn">Mark all read</button>
            </div>
            <div class="notif-list" id="notifList">
              <div class="notif-empty">Loading…</div>
            </div>
            <div class="notif-panel-foot">
              <button class="notif-clear-btn" id="notifClearBtn">Clear all</button>
            </div>
          </div>
        </div>

        <!-- User chip -->
        <button class="warm-user-chip" id="navMemberPill" aria-haspopup="true" aria-expanded="false">
          <div class="warm-user-avatar">
            <?php if ($navPicture): ?>
              <img src="<?= $navPicture ?>" alt="<?= $initial ?>">
            <?php else: ?>
              <?= $initial ?>
            <?php endif; ?>
          </div>
          <span class="warm-user-name"><?= $dispName ?></span>
          <svg class="warm-user-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div class="warm-dropdown" id="navMemberDropdown" role="dialog" aria-label="User menu">
          <!-- Header: avatar + name + username -->
          <div class="warm-dropdown-user">
            <div class="warm-dropdown-avatar">
              <?php if ($navPicture): ?>
                <img src="<?= $navPicture ?>" alt="<?= $initial ?>">
              <?php else: ?>
                <?= $initial ?>
              <?php endif; ?>
            </div>
            <div>
              <strong class="warm-dropdown-name"><?= $dispName ?></strong>
              <span class="warm-dropdown-handle">@<?= htmlspecialchars($m['username'] ?? 'member') ?></span>
            </div>
          </div>
          <!-- Items -->
          <div class="warm-dropdown-items">
            <button type="button" class="warm-dropdown-item" id="dropdownMyProfile">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              My Profile
            </button>
            <button type="button" class="warm-dropdown-item" id="dropdownMyPrayers">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              My Prayer Requests
            </button>
            <div class="warm-dropdown-divider"></div>
            <button type="button" class="warm-dropdown-item warm-dropdown-item--signout" id="signOutBtn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Sign out
            </button>
          </div>
        </div>
        <form id="signOutForm" method="POST" action="<?= BASE_URL ?>/member/logout" style="display:none;"></form>

      <?php else: ?>
        <a href="<?= BASE_URL ?>/member/login" class="nav-cta warm-nav-cta">Sign in</a>
      <?php endif; ?>

      <button class="menu-toggle" id="menuToggle" aria-label="Open menu"><i data-lucide="menu"></i></button>
    </div>
  </div>
</header>
