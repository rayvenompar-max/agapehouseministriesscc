<?php
/**
 * Public member profile page — /member/{username}
 * $profileData is set by index.php and contains only safe public fields.
 */
declare(strict_types=1);

$displayName   = htmlspecialchars($profileData['display_name'] ?? $profileData['username']);
$username      = htmlspecialchars($profileData['username']);
$initial       = strtoupper(mb_substr($profileData['display_name'] ?? $profileData['username'], 0, 1));
$pictureSrc    = !empty($profileData['profile_picture']) ? htmlspecialchars($profileData['profile_picture']) : null;
$memberSince   = $profileData['created_at'] ? date('F Y', strtotime($profileData['created_at'])) : '—';
$isOwnProfile  = $memberAuth->isLoggedIn() && ($memberAuth->current()['username'] ?? '') === $profileData['username'];

// Nav avatar for logged-in visitor
$visitor       = $memberAuth->current();
$visitorName   = htmlspecialchars($visitor['display_name'] ?? $visitor['username'] ?? '');
$visitorInitial = $visitor ? strtoupper(mb_substr($visitor['display_name'] ?? $visitor['username'] ?? 'M', 0, 1)) : '';
$visitorPic    = !empty($visitor['profile_picture']) ? htmlspecialchars($visitor['profile_picture']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $displayName ?> — <?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root{--night:#0A1B33;--dusk:#1B3E68;--horizon:#3E7CB1;--sun:#7FC4E8;--sun-light:#D3EEFB;--paper:#F3F7FA;--ink:#14202E;--ink-soft:#55677A;--line:#DCE6ED;--white:#FFFFFF;--display:'Fraunces',serif;--body:'Work Sans',sans-serif;--mono:'IBM Plex Mono',monospace;--ease-out:cubic-bezier(.16,1,.3,1);}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:var(--paper);color:var(--ink);font-family:var(--body);-webkit-font-smoothing:antialiased;}
    a{color:inherit;text-decoration:none;}

    /* NAV */
    nav{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:60px;background:var(--night);position:sticky;top:0;z-index:100;}
    .brand{display:flex;align-items:center;gap:12px;}
    .brand .mark{width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;}
    .brand .word{font-family:var(--display);font-weight:600;font-size:19px;color:var(--white);line-height:1;}
    .brand .tag{font-family:var(--mono);font-size:9px;letter-spacing:.14em;color:#8FA9C4;text-transform:uppercase;margin-top:3px;}
    .navlinks{display:flex;align-items:center;gap:28px;}
    .navlinks a{font-size:13px;color:#C3D3E2;font-weight:500;padding:4px 0;transition:color .25s;}
    .navlinks a:hover{color:var(--white);}
    .account-pill{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.06);border:1px solid #2A4667;padding:5px 8px 5px 5px;border-radius:100px;cursor:pointer;transition:border-color .2s;}
    .account-pill:hover{border-color:var(--sun);}
    .nav-avatar{width:26px;height:26px;border-radius:50%;background:linear-gradient(160deg,var(--sun-light),var(--horizon));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--night);overflow:hidden;flex-shrink:0;}
    .nav-avatar img{width:26px;height:26px;border-radius:50%;object-fit:cover;display:block;}
    .account-pill span{font-size:12px;color:var(--white);font-weight:500;}
    .account-pill .chev{font-size:9px;color:#8FA9C4;margin-right:2px;}
    .nav-right{position:relative;}
    .dropdown{display:none;position:absolute;right:0;top:calc(100% + 8px);background:var(--white);border:1px solid var(--line);border-radius:8px;min-width:160px;box-shadow:0 8px 24px rgba(10,27,51,.15);overflow:hidden;}
    .dropdown.open{display:block;}
    .dropdown a,.dropdown button{display:block;width:100%;text-align:left;padding:11px 16px;font-size:13px;color:var(--ink);background:none;border:none;cursor:pointer;font-family:inherit;transition:background .15s;}
    .dropdown a:hover,.dropdown button:hover{background:var(--paper);}
    .dropdown button{color:#c62828;}
    .dropdown hr{border:none;border-top:1px solid var(--line);}
    .nav-login-btn{font-family:var(--mono);font-size:12px;font-weight:600;color:var(--white);background:var(--horizon);padding:8px 18px;border-radius:100px;transition:background .2s;}
    .nav-login-btn:hover{background:var(--dusk);}
    @media(max-width:900px){.navlinks{display:none;}nav{padding:0 22px;}}

    /* HERO */
    .profile-hero{background:linear-gradient(180deg,var(--night) 0%,var(--dusk) 70%,var(--horizon) 100%);padding:60px 48px 50px;text-align:center;}
    .profile-avatar-wrap{position:relative;display:inline-block;margin-bottom:20px;}
    .profile-avatar{width:96px;height:96px;border-radius:50%;background:linear-gradient(160deg,var(--sun-light),var(--horizon));display:flex;align-items:center;justify-content:center;font-family:var(--display);font-size:38px;font-weight:700;color:var(--night);overflow:hidden;border:3px solid rgba(255,255,255,.15);}
    .profile-avatar img{width:96px;height:96px;border-radius:50%;object-fit:cover;display:block;}
    .profile-name{font-family:var(--display);font-weight:600;font-size:clamp(1.8rem,4vw,2.4rem);color:var(--white);}
    .profile-username{font-family:var(--mono);font-size:13px;color:#9FBEDB;margin-top:6px;letter-spacing:.04em;}
    .profile-since{font-family:var(--mono);font-size:11px;color:#7AA5C8;margin-top:10px;letter-spacing:.08em;text-transform:uppercase;}
    .profile-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(127,196,232,.12);border:1px solid rgba(127,196,232,.25);color:var(--sun-light);font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;padding:5px 14px;border-radius:100px;margin-top:14px;}
    .profile-badge::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--sun-light);}
    @media(max-width:900px){.profile-hero{padding:48px 22px 40px;}}

    /* OWN PROFILE EDIT BUTTON */
    .edit-own-btn{display:inline-block;margin-top:18px;font-family:var(--mono);font-size:12px;font-weight:600;color:var(--white);border:1px solid rgba(255,255,255,.3);padding:9px 22px;border-radius:100px;transition:background .2s,border-color .2s;}
    .edit-own-btn:hover{background:rgba(255,255,255,.1);border-color:var(--white);}

    /* BODY */
    .profile-body{max-width:680px;margin:0 auto;padding:44px 48px 80px;}
    @media(max-width:900px){.profile-body{padding:36px 22px 60px;}}

    /* INFO CARD */
    .info-card{background:var(--white);border:1px solid var(--line);border-radius:8px;padding:28px 30px;margin-bottom:24px;}
    .info-card-title{font-family:var(--mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-soft);margin-bottom:16px;}
    .info-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--line);}
    .info-row:last-child{border-bottom:none;padding-bottom:0;}
    .info-row:first-of-type{padding-top:0;}
    .info-icon{width:32px;height:32px;border-radius:6px;background:var(--paper);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
    .info-label{font-family:var(--mono);font-size:11px;color:var(--ink-soft);margin-bottom:2px;text-transform:uppercase;letter-spacing:.06em;}
    .info-value{font-size:14px;color:var(--ink);font-weight:500;}

    /* BACK LINK */
    .back-link{display:inline-flex;align-items:center;gap:8px;font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-bottom:28px;transition:color .2s;}
    .back-link:hover{color:var(--horizon);}
    .back-link::before{content:'←';}
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="<?= BASE_URL ?>/" class="brand">
    <img class="mark" src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House">
    <div>
      <div class="word">Agape House</div>
      <div class="tag">San Carlos · Ministries</div>
    </div>
  </a>
  <div class="navlinks">
    <a href="<?= BASE_URL ?>/">Home</a>
    <a href="<?= BASE_URL ?>/#watch">Watch</a>
    <a href="<?= BASE_URL ?>/#read">Read</a>
    <a href="<?= BASE_URL ?>/#bible">Bible</a>
    <a href="<?= BASE_URL ?>/#prayer">Prayer</a>
    <a href="<?= BASE_URL ?>/#events">Events</a>
  </div>

  <?php if ($memberAuth->isLoggedIn()): ?>
  <div class="nav-right">
    <div class="account-pill" onclick="document.getElementById('navDropdown').classList.toggle('open')">
      <div class="nav-avatar">
        <?php if ($visitorPic): ?>
          <img src="<?= $visitorPic ?>" alt="<?= $visitorName ?>">
        <?php else: ?>
          <?= $visitorInitial ?>
        <?php endif; ?>
      </div>
      <span><?= $visitorName ?></span>
      <span class="chev">▾</span>
    </div>
    <div class="dropdown" id="navDropdown">
      <a href="<?= BASE_URL ?>/portal">My Account</a>
      <hr>
      <form method="POST" action="<?= BASE_URL ?>/member/logout" style="margin:0;">
        <button type="submit">Sign out</button>
      </form>
    </div>
  </div>
  <?php else: ?>
  <a href="<?= BASE_URL ?>/member/login" class="nav-login-btn">Sign in</a>
  <?php endif; ?>
</nav>

<!-- HERO -->
<div class="profile-hero">
  <div class="profile-avatar-wrap">
    <div class="profile-avatar">
      <?php if ($pictureSrc): ?>
        <img src="<?= $pictureSrc ?>" alt="<?= $displayName ?>">
      <?php else: ?>
        <?= $initial ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="profile-name"><?= $displayName ?></div>
  <div class="profile-username">@<?= $username ?></div>
  <div class="profile-since">Member since <?= $memberSince ?></div>
  <div><span class="profile-badge">Agape House Member</span></div>

  <?php if ($isOwnProfile): ?>
    <div><a href="<?= BASE_URL ?>/portal" class="edit-own-btn">Edit my profile</a></div>
  <?php endif; ?>
</div>

<!-- BODY -->
<div class="profile-body">
  <a class="back-link" href="<?= BASE_URL ?>/">Back to home</a>

  <div class="info-card">
    <div class="info-card-title">Profile info</div>

    <div class="info-row">
      <div class="info-icon"><i data-lucide="user"></i></div>
      <div>
        <div class="info-label">Display name</div>
        <div class="info-value"><?= $displayName ?></div>
      </div>
    </div>

    <div class="info-row">
      <div class="info-icon"><i data-lucide="at-sign"></i></div>
      <div>
        <div class="info-label">Username</div>
        <div class="info-value">@<?= $username ?></div>
      </div>
    </div>

    <div class="info-row">
      <div class="info-icon"><i data-lucide="calendar"></i></div>
      <div>
        <div class="info-label">Member since</div>
        <div class="info-value"><?= $memberSince ?></div>
      </div>
    </div>
  </div>

  <?php if (!$memberAuth->isLoggedIn()): ?>
  <p style="text-align:center;font-size:14px;color:var(--ink-soft);margin-top:32px;">
    <a href="<?= BASE_URL ?>/member/login" style="color:var(--horizon);font-weight:600;">Sign in</a> to connect with members of Agape House.
  </p>
  <?php endif; ?>
</div>

<script>
  document.addEventListener('click', function(e) {
    const dd = document.getElementById('navDropdown');
    if (dd && !e.target.closest('.nav-right')) dd.classList.remove('open');
  });
</script>
</body>
</html>
