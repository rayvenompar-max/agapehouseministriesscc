<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account — <?= htmlspecialchars(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root{--night:#0A1B33;--dusk:#1B3E68;--horizon:#3E7CB1;--sun:#7FC4E8;--sun-light:#D3EEFB;--paper:#F3F7FA;--ink:#14202E;--ink-soft:#55677A;--line:#DCE6ED;--white:#FFFFFF;--praying:#C7862E;--praying-bg:#F7EBD9;--answered:#3F7A4E;--answered-bg:#E7EFE7;--private:#6C88A8;--private-bg:#E9EEF3;--display:'Fraunces',serif;--body:'Work Sans',sans-serif;--mono:'IBM Plex Mono',monospace;--ease-out:cubic-bezier(.16,1,.3,1);--ease-soft:cubic-bezier(.4,0,.2,1);}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:var(--paper);color:var(--ink);font-family:var(--body);-webkit-font-smoothing:antialiased;}
    a{color:inherit;text-decoration:none;}
    .reveal{opacity:0;transform:translateY(16px);transition:opacity .6s var(--ease-out),transform .6s var(--ease-out);}
    .reveal.in{opacity:1;transform:translateY(0);}
    /* NAV */
    nav{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:60px;background:var(--night);position:sticky;top:0;z-index:100;}
    .brand{display:flex;align-items:center;gap:12px;}
    .brand .mark{width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;}
    .brand .word{font-family:var(--display);font-weight:600;font-size:19px;color:var(--white);line-height:1;}
    .brand .tag{font-family:var(--mono);font-size:9px;letter-spacing:.14em;color:#8FA9C4;text-transform:uppercase;margin-top:3px;}
    .navlinks{display:flex;align-items:center;gap:28px;}
    .navlinks a{font-size:13px;color:#C3D3E2;font-weight:500;position:relative;padding:4px 0;transition:color .25s;}
    .navlinks a:hover{color:var(--white);}
    .account-pill{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.06);border:1px solid #2A4667;padding:5px 8px 5px 5px;border-radius:100px;cursor:pointer;transition:border-color .2s;}
    .account-pill:hover{border-color:var(--sun);}
    .avatar{width:26px;height:26px;border-radius:50%;background:linear-gradient(160deg,var(--sun-light),var(--horizon));flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--night);}
    .avatar img{width:26px;height:26px;border-radius:50%;object-fit:cover;display:block;}
    .account-pill span{font-size:12px;color:var(--white);font-weight:500;}
    .account-pill .chev{font-size:9px;color:#8FA9C4;margin-right:2px;}
    /* dropdown */
    .nav-right{position:relative;}
    .dropdown{display:none;position:absolute;right:0;top:calc(100% + 8px);background:var(--white);border:1px solid var(--line);border-radius:8px;min-width:160px;box-shadow:0 8px 24px rgba(10,27,51,.15);overflow:hidden;}
    .dropdown.open{display:block;}
    .dropdown a,.dropdown button{display:block;width:100%;text-align:left;padding:11px 16px;font-size:13px;color:var(--ink);background:none;border:none;cursor:pointer;font-family:inherit;transition:background .15s;}
    .dropdown a:hover,.dropdown button:hover{background:var(--paper);}
    .dropdown button{color:#c62828;}
    .dropdown hr{border:none;border-top:1px solid var(--line);}
    @media(max-width:900px){.navlinks{display:none;}nav{padding:0 22px;}}
    /* HERO */
    .hero{background:linear-gradient(180deg,var(--night) 0%,var(--dusk) 70%,var(--horizon) 100%);padding:52px 48px 46px;}
    .hero-inner{max-width:1180px;margin:0 auto;}
    .eyebrow{font-family:var(--mono);font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--sun-light);margin-bottom:14px;display:flex;align-items:center;gap:10px;}
    .eyebrow::before{content:'';width:16px;height:1px;background:var(--sun-light);}
    h1.hero-title{font-family:var(--display);font-weight:600;font-size:clamp(1.8rem,4vw,2.6rem);color:var(--white);}
    h1.hero-title em{font-style:italic;font-weight:500;color:var(--sun-light);}
    .hero-sub{font-size:15px;color:#DCEBF6;margin-top:10px;}
    .stat-row{display:flex;gap:40px;margin-top:32px;flex-wrap:wrap;}
    .stat .num{font-family:var(--display);font-size:28px;font-weight:600;color:var(--white);}
    .stat .lbl{font-family:var(--mono);font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#9FBEDB;margin-top:4px;}
    @media(max-width:900px){.hero{padding:40px 22px 36px;}}
    /* SECTIONS */
    .section{max-width:1180px;margin:0 auto;padding:44px 48px 0;}
    .section:last-of-type{padding-bottom:80px;}
    .section-head{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:20px;}
    .section-title{font-family:var(--display);font-size:21px;font-weight:600;color:var(--ink);}
    .section-link{font-family:var(--mono);font-size:11px;color:var(--horizon);border-bottom:1px solid var(--horizon);padding-bottom:2px;}
    @media(max-width:900px){.section{padding:36px 22px 0;}}
    /* watch cards */
    .watch-row{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
    @media(max-width:900px){.watch-row{grid-template-columns:1fr;}}
    .watch-card{background:var(--white);border:1px solid var(--line);border-radius:6px;overflow:hidden;transition:transform .3s var(--ease-out),box-shadow .3s var(--ease-out);}
    .watch-card:hover{transform:translateY(-4px);box-shadow:0 16px 30px -16px rgba(10,27,51,.22);}
    .watch-thumb{position:relative;height:140px;display:flex;align-items:flex-end;padding:14px;}
    .watch-thumb.a{background:linear-gradient(160deg,#10233F,#2A5C8A 60%,#7FC4E8);}
    .watch-thumb.b{background:linear-gradient(160deg,#0E1B36,#245073 55%,#4E9AC7);}
    .watch-thumb.c{background:linear-gradient(160deg,#142A45,#1F5C63 55%,#63C2B8);}
    .wt-name{font-family:var(--display);font-weight:600;font-size:18px;color:var(--white);}
    .watch-body{padding:14px 16px 16px;}
    .watch-title{font-family:var(--display);font-size:15px;font-weight:600;color:var(--ink);}
    .progress-track{height:4px;background:var(--line);border-radius:2px;margin-top:10px;overflow:hidden;}
    .progress-fill{height:100%;background:linear-gradient(90deg,var(--horizon),var(--sun));border-radius:2px;}
    .progress-label{font-family:var(--mono);font-size:10px;color:var(--ink-soft);margin-top:6px;}
    /* prayer */
    .pr-list{display:flex;flex-direction:column;}
    .pr-item{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 0;border-bottom:1px solid var(--line);}
    .pr-item:first-child{border-top:1px solid var(--line);}
    .pr-left{display:flex;align-items:center;gap:16px;}
    .pr-date{font-family:var(--mono);font-size:11px;color:var(--ink-soft);width:56px;flex-shrink:0;}
    .pr-text{font-size:14px;color:var(--ink);max-width:500px;}
    .status{font-family:var(--mono);font-size:10px;letter-spacing:.06em;text-transform:uppercase;padding:4px 11px;border-radius:100px;font-weight:600;white-space:nowrap;}
    .status.praying {background:var(--praying-bg);color:var(--praying);}
    .status.answered{background:var(--answered-bg);color:var(--answered);}
    .status.private {background:var(--private-bg);color:var(--private);}
    @media(max-width:900px){.pr-item{flex-direction:column;align-items:flex-start;gap:8px;}}
    /* giving */
    .give-summary{display:flex;gap:40px;padding:22px 26px;background:var(--white);border:1px solid var(--line);border-radius:6px;margin-bottom:20px;flex-wrap:wrap;}
    .give-summary .g-num{font-family:var(--display);font-size:26px;font-weight:600;}
    .give-summary .g-lbl{font-family:var(--mono);font-size:10px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-soft);margin-top:4px;}
    .give-table{width:100%;border-collapse:collapse;}
    .give-table th{text-align:left;font-family:var(--mono);font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-soft);padding:0 0 10px;border-bottom:1px solid var(--line);font-weight:500;}
    .give-table td{padding:14px 0;border-bottom:1px solid var(--line);font-size:13px;}
    .give-table td.amount{font-family:var(--mono);font-weight:600;}
    .receipt-link{font-family:var(--mono);font-size:11px;color:var(--horizon);border-bottom:1px solid var(--horizon);padding-bottom:1px;}
    .give-again{margin-top:18px;display:inline-block;font-family:var(--mono);font-size:12px;font-weight:600;color:var(--white);background:var(--horizon);padding:10px 20px;border-radius:100px;transition:background .25s,transform .2s;}
    .give-again:hover{background:var(--night);transform:translateY(-1px);}
    /* profile */
    .profile-card{background:var(--white);border:1px solid var(--line);border-radius:6px;padding:24px 28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;}
    .profile-left{display:flex;align-items:center;gap:18px;}
    .p-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(160deg,var(--sun-light),var(--horizon));display:flex;align-items:center;justify-content:center;font-family:var(--display);font-size:20px;font-weight:700;color:var(--night);}
    .p-name{font-family:var(--display);font-size:18px;font-weight:600;}
    .p-email{font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-top:3px;}
    .p-edit{font-family:var(--mono);font-size:12px;font-weight:600;color:var(--horizon);border:1px solid var(--horizon);padding:9px 18px;border-radius:100px;transition:background .2s,color .2s;}
    .p-edit:hover{background:var(--horizon);color:var(--white);}
    /* avatar upload */
    .p-avatar-wrap{position:relative;width:52px;height:52px;flex-shrink:0;}
    .p-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(160deg,var(--sun-light),var(--horizon));display:flex;align-items:center;justify-content:center;font-family:var(--display);font-size:20px;font-weight:700;color:var(--night);overflow:hidden;}
    .p-avatar img{width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;}
    .avatar-upload-btn{position:absolute;bottom:-2px;right:-2px;width:20px;height:20px;border-radius:50%;background:var(--horizon);border:2px solid var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;line-height:1;}
    .avatar-upload-btn:hover{background:var(--night);}
    .avatar-upload-btn svg{width:10px;height:10px;fill:none;stroke:var(--white);stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
    #avatarFileInput{display:none;}
    .avatar-uploading{opacity:.5;pointer-events:none;}
    .avatar-toast{display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:var(--night);color:var(--white);font-family:var(--mono);font-size:12px;padding:10px 20px;border-radius:100px;z-index:999;box-shadow:0 4px 16px rgba(10,27,51,.3);}
    .avatar-toast.show{display:block;}
  </style>
</head>
<body>
<?php
$member      = $memberAuth->current();
$name        = htmlspecialchars($member['display_name'] ?? $member['username'] ?? 'Friend');
$initial     = strtoupper(mb_substr($member['display_name'] ?? $member['username'] ?? 'F', 0, 1));
$username    = htmlspecialchars($member['username'] ?? '');
$email       = htmlspecialchars($member['email'] ?? '');
$pictureSrc  = !empty($member['profile_picture']) ? htmlspecialchars($member['profile_picture']) : null;
// Fetch full member record for created_at
$memberRecord = (new \Repository\MemberRepository(getDB()))->findById((int)($member['id'] ?? 0));
$memberSince  = $memberRecord ? date('Y', strtotime($memberRecord->createdAt)) : date('Y');
?>

<!-- NAV -->
<nav>
  <a href="<?= BASE_URL ?>/" class="brand">
    <img class="mark" src="<?= BASE_URL ?>/public/images/agape1.jpg" alt="Agape House">
    <div><div class="word">Agape House</div><div class="tag">San Carlos · Ministries</div></div>
  </a>
  <div class="navlinks">
    <a href="<?= BASE_URL ?>/">Home</a>
    <a href="<?= BASE_URL ?>/#watch">Watch</a>
    <a href="<?= BASE_URL ?>/#read">Read</a>
    <a href="<?= BASE_URL ?>/#bible">Bible</a>
    <a href="<?= BASE_URL ?>/#prayer">Prayer</a>
    <a href="<?= BASE_URL ?>/#events">Events</a>
  </div>
  <div class="nav-right">
    <div class="account-pill" onclick="document.getElementById('navDropdown').classList.toggle('open')">
      <div class="avatar" id="navAvatar">
        <?php if ($pictureSrc): ?>
          <img src="<?= $pictureSrc ?>" alt="<?= $name ?>">
        <?php else: ?>
          <?= $initial ?>
        <?php endif; ?>
      </div>
      <span><?= $name ?></span>
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
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="eyebrow">My Account</div>
    <h1 class="hero-title">Welcome back, <em><?= $name ?>.</em></h1>
    <p class="hero-sub">Here's what's saved, prayed over, and given — all in one place.</p>
    <div class="stat-row">
      <div class="stat"><div class="num" id="statWatched">—</div><div class="lbl">Sermons watched</div></div>
      <div class="stat"><div class="num" id="statPrayers">—</div><div class="lbl">Prayer requests</div></div>
      <div class="stat">
        <div class="num"><?= $memberSince ?></div>
        <div class="lbl">Member since</div>
      </div>
    </div>
  </div>
</section>

<!-- CONTINUE WATCHING -->
<div class="section reveal">
  <div class="section-head">
    <div class="section-title">Continue watching</div>
    <a class="section-link" href="<?= BASE_URL ?>/#watch">See all</a>
  </div>
  <div class="watch-row" id="watchRow">
    <div class="watch-card">
      <div class="watch-thumb a"><div class="wt-name">Roots</div></div>
      <div class="watch-body">
        <div class="watch-title">What It Means to Abide</div>
        <div class="progress-track"><div class="progress-fill" style="width:64%"></div></div>
        <div class="progress-label">24 of 38 min left</div>
      </div>
    </div>
    <div class="watch-card">
      <div class="watch-thumb b"><div class="wt-name">John</div></div>
      <div class="watch-body">
        <div class="watch-title">Light in the Dark</div>
        <div class="progress-track"><div class="progress-fill" style="width:22%"></div></div>
        <div class="progress-label">9 of 41 min left</div>
      </div>
    </div>
    <div class="watch-card">
      <div class="watch-thumb c"><div class="wt-name">Spirit</div></div>
      <div class="watch-body">
        <div class="watch-title">A Fire That Doesn't Consume</div>
        <div class="progress-track"><div class="progress-fill" style="width:100%"></div></div>
        <div class="progress-label">Finished · 6 min</div>
      </div>
    </div>
  </div>
</div>

<!-- PRAYER REQUESTS -->
<div class="section reveal">
  <div class="section-head">
    <div class="section-title">Your prayer requests</div>
    <a class="section-link" href="<?= BASE_URL ?>/#prayer">Submit new</a>
  </div>
  <div class="pr-list" id="prayerList">
    <div class="pr-item">
      <div class="pr-left">
        <div class="pr-date">Jul 21</div>
        <div class="pr-text">Healing for my mother during her recovery.</div>
      </div>
      <span class="status praying">Praying</span>
    </div>
    <div class="pr-item">
      <div class="pr-left">
        <div class="pr-date">Jul 08</div>
        <div class="pr-text">Peace and direction before a big job decision.</div>
      </div>
      <span class="status answered">Answered</span>
    </div>
    <div class="pr-item">
      <div class="pr-left">
        <div class="pr-date">Jun 30</div>
        <div class="pr-text">Private request — visible to care team only.</div>
      </div>
      <span class="status private">Private</span>
    </div>
  </div>
</div>

<!-- GIVING HISTORY -->
<div class="section reveal">
  <div class="section-head">
    <div class="section-title">Giving history</div>
    <a class="section-link" href="#">Download all receipts</a>
  </div>
  <div class="give-summary">
    <div><div class="g-num">₱1,240</div><div class="g-lbl">Given this year</div></div>
    <div><div class="g-num">₱95</div><div class="g-lbl">Average per gift</div></div>
    <div><div class="g-num">Monthly</div><div class="g-lbl">Recurring plan</div></div>
  </div>
  <table class="give-table">
    <thead>
      <tr><th>Date</th><th>Fund</th><th>Method</th><th>Amount</th><th></th></tr>
    </thead>
    <tbody>
      <tr><td>Jul 1, 2026</td><td>General Fund</td><td>Card ····4471</td><td class="amount">₱100.00</td><td><a class="receipt-link" href="#">Receipt</a></td></tr>
      <tr><td>Jun 1, 2026</td><td>General Fund</td><td>Card ····4471</td><td class="amount">₱100.00</td><td><a class="receipt-link" href="#">Receipt</a></td></tr>
      <tr><td>May 14, 2026</td><td>Missions</td><td>Bank transfer</td><td class="amount">₱50.00</td><td><a class="receipt-link" href="#">Receipt</a></td></tr>
    </tbody>
  </table>
  <a class="give-again" href="<?= BASE_URL ?>/#donate">Give again</a>
</div>

<!-- PROFILE -->
<div class="section reveal">
  <div class="section-head">
    <div class="section-title">Profile</div>
  </div>
  <div class="profile-card">
    <div class="profile-left">
      <div class="p-avatar-wrap" id="profileAvatarWrap">
        <div class="p-avatar" id="profileAvatar">
          <?php if ($pictureSrc): ?>
            <img src="<?= $pictureSrc ?>" alt="<?= $name ?>" id="profileAvatarImg">
          <?php else: ?>
            <span id="profileAvatarInitial"><?= $initial ?></span>
          <?php endif; ?>
        </div>
        <label class="avatar-upload-btn" for="avatarFileInput" title="Change photo" aria-label="Upload profile picture">
          <!-- pencil/camera icon -->
          <svg viewBox="0 0 16 16" aria-hidden="true">
            <path d="M11.5 2.5l2 2L5 13H3v-2L11.5 2.5z"/>
          </svg>
        </label>
        <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/gif,image/webp">
      </div>
      <div>
        <div class="p-name"><?= $name ?></div>
        <div class="p-email"><?= $email ?> · Member since <?= $memberSince ?></div>
      </div>
    </div>
    <a class="p-edit" href="#">Edit profile</a>
  </div>
  <div class="avatar-toast" id="avatarToast"></div>
</div>

<script>
  // Reveal on scroll
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const dd = document.getElementById('navDropdown');
    if (!e.target.closest('.nav-right')) dd.classList.remove('open');
  });

  // Animate stat counters
  function animateCount(el, target, prefix='', suffix='') {
    let start = 0;
    const step = Math.ceil(target / 30);
    const timer = setInterval(() => {
      start = Math.min(start + step, target);
      el.textContent = prefix + start + suffix;
      if (start >= target) clearInterval(timer);
    }, 40);
  }
  window.addEventListener('load', () => {
    animateCount(document.getElementById('statWatched'), 14);
    animateCount(document.getElementById('statPrayers'), 3);
  });

  // ── Avatar upload ────────────────────────────────────────────────────────
  const avatarInput = document.getElementById('avatarFileInput');
  const toast       = document.getElementById('avatarToast');

  function showToast(msg, isError = false) {
    toast.textContent = msg;
    toast.style.background = isError ? '#c62828' : 'var(--night)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3200);
  }

  function setAvatarSrc(url) {
    // Update profile card
    const wrap    = document.getElementById('profileAvatar');
    const initial = document.getElementById('profileAvatarInitial');
    let   img     = document.getElementById('profileAvatarImg');

    if (!img) {
      img = document.createElement('img');
      img.id  = 'profileAvatarImg';
      img.alt = '<?= $name ?>';
      if (initial) initial.replaceWith(img);
      else wrap.appendChild(img);
    }
    img.src = url;

    // Update nav avatar
    const navAv = document.getElementById('navAvatar');
    if (navAv) {
      navAv.innerHTML = `<img src="${url}" alt="<?= $name ?>" style="width:26px;height:26px;border-radius:50%;object-fit:cover;display:block;">`;
    }
  }

  avatarInput.addEventListener('change', async function () {
    const file = this.files[0];
    if (!file) return;

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
      showToast('Only JPEG, PNG, GIF, or WebP images are allowed.', true);
      this.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image must be 5 MB or smaller.', true);
      this.value = '';
      return;
    }

    const wrap = document.getElementById('profileAvatarWrap');
    wrap.classList.add('avatar-uploading');
    showToast('Uploading…');

    const fd = new FormData();
    fd.append('avatar', file);

    try {
      const res  = await fetch('<?= BASE_URL ?>/api/member/avatar', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.status === 'success') {
        setAvatarSrc(data.data.profile_picture);
        showToast('Profile picture updated.');
      } else {
        showToast(data.message || 'Upload failed.', true);
      }
    } catch (err) {
      showToast('Network error. Please try again.', true);
    } finally {
      wrap.classList.remove('avatar-uploading');
      this.value = '';
    }
  });
</script>
</body>
</html>
