<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — <?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500;1,9..144,600&family=Source+Sans+3:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --plum:#332039; --plum-deep:#20142A;
      --ember:#C1542E; --coral:#E08152; --gold:#D9A544;
      --sage:#6E8F6E;  --cream:#FBF6EC; --paper:#FFFDF8;
      --ink:#241C1F;   --ink-soft:#6B6058; --line:#E9E1D2;
    }
    *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
    html,body { height:100%; }
    body {
      font-family: 'Source Sans 3', sans-serif;
      color: var(--ink);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
      position: relative;
      overflow: hidden;
      background: var(--plum-deep);
      -webkit-font-smoothing: antialiased;
    }
    /* ── Dawn sky background ── */
    .sky {
      position: fixed; inset: 0;
      background:
        radial-gradient(ellipse 70% 55% at 50% 118%, rgba(217,165,68,.55), transparent 60%),
        radial-gradient(ellipse 90% 60% at 50% 105%, rgba(224,129,82,.45), transparent 65%),
        linear-gradient(180deg, var(--plum-deep) 0%, var(--plum) 45%, #4A2A3C 72%, #6E3A34 100%);
      z-index: 0;
    }
    .sun {
      position: fixed; left: 50%; bottom: -180px;
      transform: translateX(-50%);
      width: 520px; height: 520px; border-radius: 50%;
      background: radial-gradient(circle, rgba(217,165,68,.85) 0%, rgba(224,129,82,.35) 45%, transparent 72%);
      filter: blur(6px);
      animation: breathe 7s ease-in-out infinite;
      z-index: 0;
    }
    @keyframes breathe {
      0%,100% { opacity:.75; transform:translateX(-50%) scale(1); }
      50%      { opacity:1;   transform:translateX(-50%) scale(1.06); }
    }
    .rays {
      position: fixed; left: 50%; bottom: -40px;
      transform: translateX(-50%);
      width: 900px; height: 900px;
      z-index: 0; opacity: .14;
      animation: spin 120s linear infinite;
    }
    @keyframes spin { to { transform: translateX(-50%) rotate(360deg); } }
    .stars {
      position: fixed; inset: 0; z-index: 0;
      background-image:
        radial-gradient(1.5px 1.5px at 20% 20%,  rgba(255,255,255,.5),  transparent),
        radial-gradient(1.5px 1.5px at 75% 12%,  rgba(255,255,255,.4),  transparent),
        radial-gradient(1px   1px   at 60% 30%,  rgba(255,255,255,.35), transparent),
        radial-gradient(1.5px 1.5px at 85% 25%,  rgba(255,255,255,.3),  transparent),
        radial-gradient(1px   1px   at 35% 8%,   rgba(255,255,255,.4),  transparent),
        radial-gradient(1.5px 1.5px at 10% 35%,  rgba(255,255,255,.25), transparent);
      background-repeat: no-repeat;
    }
    /* ── Top brand / back link ── */
    .top-brand {
      position: fixed; top: 32px; left: 40px; z-index: 2;
      display: flex; align-items: center; gap: 11px;
    }
    .top-brand .glyph {
      width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
      box-shadow: 0 0 0 2px rgba(255,255,255,.25);
    }
    .top-brand .glyph img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .top-brand .name  { font-family: 'Fraunces', serif; font-weight: 600; font-size: 15.5px; color: #FBF6EC; line-height: 1.2; }
    .top-brand .loc   { font-family: 'IBM Plex Mono', monospace; font-size: 9px; letter-spacing: .14em; text-transform: uppercase; color: rgba(251,246,236,.5); }
    .back-link {
      position: fixed; top: 36px; right: 40px; z-index: 2;
      font-family: 'IBM Plex Mono', monospace; font-size: 11px; letter-spacing: .03em;
      color: rgba(251,246,236,.65); text-decoration: none;
    }
    .back-link:hover { color: #fff; }

    /* ── Stage ── */
    .stage {
      position: relative; z-index: 2;
      display: flex; flex-direction: column; align-items: center; width: 100%;
    }
    .verse {
      text-align: center; max-width: 440px; margin-bottom: 28px;
      animation: fade-down .7s cubic-bezier(.16,1,.3,1);
    }
    @keyframes fade-down {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .verse .eyebrow {
      font-family: 'IBM Plex Mono', monospace; font-size: 10px;
      letter-spacing: .2em; text-transform: uppercase; color: var(--gold); margin: 0 0 10px;
    }
    .verse h1 {
      font-family: 'Fraunces', serif; font-weight: 500; font-size: 30px;
      line-height: 1.22; color: #FBF6EC; margin: 0;
    }
    .verse h1 em { font-style: italic; font-weight: 600; color: var(--gold); }
    /* ── Card ── */
    .card {
      width: 100%; max-width: 404px;
      background: var(--paper); border-radius: 20px; padding: 38px 38px 30px;
      box-shadow: 0 30px 70px rgba(20,10,20,.45), 0 2px 0 rgba(255,255,255,.6) inset;
      animation: rise .7s cubic-bezier(.16,1,.3,1) .1s backwards;
      position: relative;
    }
    @keyframes rise {
      from { opacity: 0; transform: translateY(24px) scale(.98); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .seal-float {
      width: 64px; height: 64px; border-radius: 50%;
      margin: -68px auto 18px;
      background: var(--paper);
      box-shadow: 0 10px 24px rgba(20,10,20,.3), 0 0 0 5px var(--paper);
      display: flex; align-items: center; justify-content: center;
      position: relative; z-index: 3; overflow: hidden;
    }
    .seal-float img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

    /* ── Tabs ── */
    .tabs {
      display: flex; gap: 22px; justify-content: center;
      margin-bottom: 26px; border-bottom: 1.5px solid var(--line);
    }
    .tabs button {
      background: none; border: none; padding: 0 2px 12px;
      font-family: 'Source Sans 3', sans-serif; font-weight: 700; font-size: 14px;
      color: var(--ink-soft); cursor: pointer; position: relative; transition: color .2s;
    }
    .tabs button.active { color: var(--ink); }
    .tabs button::after {
      content: ""; position: absolute; left: 0; right: 0; bottom: -1.5px;
      height: 2px; background: var(--ember);
      transform: scaleX(0); transform-origin: center;
      transition: transform .28s cubic-bezier(.65,0,.35,1);
    }
    .tabs button.active::after { transform: scaleX(1); }

    /* ── Card head ── */
    .card-head { text-align: center; margin-bottom: 24px; }
    .card-head h2 { font-family: 'Fraunces', serif; font-weight: 500; font-size: 22px; margin: 0 0 5px; }
    .card-head p  { margin: 0; font-size: 13.5px; color: var(--ink-soft); }

    /* ── Alert ── */
    .auth-alert { padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 8px; }
    .auth-alert--error   { background: #fee2e2; color: #7f1d1d; border: 1px solid #fca5a5; }
    .auth-alert--success { background: #dcfce7; color: #14532d; border: 1px solid #86efac; }
    /* ── Fields ── */
    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: 12px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
    .field-hint  { font-size: 11px; color: var(--ink-soft); margin-top: 3px; }
    .input-wrap  { position: relative; }
    .input-wrap input {
      width: 100%; padding: 12px 14px;
      font-family: 'Source Sans 3', sans-serif; font-size: 14px; color: var(--ink);
      background: #FDFBF6; border: 1.5px solid var(--line); border-radius: 10px;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .input-wrap input::placeholder { color: #C4B8A6; }
    .input-wrap input:focus { border-color: var(--ember); background: #fff; box-shadow: 0 0 0 4px rgba(193,84,46,.1); }
    .input-wrap input.invalid { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(239,68,68,.15); }
    .toggle-pw {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #B7AC9C;
      padding: 4px; display: flex; transition: color .2s;
    }
    .toggle-pw:hover { color: var(--ember); }
    .row-between { display: flex; justify-content: flex-end; margin: -6px 0 20px; }
    .row-between a { font-size: 12.5px; color: var(--ink-soft); text-decoration: none; }
    .row-between a:hover { color: var(--ember); }

    /* ── Button ── */
    .btn-primary {
      width: 100%; padding: 13.5px 0; border: none; border-radius: 10px;
      background: linear-gradient(135deg, var(--coral), var(--ember));
      color: #fff; font-family: 'Source Sans 3', sans-serif;
      font-weight: 700; font-size: 14.5px; cursor: pointer;
      box-shadow: 0 10px 24px rgba(193,84,46,.35);
      transition: transform .18s, box-shadow .18s, filter .18s, opacity .18s;
    }
    .btn-primary:hover    { filter: brightness(1.06); transform: translateY(-1px); box-shadow: 0 14px 30px rgba(193,84,46,.42); }
    .btn-primary:active   { transform: translateY(0); }
    .btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* ── Panels & switch ── */
    .auth-panel { display: none; }
    .auth-panel.active { display: block; }
    .switch-line { text-align: center; font-size: 13px; color: var(--ink-soft); margin-top: 20px; }
    .switch-line a { color: var(--ember); font-weight: 700; text-decoration: none; cursor: pointer; }
    .switch-line a:hover { text-decoration: underline; }

    /* ── Footer ── */
    .stage-foot {
      margin-top: 26px; display: flex; align-items: center; gap: 9px;
      font-family: 'IBM Plex Mono', monospace; font-size: 10px; color: rgba(251,246,236,.45);
    }
    .stage-foot a   { color: rgba(251,246,236,.55); text-decoration: underline; }
    .stage-foot .dot{ width: 3px; height: 3px; border-radius: 50%; background: rgba(251,246,236,.35); }

    @media (max-width: 480px) {
      .top-brand  { top: 20px; left: 20px; }
      .back-link  { top: 24px; right: 20px; }
      .verse h1   { font-size: 24px; }
      .card       { padding: 32px 26px 26px; }
    }
  </style>
</head>
<body>

<div class="sky"></div>
<div class="stars"></div>
<div class="sun"></div>
<svg class="rays" viewBox="0 0 900 900">
  <g fill="none" stroke="#D9A544" stroke-width="2"><g id="raygroup"></g></g>
</svg>

<a href="<?= BASE_URL ?>/" class="back-link">← Back to site</a>

<div class="top-brand">
  <div class="glyph">
    <img src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House">
  </div>
  <div>
    <div class="name">Agape House</div>
    <div class="loc">San Carlos · Ministries</div>
  </div>
</div>

<div class="stage">

  <!-- Tagline above card -->
  <div class="verse">
    <p class="eyebrow">Member Access</p>
    <h1>A place to <em>belong</em> &amp; grow.</h1>
  </div>

  <!-- Card -->
  <div class="card">

    <!-- Floating seal -->
    <div class="seal-float">
      <img src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House">
    </div>

    <!-- Tabs -->
    <?php
      $isAdminLoginPage = isset($route) && $route === '/login';
      $signInAction     = $isAdminLoginPage ? BASE_URL . '/login'         : BASE_URL . '/member/login';
      $identifierLabel  = $isAdminLoginPage ? 'Username'                  : 'Email or Username';
      $identifierName   = $isAdminLoginPage ? 'username'                  : 'identifier';
      $identifierValue  = $isAdminLoginPage
                          ? htmlspecialchars($_POST['username']   ?? '')
                          : htmlspecialchars($_POST['identifier'] ?? '');
      $activeTab = $authTab ?? 'signin';
    ?>
    <div class="tabs" id="tabs">
      <button type="button" class="active" data-tab="signin">
        <?= $isAdminLoginPage ? 'Admin Sign In' : 'Sign In' ?>
      </button>
      <?php if (!$isAdminLoginPage): ?>
      <button type="button" class="<?= $activeTab === 'signup' ? 'active' : '' ?>" data-tab="signup">
        Create Account
      </button>
      <?php endif; ?>
    </div>

    <!-- Sign In panel -->
    <div class="auth-panel <?= $activeTab === 'signin' ? 'active' : '' ?>" id="panelSignIn">
      <div class="card-head">
        <h2><?= $isAdminLoginPage ? 'Admin Sign In' : 'Welcome back' ?></h2>
        <p><?= $isAdminLoginPage ? 'Sign in to the admin panel.' : 'Sign in with your email or username.' ?></p>
      </div>

      <?php if (!empty($loginError)): ?>
        <div class="auth-alert auth-alert--error"><span>⚠</span><?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= $signInAction ?>" id="signInForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="field">
          <label for="siIdentifier"><?= $identifierLabel ?></label>
          <div class="input-wrap">
            <input id="siIdentifier" type="text" name="<?= $identifierName ?>"
                   value="<?= $identifierValue ?>"
                   placeholder="<?= $isAdminLoginPage ? 'admin' : 'you@example.com' ?>"
                   autocomplete="username" autofocus
                   class="<?= !empty($loginError) ? 'invalid' : '' ?>">
          </div>
        </div>
        <div class="field" style="margin-bottom:0;">
          <label for="siPassword">Password</label>
          <div class="input-wrap">
            <input id="siPassword" type="password" name="password"
                   placeholder="••••••••" autocomplete="current-password"
                   class="<?= !empty($loginError) ? 'invalid' : '' ?>">
            <button type="button" class="toggle-pw" id="toggleSiPw" aria-label="Show password">
              <svg id="eyeSi" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <?php if (!$isAdminLoginPage): ?>
        <div class="row-between"><a href="#">Forgot password?</a></div>
        <?php else: ?>
        <div style="margin-bottom:20px;"></div>
        <?php endif; ?>
        <button type="submit" class="btn-primary" id="siBtn">Sign in</button>
      </form>

      <?php if (!$isAdminLoginPage): ?>
      <p class="switch-line">New here? <a id="toSignUp">Create an account</a></p>
      <?php endif; ?>
    </div><!-- /panelSignIn -->

    <?php if (!$isAdminLoginPage): ?>
    <!-- Create Account panel -->
    <div class="auth-panel <?= $activeTab === 'signup' ? 'active' : '' ?>" id="panelSignUp">
      <div class="card-head">
        <h2>Join Agape House</h2>
        <p>Create your free member account.</p>
      </div>

      <?php if (!empty($registerError)): ?>
        <div class="auth-alert auth-alert--error"><span>⚠</span><?= htmlspecialchars($registerError) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/member/register" id="signUpForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="field">
          <label for="suName">Full Name</label>
          <div class="input-wrap">
            <input id="suName" type="text" name="display_name"
                   value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>"
                   placeholder="e.g. Maria Santos" maxlength="120" autocomplete="name">
          </div>
        </div>
        <div class="field">
          <label for="suEmail">Email</label>
          <div class="input-wrap">
            <input id="suEmail" type="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="you@example.com" autocomplete="email">
          </div>
        </div>
        <div class="field">
          <label for="suUsername">Username</label>
          <div class="input-wrap">
            <input id="suUsername" type="text" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   placeholder="e.g. mariacf" maxlength="60"
                   autocomplete="username" spellcheck="false">
          </div>
          <p class="field-hint">Min 3 characters · letters, numbers, _ only</p>
        </div>
        <div class="field">
          <label for="suPassword">Password</label>
          <div class="input-wrap">
            <input id="suPassword" type="password" name="password"
                   placeholder="Min 8 characters" autocomplete="new-password">
            <button type="button" class="toggle-pw" aria-label="Show password"
                    onclick="togglePw('suPassword', this)">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="field">
          <label for="suConfirm">Confirm Password</label>
          <div class="input-wrap">
            <input id="suConfirm" type="password" name="confirm_password"
                   placeholder="Repeat your password" autocomplete="new-password">
            <button type="button" class="toggle-pw" aria-label="Show password"
                    onclick="togglePw('suConfirm', this)">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary" id="suBtn">Create account</button>
      </form>
      <p class="switch-line">Already a member? <a id="toSignIn">Sign in</a></p>
    </div><!-- /panelSignUp -->
    <?php endif; ?>

  </div><!-- /card -->

  <!-- Stage footer -->
  <div class="stage-foot">
    <span><?= htmlspecialchars(APP_NAME) ?> · <?= date('Y') ?></span>
    <span class="dot"></span>
    <?php if ($isAdminLoginPage): ?>
      <a href="<?= BASE_URL ?>/member/login">Member login</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login">Admin login</a>
    <?php endif; ?>
  </div>

</div><!-- /stage -->

<script>
// ── Sunrise rays ──
(function(){
  const g = document.getElementById('raygroup');
  const count = 20;
  for (let i = 0; i < count; i++) {
    const angle = (360 / count) * i;
    const line  = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', '450'); line.setAttribute('y1', '450');
    line.setAttribute('x2', '450'); line.setAttribute('y2', '30');
    line.setAttribute('transform', `rotate(${angle} 450 450)`);
    g.appendChild(line);
  }
})();

// ── Tab switching ──
<?php if (!$isAdminLoginPage): ?>
const tabs       = document.getElementById('tabs');
const tabBtns    = tabs.querySelectorAll('button');
const panelIn    = document.getElementById('panelSignIn');
const panelUp    = document.getElementById('panelSignUp');

function setTab(mode) {
  tabBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === mode));
  panelIn.classList.toggle('active', mode === 'signin');
  panelUp.classList.toggle('active', mode === 'signup');
  if (mode === 'signin') document.getElementById('siIdentifier').focus();
  else                   document.getElementById('suName').focus();
}

tabBtns.forEach(btn => btn.addEventListener('click', () => setTab(btn.dataset.tab)));

document.getElementById('toSignUp')?.addEventListener('click', e => { e.preventDefault(); setTab('signup'); });
document.getElementById('toSignIn')?.addEventListener('click', e => { e.preventDefault(); setTab('signin'); });
<?php endif; ?>

// ── Password toggle ──
function togglePw(id, btn) {
  const el = document.getElementById(id);
  const show = el.type === 'password';
  el.type = show ? 'text' : 'password';
  const svg = btn.querySelector('svg');
  if (svg) svg.innerHTML = show
    ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.6 20.6 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a20.6 20.6 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
    : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>';
}

// Wire up sign-in toggle
document.getElementById('toggleSiPw')?.addEventListener('click', function() {
  togglePw('siPassword', this);
});

// ── Disable buttons on submit ──
document.getElementById('signInForm')?.addEventListener('submit', () => {
  const b = document.getElementById('siBtn');
  b.disabled = true; b.textContent = 'Signing in…';
});
document.getElementById('signUpForm')?.addEventListener('submit', () => {
  const b = document.getElementById('suBtn');
  b.disabled = true; b.textContent = 'Creating account…';
});
</script>
</body>
</html>
