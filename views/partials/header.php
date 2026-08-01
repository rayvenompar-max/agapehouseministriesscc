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
      <button data-page="home" class="nav-item active">Home</button>

      <!-- Media dropdown -->
      <div class="nav-group" id="navGroupMedia">
        <button class="nav-item nav-group-toggle" aria-haspopup="true" aria-expanded="false">
          Media <span class="nav-caret">‹</span>
        </button>
        <div class="nav-dropdown" role="menu">
          <button data-page="watch"   role="menuitem"><i data-lucide="play-circle"></i> Watch &amp; Listen</button>
          <button data-page="read"    role="menuitem"><i data-lucide="book-open"></i> Read</button>
          <button data-page="bible"   role="menuitem"><i data-lucide="cross"></i> Bible</button>
          <button data-page="quizzes" role="menuitem"><i data-lucide="circle-help"></i> Quizzes</button>
        </div>
      </div>

      <!-- Community dropdown -->
      <div class="nav-group" id="navGroupCommunity">
        <button class="nav-item nav-group-toggle" aria-haspopup="true" aria-expanded="false">
          Community <span class="nav-caret">‹</span>
        </button>
        <div class="nav-dropdown" role="menu">
          <button data-page="prayer"       role="menuitem"><i data-lucide="heart-handshake"></i> Prayer Wall</button>
          <button data-page="events"       role="menuitem"><i data-lucide="calendar"></i> Events</button>
          <button data-page="announcement" role="menuitem"><i data-lucide="megaphone"></i> Announcements</button>
        </div>
      </div>

      <!-- About dropdown -->
      <div class="nav-group" id="navGroupAbout">
        <button class="nav-item nav-group-toggle" aria-haspopup="true" aria-expanded="false">
          About <span class="nav-caret">‹</span>
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
        <!-- Notification Bell -->
        <div class="notif-bell-wrap" id="notifBellWrap">
          <button class="notif-bell-btn warm-bell-btn" id="notifBellBtn" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
            <i data-lucide="bell"></i>
            <span class="notif-badge" id="notifBadge" hidden>0</span>
          </button>
          <div class="notif-dropdown" id="notifDropdown" role="dialog" aria-label="Notifications">
            <div class="notif-dropdown-header">
              <span class="notif-dropdown-title">Notifications</span>
              <button class="notif-mark-all-btn" id="notifMarkAllBtn">Mark all read</button>
            </div>
            <div class="notif-list" id="notifList">
              <div class="notif-empty">Loading…</div>
            </div>
            <div class="notif-dropdown-footer">
              <button class="notif-clear-btn" id="notifClearBtn">Clear all</button>
            </div>
          </div>
        </div>

        <!-- User chip -->
        <div class="nav-member-pill warm-user-chip" id="navMemberPill">
          <div class="nav-member-avatar warm-user-avatar">
            <?php if ($navPicture): ?>
              <img src="<?= $navPicture ?>" alt="<?= $initial ?>">
            <?php else: ?>
              <?= $initial ?>
            <?php endif; ?>
          </div>
          <span class="nav-member-name"><?= $dispName ?></span>
          <span class="nav-member-caret">▾</span>
        </div>
        <div class="nav-member-dropdown" id="navMemberDropdown">
          <button type="button" id="signOutBtn">Sign out</button>
        </div>
        <form id="signOutForm" method="POST" action="<?= BASE_URL ?>/member/logout" style="display:none;"></form>

      <?php else: ?>
        <a href="<?= BASE_URL ?>/member/login" class="nav-cta warm-nav-cta">Sign in</a>
      <?php endif; ?>

      <button class="menu-toggle" id="menuToggle" aria-label="Open menu"><i data-lucide="menu"></i></button>
    </div>
  </div>
</header>
