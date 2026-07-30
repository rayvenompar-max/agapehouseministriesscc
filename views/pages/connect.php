<section class="page" id="page-connect">

  <!-- ── Page hero ─────────────────────────────────────────────────────────── -->
  <div class="page-hero connect-hero">
    <div class="page-hero-inner">
      <div class="eyebrow connect-hero-eyebrow">Connect</div>
      <h2 class="connect-hero-title">Talk to a <em>real person</em></h2>
      <p class="lede connect-hero-lede">Questions about faith, prayer needs, or just want to say hello — we read every message.</p>
    </div>

    <!-- Sun disc + rays -->
    <div class="page-hero-horizon-stage" aria-hidden="true">
      <div class="sun-wrap">
        <div class="sun-ray sun-ray--r1"></div>
        <div class="sun-ray sun-ray--r2"></div>
        <div class="sun-ray sun-ray--r3"></div>
        <div class="sun-ray sun-ray--r4"></div>
        <div class="sun-ray sun-ray--r5"></div>
        <div class="sun-ray sun-ray--r6"></div>
        <div class="sun-ray sun-ray--r7"></div>
        <div class="sun-ray sun-ray--r8"></div>
        <div class="sun-ray sun-ray--r9"></div>
        <div class="sun-disc"></div>
      </div>
    </div>
  </div>

  <div class="section-wrap" style="padding-top:44px; padding-bottom:80px;">
    <div class="connect-layout">

      <!-- Contact form -->
      <div class="form-card" style="position:static;">
        <h3 style="font-family:var(--display); margin-bottom:20px; color:var(--ink);">Send a message</h3>
        <form id="contactForm" novalidate>
          <div class="field-row">
            <div class="field">
              <label for="cname">Name <span style="color:var(--horizon);">*</span></label>
              <input id="cname" type="text" placeholder="Your name" maxlength="120" autocomplete="name">
              <span class="field-error" id="cnameError"></span>
            </div>
            <div class="field">
              <label for="cemail">Email <span style="color:var(--horizon);">*</span></label>
              <input id="cemail" type="email" placeholder="you@example.com" maxlength="160" autocomplete="email">
              <span class="field-error" id="cemailError"></span>
            </div>
          </div>
          <div class="field">
            <label for="creason">Reason for reaching out</label>
            <select id="creason">
              <option value="Just saying hi">Just saying hi</option>
              <option value="Prayer request">Prayer request</option>
              <option value="Questions about faith">Questions about faith</option>
              <option value="Volunteering">Volunteering</option>
              <option value="Technical issue">Technical issue</option>
            </select>
          </div>
          <div class="field">
            <label for="cmsg">
              Message <span style="color:var(--horizon);">*</span>
              <span class="form-hint" id="cmsgCount" style="font-weight:400; color:var(--ink-soft);">0 / 3000</span>
            </label>
            <textarea id="cmsg" placeholder="Write your message here…" rows="5" maxlength="3000"></textarea>
            <span class="field-error" id="cmsgError"></span>
          </div>

          <div id="contactMsg" class="form-msg" hidden></div>

          <button type="submit" class="submit-btn" id="contactSubmitBtn">Send message</button>
        </form>
      </div>

      <!-- Social & chat -->
      <div>
        <h3 style="font-family:var(--display); margin-bottom:12px; color:var(--ink);">Find us elsewhere</h3>
        <p style="font-size:15px;">Also follow us — we post daily on every major platform.</p>
        <div class="social-row">
          
          <a class="social-chip" href="https://youtube.com" target="_blank" rel="noopener">YouTube</a>
          <a class="social-chip" href="https://tiktok.com" target="_blank" rel="noopener">TikTok</a>
          <a class="social-chip" href="https://www.facebook.com/agapnistries" target="_blank" rel="noopener">Facebook</a>
         
        </div>

        <h3 style="font-family:var(--display); margin-top:40px; margin-bottom:12px; color:var(--ink);">Need to talk now?</h3>
        <p style="font-size:15px; margin-bottom:16px;">Our care team is available for urgent prayer conversations.</p>
        <button class="btn btn-dark" id="liveChatBtn">Start a live chat</button>

        <!-- Donation nudge -->
        <div style="margin-top:40px; padding:24px; background:var(--night); border-radius:4px; border:1px solid rgba(127,196,232,.15);">
          <div class="eyebrow" style="margin-bottom:10px;">Support the mission</div>
          <p style="color:#C6D9EA; font-size:14px; line-height:1.6; margin-bottom:16px;">
            Every gift helps us keep every message free and reach more people around the world.
          </p>
          <button class="btn btn-primary" id="openDonateModalBtn">Give today</button>
        </div>
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
            <img src="/DigitalEvangelization/public/images/qr-gcash.png"
                 alt="GCash QR Code"
                 class="donate-qr-img"
                 onerror="this.closest('.donate-qr-wrap').innerHTML='<div class=\'donate-qr-placeholder\'>QR code<br>coming soon</div>'">
          </div>
          <div class="donate-wallet-name">Agape House Ministries</div>
          <div class="donate-wallet-number">09XX XXX XXXX</div>
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
          <div class="donate-wallet-number">09XX XXX XXXX</div>
        </div>

      </div>

      <p style="font-size:13px; color:var(--ink-soft); text-align:center; margin-top:24px; line-height:1.6;">
        May God bless you for your generosity. 💛<br>
        For other ways to give, please contact us through the form above.
      </p>
    </div>
  </div>
</div>
