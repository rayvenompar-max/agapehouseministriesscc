<!DOCTYPE html>
<html lang="en">
<head>
<?php
  // ── Verse of the Day — computed once here so home.php and the JS global both use it ──
  $votd = \Service\VerseOfTheDayService::getToday();
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Agape House Ministries San Carlos — a digital home for the Gospel</title>

<!-- PWA — manifest & theme -------------------------------------------------->
<link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
<meta name="theme-color" content="#0A1B33">
<meta name="mobile-web-app-capable" content="yes">
<!-- iOS specific -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Agape House">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/images/icons/icon-192x192.png">
<!-- Windows tiles -->
<meta name="msapplication-TileImage" content="<?= BASE_URL ?>/public/images/icons/icon-144x144.png">
<meta name="msapplication-TileColor" content="#0A1B33">

<!-- SEO / social preview --------------------------------------------------->
<meta name="description" content="A digital home for the Gospel — sermons, articles, prayer, events and more from Agape House Ministries San Carlos.">
<meta property="og:title" content="Agape House Ministries San Carlos">
<meta property="og:description" content="A digital home for the Gospel — sermons, articles, prayer, events and more.">
<meta property="og:image" content="<?= BASE_URL ?>/public/images/agape1.jpg">
<meta property="og:type" content="website">

<!-- Fonts ------------------------------------------------------------------>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php $v = '2.0.' . filemtime(BASE_PATH . '/public/css/app.css'); ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css?v=<?= $v ?>">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>

<?php require_once BASE_PATH . '/views/partials/header.php'; ?>

<main>
<?php
$pages = ['home','watch','read','bible','quizzes','prayer','events','announcement','about','connect'];
foreach ($pages as $page) {
    require_once BASE_PATH . '/views/pages/' . $page . '.php';
}
?>
</main>

<?php require_once BASE_PATH . '/views/partials/footer.php'; ?>

<div class="dawn-rule"></div>
<!-- Sign-out confirmation modal (body-level so stacking context is clean) -->
<?php if (isset($memberAuth) && $memberAuth->isLoggedIn()): ?>
<div id="signOutModal" class="signout-modal" hidden>
  <div class="signout-backdrop" id="signOutBackdrop"></div>
  <div class="signout-box" role="dialog" aria-modal="true" aria-labelledby="signOutTitle">
    <div class="signout-icon">
      <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </div>
    <h3 id="signOutTitle">Signing out?</h3>
    <p>You'll need to sign back in to like, comment, or access your account.</p>
    <div class="signout-divider"></div>
    <div class="signout-actions">
      <button class="btn-so-stay" id="signOutCancel">Stay signed in</button>
      <button class="btn-so-confirm" id="signOutConfirm">Yes, sign out</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Member Profile Modal -->
<?php require_once BASE_PATH . '/views/partials/member_profile_modal.php'; ?>

<!-- Announcement Modals (body-level so they render outside .page stacking context) -->
<!-- ── Detail Modal ─────────────────────────────────────────────────────── -->
<div class="article-modal" id="annModal" hidden aria-modal="true" role="dialog" aria-labelledby="annModalTitle">
  <div class="article-modal-backdrop" id="annModalBackdrop"></div>
  <div class="article-modal-box">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta" id="annModalMeta"></div>
        <h2 class="article-modal-title" id="annModalTitle"></h2>
      </div>
      <button class="article-modal-close" id="annModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body" id="annModalBody"></div>
  </div>
</div>

<!-- ── Add Announcement Modal ─────────────────────────────────────────── -->
<div class="article-modal" id="addAnnModal" hidden aria-modal="true" role="dialog" aria-labelledby="addAnnModalHeading">
  <div class="article-modal-backdrop" id="addAnnModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Announcement</div>
        <h2 class="article-modal-title" id="addAnnModalHeading">New Announcement</h2>
      </div>
      <button class="article-modal-close" id="addAnnModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="addAnnForm" novalidate>
        <div class="form-group">
          <label for="addAnnTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="addAnnTitle" placeholder="Announcement headline" maxlength="255">
        </div>
        <div class="form-group">
          <label for="addAnnBody">Body <span class="form-hint">(required)</span></label>
          <textarea id="addAnnBody" rows="5" placeholder="Write the full announcement here…"></textarea>
        </div>
        <div class="form-group form-group--half">
          <div>
            <label for="addAnnCategory">Category</label>
            <select id="addAnnCategory">
              <option value="Ministry">Ministry</option>
              <option value="Events">Events</option>
              <option value="Community">Community</option>
              <option value="Urgent">Urgent</option>
            </select>
          </div>
          <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:var(--ink);">
              <input type="checkbox" id="addAnnPinned" style="width:auto;margin:0;">
              Pin to top
            </label>
          </div>
        </div>
        <div class="form-msg" id="addAnnMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn btn-primary" id="addAnnSubmitBtn">Post Announcement</button>
          <button type="button" class="btn btn-ghost-dark" id="addAnnCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Edit Announcement Modal ────────────────────────────────────────── -->
<div class="article-modal" id="editAnnModal" hidden aria-modal="true" role="dialog" aria-labelledby="editAnnModalHeading">
  <div class="article-modal-backdrop" id="editAnnModalBackdrop"></div>
  <div class="article-modal-box article-modal-box--form">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Announcement</div>
        <h2 class="article-modal-title" id="editAnnModalHeading">Edit Announcement</h2>
      </div>
      <button class="article-modal-close" id="editAnnModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <form id="editAnnForm" novalidate>
        <input type="hidden" id="editAnnId">
        <div class="form-group">
          <label for="editAnnTitle">Title <span class="form-hint">(required)</span></label>
          <input type="text" id="editAnnTitle" placeholder="Announcement headline" maxlength="255">
        </div>
        <div class="form-group">
          <label for="editAnnBody">Body <span class="form-hint">(required)</span></label>
          <textarea id="editAnnBody" rows="5" placeholder="Write the full announcement here…"></textarea>
        </div>
        <div class="form-group form-group--half">
          <div>
            <label for="editAnnCategory">Category</label>
            <select id="editAnnCategory">
              <option value="Ministry">Ministry</option>
              <option value="Events">Events</option>
              <option value="Community">Community</option>
              <option value="Urgent">Urgent</option>
            </select>
          </div>
          <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:var(--ink);">
              <input type="checkbox" id="editAnnPinned" style="width:auto;margin:0;">
              Pin to top
            </label>
          </div>
        </div>
        <div class="form-msg" id="editAnnMsg" hidden></div>
        <div style="display:flex;gap:12px;margin-top:24px;">
          <button type="submit" class="btn btn-primary" id="editAnnSubmitBtn">Save Changes</button>
          <button type="button" class="btn btn-ghost-dark" id="editAnnCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Post Pending Approval Modal ──────────────────────────────────────── -->
<div class="article-modal" id="pendingApprovalModal" hidden role="dialog" aria-modal="true" aria-labelledby="pendingApprovalTitle">
  <div class="article-modal-backdrop" id="pendingApprovalBackdrop"></div>
  <div class="article-modal-box pending-approval-modal">
    <div class="pending-approval-icon" aria-hidden="true"><i data-lucide="clock"></i></div>
    <h2 class="pending-approval-title" id="pendingApprovalTitle">Post Submitted!</h2>
    <p class="pending-approval-body" id="pendingApprovalBody">
      Your post has been received and is waiting for admin approval.<br>
      It will appear publicly once it's reviewed.
    </p>
    <button type="button" class="btn btn-primary pending-approval-ok" id="pendingApprovalOkBtn">Got it</button>
  </div>
</div>

<!-- Post Detail Modal (opened from notifications) -->
<div class="pdm-overlay" id="postDetailModal" hidden role="dialog" aria-modal="true" aria-labelledby="pdmTitle">
  <div class="pdm-backdrop" id="pdmBackdrop"></div>
  <div class="pdm-box">

    <!-- Header -->
    <div class="pdm-header">
      <div class="pdm-header-meta" id="pdmMeta"></div>
      <h2 class="pdm-header-title" id="pdmTitle"></h2>
      <button class="pdm-close" id="pdmClose" aria-label="Close">✕</button>
    </div>

    <!-- Content area (article body / video / announcement text) -->
    <div class="pdm-content" id="pdmContent"></div>

    <!-- Divider -->
    <div class="pdm-divider">
      <span class="pdm-divider-label">Comments</span>
    </div>

    <!-- Comment list -->
    <div class="pdm-comment-list" id="pdmCommentList">
      <div class="comment-loading"><span class="feed-spinner"></span> Loading…</div>
    </div>

    <!-- Comment form -->
    <div class="pdm-comment-form-wrap" id="pdmCommentFormWrap"></div>

  </div>
</div>

<script>window.APP_BASE_URL = '<?= BASE_URL ?>';</script>
<script>window.VERSE_OF_THE_DAY = <?= \Service\VerseOfTheDayService::getTodayJson() ?>;</script>
<script>
window.CURRENT_MEMBER = <?= json_encode(
    $memberAuth->isLoggedIn() ? $memberAuth->current() : null,
    JSON_HEX_TAG | JSON_HEX_AMP
) ?>;
</script>
<?php $jsv = '2.0.' . filemtime(BASE_PATH . '/public/js/app.js'); ?>
<script src="<?= BASE_URL ?>/public/js/app.js?v=<?= $jsv ?>"></script>
<script>
  // Initialise Lucide icons after app.js has run
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
    // Re-run after any dynamic DOM updates (SPA page switches etc.)
    document.addEventListener('lucide:reinit', function() { lucide.createIcons(); });
  }
</script>

<!-- PWA — Service Worker registration --------------------------------------->
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/DigitalEvangelization/sw.js', {
        scope: '/DigitalEvangelization/'
      })
      .then(reg => console.log('[SW] Registered, scope:', reg.scope))
      .catch(err => console.warn('[SW] Registration failed:', err));
    });
  }
</script>

<!-- PWA — Install prompt banner --------------------------------------------->
<div id="pwaInstallBanner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;
  background:#0A1B33;border-top:1px solid rgba(127,196,232,.25);
  padding:14px 20px;align-items:center;gap:12px;flex-wrap:wrap;">
  <img src="/DigitalEvangelization/public/images/agape1.jpg" alt="Agape House"
       style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
  <div style="flex:1;min-width:0;">
    <div style="font-size:13px;font-weight:600;color:#fff;font-family:'Work Sans',sans-serif;">
      Add Agape House to your Home Screen
    </div>
    <div style="font-size:12px;color:#8FA9C4;font-family:'Work Sans',sans-serif;margin-top:2px;">
      Access sermons, prayer &amp; events instantly — works offline too.
    </div>
  </div>
  <button id="pwaInstallBtn" style="background:linear-gradient(90deg,#3E7CB1,#7FC4E8);
    color:#fff;border:none;padding:9px 18px;border-radius:100px;
    font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;">
    Install
  </button>
  <button id="pwaInstallDismiss" style="background:none;border:none;color:#8FA9C4;
    font-size:20px;cursor:pointer;padding:0 4px;flex-shrink:0;" aria-label="Dismiss">
    &times;
  </button>
</div>

<script>
  (function () {
    var deferredPrompt = null;
    var banner         = document.getElementById('pwaInstallBanner');
    var installBtn     = document.getElementById('pwaInstallBtn');
    var dismissBtn     = document.getElementById('pwaInstallDismiss');

    // Don't show if already running as installed app or user already dismissed
    if (window.matchMedia('(display-mode: standalone)').matches) return;
    if (localStorage.getItem('pwaInstallDismissed')) return;

    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      deferredPrompt = e;
      banner.style.display = 'flex';
    });

    installBtn && installBtn.addEventListener('click', function () {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(function (choice) {
        if (choice.outcome === 'accepted') {
          console.log('[PWA] User accepted install');
        }
        deferredPrompt = null;
        banner.style.display = 'none';
      });
    });

    dismissBtn && dismissBtn.addEventListener('click', function () {
      banner.style.display = 'none';
      localStorage.setItem('pwaInstallDismissed', '1');
    });

    window.addEventListener('appinstalled', function () {
      banner.style.display = 'none';
      deferredPrompt = null;
    });
  })();
</script>
</body>
</html>
