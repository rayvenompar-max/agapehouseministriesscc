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
      <div class="mpm-section-title">Profile Info</div>
      
      <div class="mpm-info-row">
        <span class="mpm-info-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </span>
        <div>
          <div class="mpm-info-label">Display Name</div>
          <div class="mpm-info-value" id="mpmDisplayName">—</div>
        </div>
      </div>

      <div class="mpm-info-row">
        <span class="mpm-info-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M16 8a4 4 0 1 0-1.17 6.83c.36.36.94.36 1.3 0M16 8v3a2.5 2.5 0 0 0 5 0V12a9 9 0 1 0-4.5 7.79"/>
          </svg>
        </span>
        <div>
          <div class="mpm-info-label">Username</div>
          <div class="mpm-info-value" id="mpmUsernameVal">—</div>
        </div>
      </div>

      <div class="mpm-info-row">
        <span class="mpm-info-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
          </svg>
        </span>
        <div>
          <div class="mpm-info-label">Member Since</div>
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

      <div class="mpm-action-buttons">
        <button class="mpm-follow-btn" id="mpmFollowBtn" hidden>Follow</button>
        <button class="mpm-message-btn" id="mpmMessageBtn" hidden>Message</button>
      </div>
    </div>

  </div>
</div>
