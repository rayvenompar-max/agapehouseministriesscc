<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — <?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --night:#0A1B33; --dusk:#1B3E68; --horizon:#3E7CB1;
      --sun:#7FC4E8; --sun-light:#D3EEFB; --paper:#F3F7FA;
      --ink:#14202E; --ink-soft:#55677A; --line:#DCE6ED; --white:#FFFFFF;
    }
    html,body{ height:100%; font-family:'Work Sans',sans-serif; background:var(--paper); color:var(--ink); -webkit-font-smoothing:antialiased; }
    .auth-page{ min-height:100vh; display:grid; grid-template-columns:1fr 1fr; }
    @media(max-width:768px){ .auth-page{grid-template-columns:1fr} .auth-side{display:none} }

    /* ── Left ── */
    .auth-side{
      background:linear-gradient(160deg,var(--night) 0%,var(--dusk) 55%,var(--horizon) 100%);
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      padding:60px 48px; position:relative; overflow:hidden;
    }
    .auth-side::before{ content:''; position:absolute; width:480px; height:480px; border-radius:50%; background:radial-gradient(circle,rgba(127,196,232,.15) 0%,transparent 70%); top:-80px; right:-100px; pointer-events:none; }
    .auth-side::after { content:''; position:absolute; width:300px; height:300px; border-radius:50%; background:radial-gradient(circle,rgba(127,196,232,.1) 0%,transparent 70%); bottom:-40px; left:-60px; pointer-events:none; }
    .side-brand{ display:flex; align-items:center; gap:14px; position:absolute; top:36px; left:48px; }
    .brand-mark{ width:36px; height:36px; border-radius:50%; background:radial-gradient(circle at 35% 30%,var(--sun-light),var(--sun) 60%,var(--horizon) 100%); box-shadow:0 0 16px 3px rgba(127,196,232,.4); flex-shrink:0; }
    .brand-logo-img{ width:40px; height:40px; border-radius:50%; object-fit:cover; flex-shrink:0; box-shadow:0 0 12px 2px rgba(127,196,232,.35); }
    .brand-name{ font-family:'Fraunces',serif; font-size:22px; font-weight:600; color:var(--white); line-height:1; }
    .brand-tag { display:block; font-family:'IBM Plex Mono',monospace; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,.5); margin-top:3px; }
    .side-content{ position:relative; z-index:1; text-align:center; max-width:340px; }
    .side-orb{ width:120px; height:120px; border-radius:50%; margin:0 auto 36px; object-fit:cover; box-shadow:0 0 40px 10px rgba(127,196,232,.35); display:block; }
    @keyframes breathe{ 0%,100%{box-shadow:0 0 60px 12px rgba(127,196,232,.45)} 50%{box-shadow:0 0 80px 20px rgba(127,196,232,.6)} }
    .side-heading{ font-family:'Fraunces',serif; font-size:32px; font-weight:600; color:var(--white); line-height:1.1; margin-bottom:14px; }
    .side-heading em{ font-style:italic; color:var(--sun-light); }
    .side-sub{ font-size:15px; color:rgba(255,255,255,.65); line-height:1.6; }

    /* ── Right ── */
    .auth-form-panel{ display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 40px; background:var(--white); }
    .mobile-brand{ display:none; align-items:center; gap:10px; margin-bottom:32px; }
    @media(max-width:768px){ .mobile-brand{display:flex} .auth-form-panel{padding:40px 24px} }
    .mobile-brand .brand-mark{ box-shadow:none; }
    .mobile-brand .brand-name{ color:var(--ink); }
    .mobile-brand .brand-tag { color:var(--ink-soft); }
    .auth-box{ width:100%; max-width:400px; }

    /* ── Tabs ── */
    .auth-tabs{ display:flex; background:var(--paper); border-radius:100px; padding:4px; margin-bottom:28px; border:1px solid var(--line); }
    .auth-tab{ flex:1; padding:10px 0; border:none; border-radius:100px; font-family:inherit; font-size:14px; font-weight:600; cursor:pointer; background:none; color:var(--ink-soft); transition:all .2s; }
    .auth-tab.active{ background:var(--night); color:var(--white); box-shadow:0 2px 8px rgba(10,27,51,.25); }
    .auth-panel{ display:none; }
    .auth-panel.active{ display:block; }

    /* ── Headings ── */
    .auth-heading{ font-family:'Fraunces',serif; font-size:24px; font-weight:600; color:var(--ink); margin-bottom:4px; }
    .auth-sub{ font-size:13px; color:var(--ink-soft); margin-bottom:24px; }

    /* ── Alert ── */
    .auth-alert{ padding:11px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; display:flex; align-items:flex-start; gap:8px; }
    .auth-alert--error  { background:#fee2e2; color:#7f1d1d; border:1px solid #fca5a5; }
    .auth-alert--success{ background:#dcfce7; color:#14532d; border:1px solid #86efac; }

    /* ── Fields ── */
    .field{ margin-bottom:14px; }
    .field label{ display:block; font-size:13px; font-weight:600; color:var(--ink); margin-bottom:5px; }
    .field-wrap{ position:relative; }
    .field input{ width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:8px; font-family:inherit; font-size:14px; background:var(--paper); color:var(--ink); transition:border-color .2s,box-shadow .2s; }
    .field input:focus{ outline:none; border-color:var(--horizon); box-shadow:0 0 0 3px rgba(62,124,177,.2); background:var(--white); }
    .field input.invalid{ border-color:#fca5a5; box-shadow:0 0 0 3px rgba(239,68,68,.15); }
    .field-hint{ font-size:11px; color:var(--ink-soft); margin-top:3px; }
    .toggle-pw{ position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--ink-soft); cursor:pointer; font-size:15px; padding:4px; line-height:1; }
    .toggle-pw:hover{ color:var(--horizon); }

    /* ── Submit ── */
    .btn-submit{ width:100%; padding:12px; background:linear-gradient(90deg,var(--dusk),var(--horizon)); color:var(--white); border:none; border-radius:100px; font-family:inherit; font-size:15px; font-weight:600; cursor:pointer; margin-top:6px; transition:opacity .2s,transform .2s,box-shadow .2s; }
    .btn-submit:hover  { opacity:.9; transform:translateY(-1px); box-shadow:0 8px 20px -8px rgba(30,70,120,.5); }
    .btn-submit:disabled{ opacity:.6; cursor:not-allowed; transform:none; }

    .auth-switch{ text-align:center; margin-top:18px; font-size:13px; color:var(--ink-soft); }
    .auth-switch a{ color:var(--horizon); font-weight:600; cursor:pointer; }
    .auth-switch a:hover{ text-decoration:underline; }

    .auth-footer{ position:fixed; bottom:0; right:0; left:50%; text-align:center; padding:12px; font-family:'IBM Plex Mono',monospace; font-size:10px; color:var(--ink-soft); letter-spacing:.04em; }
    @media(max-width:768px){ .auth-footer{left:0} }
  </style>
</head>
<body>
<div class="auth-page">

  <!-- Left brand panel -->
  <div class="auth-side">
    <div class="side-brand">
      <img src="<?= BASE_URL ?>/public/images/agape.png" alt="Agape House" class="brand-logo-img">
      <div><span class="brand-name">Agape House</span><span class="brand-tag">San Carlos · Ministries</span></div>
    </div>
    <div class="side-content">
      <img src="<?= BASE_URL ?>/public/images/agape.png" alt="Agape House" class="side-orb">
      <h1 class="side-heading">A place to<br><em>belong & grow.</em></h1>
      <p class="side-sub">Watch, read, pray, and connect — your personal space in the Agape House Ministries community.</p>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="auth-form-panel">
    <div class="mobile-brand">
      <img src="<?= BASE_URL ?>/public/images/agape.png" alt="Agape House" class="brand-logo-img">
      <div><span class="brand-name" style="color:var(--ink)">Agape House</span><span class="brand-tag" style="color:var(--ink-soft)">San Carlos · Ministries</span></div>
    </div>

    <div class="auth-box">
      <!-- Tabs -->
      <div class="auth-tabs" role="tablist">
        <button class="auth-tab <?= ($authTab ?? 'signin') === 'signin' ? 'active' : '' ?>" id="tabSignIn" role="tab" onclick="switchTab('signin')">Sign In</button>
        <?php if (!isset($route) || $route !== '/login'): ?>
        <button class="auth-tab <?= ($authTab ?? 'signin') === 'signup' ? 'active' : '' ?>" id="tabSignUp" role="tab" onclick="switchTab('signup')">Create Account</button>
        <?php endif; ?>
      </div>

      <!-- Sign In -->
      <div class="auth-panel <?= ($authTab ?? 'signin') === 'signin' ? 'active' : '' ?>" id="panelSignIn">
        <h2 class="auth-heading"><?= (isset($route) && $route === '/login') ? 'Admin Sign In' : 'Welcome back' ?></h2>
        <p class="auth-sub"><?= (isset($route) && $route === '/login') ? 'Sign in to the admin panel.' : 'Sign in with your email or username.' ?></p>

        <?php if (!empty($loginError)): ?>
          <div class="auth-alert auth-alert--error"><span>⚠</span><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <?php
          // Determine if this page was loaded for admin login or member login
          $isAdminLoginPage = isset($route) && ($route === '/login');
          $signInAction     = $isAdminLoginPage
                            ? BASE_URL . '/login'
                            : BASE_URL . '/member/login';
          $identifierLabel  = $isAdminLoginPage ? 'Username' : 'Email or Username';
          $identifierName   = $isAdminLoginPage ? 'username'  : 'identifier';
          $identifierValue  = $isAdminLoginPage
                            ? htmlspecialchars($_POST['username']   ?? '')
                            : htmlspecialchars($_POST['identifier'] ?? '');
        ?>
        <form method="POST" action="<?= $signInAction ?>" id="signInForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="field">
            <label for="siIdentifier"><?= $identifierLabel ?></label>
            <input type="text" id="siIdentifier" name="<?= $identifierName ?>"
                   value="<?= $identifierValue ?>"
                   required autofocus autocomplete="username" spellcheck="false"
                   class="<?= !empty($loginError) ? 'invalid' : '' ?>">
          </div>
          <div class="field">
            <label for="siPassword">Password</label>
            <div class="field-wrap">
              <input type="password" id="siPassword" name="password" required autocomplete="current-password"
                     class="<?= !empty($loginError) ? 'invalid' : '' ?>">
              <button type="button" class="toggle-pw" onclick="togglePw('siPassword',this)">👁</button>
            </div>
          </div>
          <button type="submit" class="btn-submit" id="siBtn">Sign in</button>
        </form>
        <?php if (!isset($route) || $route !== '/login'): ?>
        <p class="auth-switch">New here? <a onclick="switchTab('signup')">Create an account</a></p>
        <?php endif; ?>
      </div>

      <!-- Sign Up (creates a MEMBER, not admin) -->
      <div class="auth-panel <?= ($authTab ?? 'signin') === 'signup' ? 'active' : '' ?>" id="panelSignUp">
        <h2 class="auth-heading">Join Agape House</h2>
        <p class="auth-sub">Create your free member account.</p>

        <?php if (!empty($registerError)): ?>
          <div class="auth-alert auth-alert--error"><span>⚠</span><?= htmlspecialchars($registerError) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/member/register" id="signUpForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="field">
            <label for="suName">Full Name</label>
            <input type="text" id="suName" name="display_name"
                   value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>"
                   placeholder="e.g. Maria Santos" maxlength="120" autocomplete="name">
          </div>
          <div class="field">
            <label for="suEmail">Email</label>
            <input type="email" id="suEmail" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="you@example.com" autocomplete="email">
          </div>
          <div class="field">
            <label for="suUsername">Username</label>
            <input type="text" id="suUsername" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   placeholder="e.g. mariacf" maxlength="60" autocomplete="username" spellcheck="false">
            <p class="field-hint">Min 3 characters · letters, numbers, _ only</p>
          </div>
          <div class="field">
            <label for="suPassword">Password</label>
            <div class="field-wrap">
              <input type="password" id="suPassword" name="password" placeholder="Min 8 characters" autocomplete="new-password">
              <button type="button" class="toggle-pw" onclick="togglePw('suPassword',this)">👁</button>
            </div>
          </div>
          <div class="field">
            <label for="suConfirm">Confirm Password</label>
            <div class="field-wrap">
              <input type="password" id="suConfirm" name="confirm_password" placeholder="Repeat your password" autocomplete="new-password">
              <button type="button" class="toggle-pw" onclick="togglePw('suConfirm',this)">👁</button>
            </div>
          </div>
          <button type="submit" class="btn-submit" id="suBtn">Create account</button>
        </form>
        <p class="auth-switch">Already a member? <a onclick="switchTab('signin')">Sign in</a></p>
      </div>

    </div>
  </div>
</div>

<footer class="auth-footer">
  <?php if (isset($route) && $route === '/login'): ?>
    <!-- Admin login page — link back to main site and member login -->
    <a href="<?= BASE_URL ?>/" style="color:inherit;text-decoration:none;">← Back to site</a>
    &nbsp;·&nbsp; <?= htmlspecialchars(APP_NAME) ?> · <?= date('Y') ?>
    &nbsp;·&nbsp; <a href="<?= BASE_URL ?>/member/login" style="color:var(--horizon);">Member login</a>
  <?php else: ?>
    <!-- Member login page — link back to main site and admin login -->
    <a href="<?= BASE_URL ?>/" style="color:inherit;text-decoration:none;">← Back to site</a>
    &nbsp;·&nbsp; <?= htmlspecialchars(APP_NAME) ?> · <?= date('Y') ?>
    &nbsp;·&nbsp; <a href="<?= BASE_URL ?>/login" style="color:var(--horizon);">Admin login</a>
  <?php endif; ?>
</footer>

<script>
  function switchTab(tab) {
    document.getElementById('tabSignIn').classList.toggle('active', tab==='signin');
    document.getElementById('tabSignUp').classList.toggle('active', tab==='signup');
    document.getElementById('panelSignIn').classList.toggle('active', tab==='signin');
    document.getElementById('panelSignUp').classList.toggle('active', tab==='signup');
    if (tab==='signin') document.getElementById('siIdentifier').focus();
    else                document.getElementById('suName').focus();
  }
  function togglePw(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type==='password' ? 'text' : 'password';
    btn.textContent = el.type==='text' ? '🙈' : '👁';
  }
  document.getElementById('signInForm').addEventListener('submit', () => {
    const b = document.getElementById('siBtn'); b.disabled=true; b.textContent='Signing in…';
  });
  document.getElementById('signUpForm').addEventListener('submit', () => {
    const b = document.getElementById('suBtn'); b.disabled=true; b.textContent='Creating account…';
  });
</script>
</body>
</html>
