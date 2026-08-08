<section class="page" id="page-connect">

  <!-- ── Hero ──────────────────────────────────────────────────────────────── -->
  <div class="connect-hero">
    <div class="connect-spark-wrap" aria-hidden="true">
      <div class="connect-spark-ring"></div>
      <div class="connect-spark-ring connect-spark-ring--delay"></div>
      <div class="connect-spark"></div>
    </div>
    <div class="connect-hero-content">
      <p class="connect-hero-eyebrow">Connect</p>
      <h1 class="connect-hero-title">Talk to a <em>real person.</em></h1>
      <p class="connect-hero-lede">Questions about faith, prayer needs, or just want to say hello — we read every message.</p>
    </div>
  </div>

  <!-- ── Body ──────────────────────────────────────────────────────────────── -->
  <div class="connect-wrap">

    <!-- Contact form -->
    <div class="connect-form-card">
      <h2>Send a message</h2>
      <?php
      // Show member info if logged in
      $isMemberLoggedIn = isset($memberAuth) && $memberAuth->isLoggedIn();
      if ($isMemberLoggedIn):
        $member      = $memberAuth->current();
        $displayName = htmlspecialchars($member['display_name'] ?? $member['username'] ?? 'Member');
        $email       = htmlspecialchars($member['email'] ?? '');
        $initial     = strtoupper(mb_substr($member['display_name'] ?? $member['username'] ?? 'M', 0, 1));
        $profilePic  = !empty($member['profile_picture']) ? htmlspecialchars($member['profile_picture']) : null;
      ?>
      <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--paper,#f5f5f0);border-radius:10px;margin-bottom:20px;">
        <div style="width:42px;height:42px;border-radius:50%;background:var(--horizon,#e07b3a);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:600;flex-shrink:0;<?= $profilePic ? 'background:none;' : '' ?>">
          <?php if ($profilePic): ?>
            <img src="<?= $profilePic ?>" alt="<?= $initial ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
          <?php else: ?>
            <?= $initial ?>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;font-size:15px;color:var(--night,#1a1a1a);"><?= $displayName ?></div>
          <div style="font-size:13px;color:var(--ink-soft,#888);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= $email ?></div>
        </div>
      </div>
      <?php endif; ?>

      <form id="contactForm" novalidate>
        <?php if (!$isMemberLoggedIn): ?>
        <div class="field-row">
          <div class="field">
            <label for="cname">Name <span class="req">*</span></label>
            <input id="cname" type="text" placeholder="Your name" maxlength="120" autocomplete="name">
            <span class="field-error" id="cnameError"></span>
          </div>
          <div class="field">
            <label for="cemail">Email <span class="req">*</span></label>
            <input id="cemail" type="email" placeholder="you@example.com" maxlength="160" autocomplete="email">
            <span class="field-error" id="cemailError"></span>
          </div>
        </div>
        <?php endif; ?>
        <div class="field">
          <label for="creason">Reason for reaching out</label>
          <div class="connect-select-wrap">
            <select id="creason">
              <option value="Just saying hi">Just saying hi</option>
              <option value="Prayer request">Prayer request</option>
              <option value="Questions about faith">Questions about faith</option>
              <option value="Volunteering">Volunteering</option>
              <option value="Technical issue">Technical issue</option>
            </select>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
        </div>
        <div class="field">
          <label for="cmsg">
            Message <span class="req">*</span>
            <span class="connect-count" id="cmsgCount">0 / 3000</span>
          </label>
          <textarea id="cmsg" placeholder="Write your message here…" rows="5" maxlength="3000"></textarea>
          <span class="field-error" id="cmsgError"></span>
        </div>

        <div id="contactMsg" class="form-msg" hidden></div>

        <button type="submit" class="connect-btn-send" id="contactSubmitBtn">Send message</button>
      </form>
    </div>

    <!-- Right column -->
    <div class="connect-side">

      <div class="connect-side-block">
        <h3>Find us elsewhere</h3>
        <p>Also follow us — we post daily on every major platform.</p>
        <div class="connect-platform-pills">
          <a class="connect-platform-pill" href="https://youtube.com" target="_blank" rel="noopener">YouTube</a>
          <a class="connect-platform-pill" href="https://tiktok.com" target="_blank" rel="noopener">TikTok</a>
          <a class="connect-platform-pill" href="https://www.facebook.com/agapnistries" target="_blank" rel="noopener">Facebook</a>
        </div>
      </div>

      <div class="connect-side-block">
        <h3>Need to talk now?</h3>
        <p>Our care team is available for urgent prayer conversations.</p>
        <button class="connect-btn-livechat" id="liveChatBtn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Start a live chat
        </button>
      </div>

      <div class="connect-support-card">
        <p class="connect-support-eyebrow">Support the Mission</p>
        <p>Every gift helps us keep every message free and reach more people around the world.</p>
        <button class="connect-btn-give" id="openDonateModalBtn">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Give now
        </button>
      </div>

    </div>
  </div>

</section>

<!-- ── Donation / Give Modal ─────────────────────────────────────────────── -->
<div class="article-modal" id="donateModal" role="dialog" aria-modal="true" aria-label="Give / Donate" hidden>
  <div class="article-modal-backdrop" id="donateModalBackdrop"></div>
  <div class="article-modal-box donate-modal-box">
    <div class="article-modal-header">
      <div>
        <div class="article-modal-meta">Support the Mission</div>
        <h2 class="article-modal-title">Give / Donate</h2>
      </div>
      <button class="article-modal-close" id="donateModalClose" aria-label="Close">✕</button>
    </div>
    <div class="article-modal-body">
      <p style="font-size:15px; color:var(--ink-soft); margin-bottom:28px; line-height:1.6;">
        Every gift helps us keep every message free and reach more people around the world.
        Scan any of the QR codes below to give through your preferred e-wallet.
      </p>

      <blockquote class="donate-verse">
        <p>"Each one must give as he has decided in his heart, not reluctantly or under compulsion, for God loves a cheerful giver."</p>
        <cite>— 2 Corinthians 9:7</cite>
      </blockquote>

      <div class="donate-wallet-grid">

        <!-- GCash -->
        <div class="donate-wallet-card">
          <div class="donate-wallet-logo donate-wallet-logo--gcash">GCash</div>
          <div class="donate-qr-wrap">
            <img src="/DigitalEvangelization/public/images/gcash.jpg"
                 alt="GCash QR Code"
                 class="donate-qr-img"
                 onerror="this.closest('.donate-qr-wrap').innerHTML='<div class=\'donate-qr-placeholder\'>QR code<br>coming soon</div>'">
          </div>
          <div class="donate-wallet-name">Agape House Ministries</div>
          <div class="donate-wallet-number">0975 150 3967</div>
        </div>

        <!-- Maya (PayMaya) -->
        <div class="donate-wallet-card">
          <div class="donate-wallet-logo donate-wallet-logo--maya">Maya</div>
          <div class="donate-qr-wrap">
            <img src="/DigitalEvangelization/public/images/qr-maya.png"
                 alt="Maya QR Code"
                 class="donate-qr-img"
                 onerror="this.closest('.donate-qr-wrap').innerHTML='<div class=\'donate-qr-placeholder\'>QR code<br>coming soon</div>'">
          </div>
          <div class="donate-wallet-name">Agape House Ministries</div>
          <div class="donate-wallet-number">0951 588 2518</div>
        </div>

      </div>

      <p style="font-size:13px; color:var(--ink-soft); text-align:center; margin-top:24px; line-height:1.6;">
        May God bless you for your generosity.<br>
        For other ways to give, please contact us through the form above.
      </p>
    </div>
  </div>
</div>
