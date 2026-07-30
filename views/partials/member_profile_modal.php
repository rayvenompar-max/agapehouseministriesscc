<!-- ── Member Profile Modal ──────────────────────────────────────────────── -->
<div class="member-profile-modal" id="memberProfileModal" role="dialog" aria-modal="true" aria-label="Member profile" hidden>
  <div class="member-profile-modal-backdrop" id="memberProfileModalBackdrop"></div>
  <div class="member-profile-modal-box">
    
    <!-- Header with avatar and name -->
    <div class="member-profile-modal-header">
      <div class="mpm-hero">
        <div class="mpm-avatar-wrap">
          <div class="mpm-avatar" id="mpmAvatar">?</div>
        </div>
        <div class="mpm-name" id="mpmName">Loading...</div>
        <div class="mpm-username" id="mpmUsername"></div>
        <div class="mpm-since" id="mpmSince"></div>
        <div class="mpm-badge">
          <span class="mpm-badge-dot"></span>Agape House Member
        </div>
      </div>
      <button class="member-profile-modal-close" id="memberProfileModalClose" aria-label="Close profile">✕</button>
    </div>

    <!-- Body with profile info -->
    <div class="member-profile-modal-body">
      <div class="mpm-section-title">Profile info</div>
      
      <div class="mpm-info-row">
        <span class="mpm-info-icon"><i data-lucide="user"></i></span>
        <div>
          <div class="mpm-info-label">Display name</div>
          <div class="mpm-info-value" id="mpmDisplayName">—</div>
        </div>
      </div>

      <div class="mpm-info-row">
        <span class="mpm-info-icon"><i data-lucide="at-sign"></i></span>
        <div>
          <div class="mpm-info-label">Username</div>
          <div class="mpm-info-value" id="mpmUsernameVal">—</div>
        </div>
      </div>

      <div class="mpm-info-row">
        <span class="mpm-info-icon"><i data-lucide="calendar"></i></span>
        <div>
          <div class="mpm-info-label">Member since</div>
          <div class="mpm-info-value" id="mpmMemberSince">—</div>
        </div>
      </div>

      <!-- Follow stats + button -->
      <div class="mpm-follow-stats">
        <div class="mpm-follow-stat">
          <span id="mpmFollowingCount">—</span>
          <small>Following</small>
        </div>
        <div class="mpm-follow-stat-div"></div>
        <div class="mpm-follow-stat">
          <span id="mpmFollowerCount">—</span>
          <small>Followers</small>
        </div>
      </div>

      <button class="mpm-follow-btn" id="mpmFollowBtn" hidden>Follow</button>
    </div>

  </div>
</div>
