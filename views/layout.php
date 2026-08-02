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

<?php if (isset($memberAuth) && $memberAuth->isLoggedIn()): ?>
<!-- Member Live Chat Modal (opened from contact_reply notifications) -->
<div class="member-chat-overlay" id="memberChatModal" hidden role="dialog" aria-modal="true" aria-labelledby="memberChatTitle">
  <div class="member-chat-backdrop" id="memberChatBackdrop"></div>
  <div class="member-chat-box">
    <!-- Header -->
    <div class="member-chat-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <img src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House" class="member-chat-logo">
        <div>
          <h2 class="member-chat-title" id="memberChatTitle">Your Conversation</h2>
          <p class="member-chat-meta" id="memberChatMeta"></p>
        </div>
      </div>
      <button class="member-chat-close" id="memberChatClose" aria-label="Close">✕</button>
    </div>
    <!-- Original message -->
    <div class="member-chat-original" id="memberChatOriginal"></div>
    <!-- Thread -->
    <div class="member-chat-thread" id="memberChatThread"></div>
    <!-- Input -->
    <div class="member-chat-footer">
      <div class="member-chat-input-wrap">
        <textarea class="member-chat-input" id="memberChatInput"
          rows="2" placeholder="Write a message..." maxlength="3000"></textarea>
        <button class="member-chat-send" id="memberChatSend" aria-label="Send message">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </div>
      <span class="member-chat-msg" id="memberChatMsg"></span>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── My Prayer Requests Drawer (global — available on every page) ────────── -->
<?php if (isset($memberAuth) && $memberAuth->isLoggedIn()):
  $__m        = $memberAuth->current();
  $__initial  = strtoupper(mb_substr($__m['display_name'] ?? $__m['username'] ?? 'M', 0, 1));
  $__name     = htmlspecialchars($__m['display_name'] ?? $__m['username'] ?? 'Member');
  if (!array_key_exists('profile_picture', $__m)) {
    $__rec = (new \Repository\MemberRepository(getDB()))->findById((int)$__m['id']);
    $_SESSION['member']['profile_picture'] = $__rec?->profilePicture;
    $__m['profile_picture'] = $__rec?->profilePicture;
  }
  $__pic = !empty($__m['profile_picture']) ? htmlspecialchars($__m['profile_picture']) : null;
?>
<div id="prayerDrawer" class="prayer-drawer" hidden>
  <div class="prayer-drawer-backdrop"></div>
  <div class="prayer-drawer-panel">
    <div class="prayer-drawer-header">
      <div class="prayer-drawer-header-title">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        <div>
          <h3 class="prayer-drawer-title">My Prayer Requests</h3>
          <div class="prayer-drawer-subtitle" id="prayerDrawerSubtitle">0 total</div>
        </div>
      </div>
      <button class="prayer-drawer-close" id="prayerDrawerClose" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="prayer-drawer-form">
      <div class="prayer-field">
        <label for="drawerPcat">Category</label>
        <div class="prayer-field-select-wrap">
          <select id="drawerPcat">
            <option>Healing</option>
            <option>Family</option>
            <option>Guidance</option>
            <option>Provision</option>
            <option>Thanksgiving</option>
          </select>
          <svg class="select-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>
      <div class="prayer-field">
        <label for="drawerPreq">
          Your request
          <span class="char-counter" id="drawerPreqCounter">0/1000</span>
        </label>
        <textarea id="drawerPreq" rows="4" maxlength="1000"
          placeholder="Share your prayer need… (10 characters minimum)"></textarea>
      </div>
      <p class="prayer-char-hint" id="drawerPreqHint">Minimum 10 characters to post.</p>
      <div class="prayer-anon-row">
        <div class="prayer-anon-label">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <div>
            <strong>Post anonymously</strong>
            <span>Your name stays hidden from other members</span>
          </div>
        </div>
        <label class="prayer-toggle" aria-label="Post anonymously">
          <input type="checkbox" id="drawerAnonToggle">
          <span class="prayer-toggle-track"></span>
        </label>
      </div>
      <button class="prayer-drawer-submit-btn" id="drawerPrayerSubmitBtn" disabled>Post to the wall</button>
      <p id="drawerPrayerMsg" class="prayer-drawer-form-note" style="display:none;"></p>
    </div>
    <div class="prayer-drawer-list" id="myPrayerList">
      <div class="prayer-list-head">
        <span class="prayer-list-label">Your submitted requests</span>
        <span class="prayer-list-count" id="myPrayerListCount">0</span>
      </div>
      <p class="prayer-drawer-empty">You haven't submitted any requests yet.</p>
    </div>
  </div>
</div>

<!-- ── My Profile Drawer (global — available on every page) ────────────────── -->
<div id="profileDrawer" class="profile-drawer" hidden>
  <div class="profile-drawer-backdrop"></div>
  <div class="profile-drawer-panel">
    <div class="profile-drawer-header">
      <div class="profile-drawer-header-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <h3 class="profile-drawer-title">My Profile</h3>
      </div>
      <button class="profile-drawer-close" id="profileDrawerClose" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="profile-drawer-body">
      <div class="pd-hero">
        <div class="pd-avatar-wrap">
          <div class="pd-avatar" id="pdAvatar">
            <?php if ($__pic): ?>
              <img src="<?= $__pic ?>" alt="<?= $__initial ?>">
            <?php else: ?>
              <?= $__initial ?>
            <?php endif; ?>
          </div>
          <label class="pd-avatar-upload-btn" for="pdAvatarInput" title="Change photo" aria-label="Upload profile picture">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
          </label>
          <input type="file" id="pdAvatarInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">
        </div>
        <div class="pd-hero-info">
          <div class="pd-display-name" id="pdDisplayName"><?= $__name ?></div>
          <div class="pd-username" id="pdUsername"></div>
          <div class="pd-badge">
            <span class="sidebar-role-dot"></span>Member · Agape House
          </div>
        </div>
      </div>
      <div class="pd-info-grid">
        <div class="pd-info-row">
          <span class="pd-info-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </span>
          <div>
            <div class="pd-info-label">Email</div>
            <div class="pd-info-value" id="pdEmail">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2M15 12h2M7 16h10"/></svg>
          </span>
          <div>
            <div class="pd-info-label">Username</div>
            <div class="pd-info-value" id="pdUsernameVal">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </span>
          <div>
            <div class="pd-info-label">Member since</div>
            <div class="pd-info-value" id="pdMemberSince">—</div>
          </div>
        </div>
        <div class="pd-info-row">
          <span class="pd-info-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </span>
          <div>
            <div class="pd-info-label">Last login</div>
            <div class="pd-info-value" id="pdLastLogin">—</div>
          </div>
        </div>
      </div>
      <p class="pd-section-label">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
        Edit Profile
      </p>
      <form id="pdEditForm" novalidate autocomplete="off">
        <div class="pd-field-group">
          <label class="pd-field-label" for="pdNameInput">Display Name</label>
          <input type="text" id="pdNameInput" maxlength="120" placeholder="Your display name" autocomplete="off">
        </div>
        <div class="pd-field-group">
          <label class="pd-field-label" for="pdUsernameInput">Username</label>
          <div class="pd-input-prefix-wrap">
            <span class="pd-input-prefix">@</span>
            <input type="text" id="pdUsernameInput" maxlength="60" placeholder="yourhandle" autocomplete="off" spellcheck="false">
          </div>
        </div>
        <div class="pd-field-group">
          <label class="pd-field-label" for="pdEmailInput">Email</label>
          <input type="email" id="pdEmailInput" maxlength="160" placeholder="you@example.com" autocomplete="off">
        </div>
        <button type="button" class="pd-pw-toggle" id="pdPwToggle">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          Change Password
          <span class="pd-pw-note">(leave blank to keep current)</span>
        </button>
        <div class="pd-pw-fields" id="pdPwFields">
          <div class="pd-field-group">
            <label class="pd-field-label" for="pdCurrentPass">Current Password</label>
            <input type="password" id="pdCurrentPass" maxlength="255" placeholder="Required to save any changes" autocomplete="current-password">
          </div>
          <div class="pd-field-group">
            <label class="pd-field-label" for="pdNewPass">New Password</label>
            <input type="password" id="pdNewPass" maxlength="255" placeholder="Min 8 characters" autocomplete="new-password">
            <div class="pd-pass-strength" id="pdPassStrength" hidden>
              <div class="pd-pass-bar"><div class="pd-pass-fill" id="pdPassFill"></div></div>
              <span class="pd-pass-label" id="pdPassLabel"></span>
            </div>
          </div>
          <div class="pd-field-group">
            <label class="pd-field-label" for="pdConfirmPass">Confirm New Password</label>
            <input type="password" id="pdConfirmPass" maxlength="255" placeholder="Repeat new password" autocomplete="new-password">
          </div>
        </div>
        <p id="pdSaveMsg" class="pd-save-msg" style="display:none;"></p>
        <button type="submit" class="pd-save-btn" id="pdSaveBtn">Save changes</button>
      </form>
      <div class="pd-divider" style="margin-top:24px;"></div>
      <div class="pd-section-title" style="margin-bottom:12px;">Activity</div>
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
      <div class="pd-signout-wrap">
        <button type="button" class="pd-signout-btn" id="pdSignOutBtn">Sign out of Agape House</button>
      </div>
    </div><!-- /.profile-drawer-body -->
  </div>
</div>
<?php endif; ?>

<!-- ── Comment Drawer (global — available on every page) ───────────────────── -->
<div id="commentDrawer" class="comment-drawer" hidden>
  <div class="comment-drawer-backdrop"></div>
  <div class="comment-drawer-panel">
    <div class="comment-drawer-header">
      <h3 class="comment-drawer-title">Comments</h3>
      <button class="comment-drawer-close" id="commentDrawerClose" aria-label="Close">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
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
