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
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600&family=Source+Sans+3:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
  <style>
    :root{--plum:#332039;--plum-deep:#20142A;--plum-black:#150D1B;--mauve:#4A3155;--ember:#C1542E;--coral:#E08152;--gold:#D9A544;--sage:#6E8F6E;--cream:#FBF6EC;--paper:#FFFDF8;--ink:#241C1F;--ink-soft:#6B6058;--line:#E9E1D2;}
    *{box-sizing:border-box;}
    html,body{height:100%;margin:0;}
    body{font-family:'Source Sans 3',sans-serif;color:var(--ink);display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(115deg,var(--plum-black) 0%,var(--plum-deep) 40%,var(--mauve) 100%);padding:24px;}
    
    /* MODAL BACKDROP */
    .backdrop{position:fixed;inset:0;background:rgba(21,13,27,0.55);backdrop-filter:blur(3px);z-index:1;}
    
    /* MODAL */
    .modal{position:relative;z-index:2;width:100%;max-width:420px;background:var(--paper);border-radius:20px;box-shadow:0 30px 70px rgba(20,10,20,0.5);overflow:hidden;animation:rise 0.4s cubic-bezier(.16,1,.3,1);}
    @keyframes rise{from{opacity:0;transform:translateY(14px) scale(0.98);}to{opacity:1;transform:translateY(0) scale(1);}}
    
    /* PROFILE BANNER */
    .profile-banner{position:relative;background:linear-gradient(160deg,var(--plum-black) 0%,var(--plum-deep) 45%,var(--plum) 75%,var(--mauve) 100%);padding:44px 28px 30px;text-align:center;overflow:hidden;}
    .profile-banner::before{content:"";position:absolute;left:50%;top:-30%;transform:translateX(-50%);width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(217,165,68,0.16),transparent 70%);}
    
    /* CLOSE BUTTON */
    .close-btn{position:absolute;top:16px;right:16px;width:30px;height:30px;border-radius:50%;border:none;background:rgba(255,255,255,0.1);color:rgba(251,246,236,0.85);display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:2;transition:background 0.18s ease;}
    .close-btn:hover{background:rgba(255,255,255,0.2);}
    
    /* BANNER AVATAR */
    .banner-avatar{width:88px;height:88px;border-radius:50%;margin:0 auto 16px;background:linear-gradient(135deg,var(--coral),var(--ember));box-shadow:0 0 0 4px rgba(255,255,255,0.15),0 12px 30px rgba(20,10,20,0.4);position:relative;z-index:1;display:flex;align-items:center;justify-content:center;font-family:'Fraunces',serif;font-size:38px;font-weight:700;color:var(--paper);overflow:hidden;}
    .banner-avatar img{width:88px;height:88px;border-radius:50%;object-fit:cover;display:block;}
    
    /* PROFILE INFO */
    .profile-banner h2{font-family:'Fraunces',serif;font-weight:600;font-size:23px;color:#FBF6EC;margin:0 0 4px;position:relative;z-index:1;}
    .profile-banner .handle{font-family:'IBM Plex Mono',monospace;font-size:12.5px;color:rgba(251,246,236,0.55);margin:0 0 6px;position:relative;z-index:1;}
    .profile-banner .since{font-family:'IBM Plex Mono',monospace;font-size:9.5px;letter-spacing:0.1em;text-transform:uppercase;color:rgba(251,246,236,0.4);margin:0 0 18px;position:relative;z-index:1;}
    
    /* MEMBER BADGE */
    .member-badge{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;border-radius:999px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);font-size:12px;font-weight:700;color:#FBF6EC;position:relative;z-index:1;}
    .member-badge .dot{width:6px;height:6px;border-radius:50%;background:var(--gold);}
    
    /* PROFILE BODY */
    .profile-body{padding:26px 28px 28px;position:relative;z-index:5;}
    .section-label{font-family:'IBM Plex Mono',monospace;font-size:10.5px;letter-spacing:0.12em;text-transform:uppercase;font-weight:700;color:var(--ember);margin:0 0 14px;}
    
    /* INFO ROW */
    .info-row{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--line);}
    .info-row:last-of-type{border-bottom:none;}
    .info-row svg{color:var(--ember);flex-shrink:0;margin-top:2px;}
    .info-row .label{font-family:'IBM Plex Mono',monospace;font-size:9.5px;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-soft);margin-bottom:3px;}
    .info-row .value{font-size:14px;font-weight:600;color:var(--ink);}
    
    /* STATS & FOLLOW */
    .stats-follow{display:flex;align-items:center;justify-content:space-between;margin-top:20px;}
    .stats{display:flex;gap:20px;}
    .stat-item{text-align:left;}
    .stat-item strong{display:block;font-family:'Fraunces',serif;font-size:16px;}
    .stat-item span{font-size:11px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:0.03em;}
    
    /* FOLLOW BUTTON */
    .btn-follow{padding:10px 26px;border-radius:999px;border:none;background:linear-gradient(135deg,var(--coral),var(--ember));color:#fff;font-weight:700;font-size:13px;cursor:pointer;box-shadow:0 8px 18px rgba(193,84,46,0.3);transition:transform 0.18s ease,filter 0.18s ease;}
    .btn-follow:hover{transform:translateY(-1px);filter:brightness(1.06);}
    .btn-follow.following{background:var(--paper);color:var(--ink);border:1.5px solid var(--line);box-shadow:none;}
    .btn-follow.following:hover{border-color:var(--ember);color:var(--ember);background:var(--cream);}
    
    /* EDIT BUTTON (for own profile) */
    .btn-edit{padding:10px 26px;border-radius:999px;border:1.5px solid rgba(255,255,255,0.3);background:transparent;color:#FBF6EC;font-weight:700;font-size:13px;cursor:pointer;transition:background 0.18s ease,border-color 0.18s ease;margin-top:14px;}
    .btn-edit:hover{background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.5);}
    
    /* MESSAGE BUTTON */
    .btn-message{width:100%;padding:12px 26px;border-radius:999px;border:1.5px solid var(--line);background:#fff;color:var(--ink);font-weight:700;font-size:13px;cursor:pointer;transition:background 0.18s ease,border-color 0.18s ease,color 0.18s ease;display:flex;align-items:center;justify-content:center;gap:8px;position:relative;z-index:10;}
    .btn-message:hover{background:var(--cream);border-color:var(--ember);color:var(--ember);}
    .btn-message:disabled{opacity:0.6;cursor:not-allowed;}
    .btn-message:active{transform:scale(0.98);}
    
    @keyframes spin{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
    
    /* DIRECT MESSAGE MODAL */
    #profileDmModal{position:fixed;inset:0;z-index:9999;}
    .dm-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(3px);}
    .dm-modal{position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:500px;height:70vh;background:var(--paper);border-radius:20px 20px 0 0;display:flex;flex-direction:column;box-shadow:0 -10px 40px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;}
    @keyframes slideUp{from{transform:translateX(-50%) translateY(100%);}to{transform:translateX(-50%) translateY(0);}}
    .dm-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--line);flex-shrink:0;}
    .dm-title{margin:0;font-size:18px;font-weight:700;color:var(--ink);}
    .dm-close-btn{width:32px;height:32px;border-radius:50%;border:none;background:var(--cream);color:var(--ink);font-size:24px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;}
    .dm-close-btn:hover{background:var(--line);}
    .dm-thread{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:12px;}
    .dm-bubble{max-width:75%;padding:12px 16px;border-radius:18px;word-wrap:break-word;}
    .dm-bubble-own{align-self:flex-end;background:linear-gradient(135deg,var(--coral),var(--ember));color:#fff;border-bottom-right-radius:4px;}
    .dm-bubble-other{align-self:flex-start;background:var(--cream);color:var(--ink);border-bottom-left-radius:4px;}
    .dm-bubble-body{font-size:14px;line-height:1.4;margin-bottom:4px;}
    .dm-bubble-time{font-size:10px;opacity:0.7;text-align:right;}
    .dm-footer{display:flex;gap:12px;padding:16px 24px;border-top:1px solid var(--line);flex-shrink:0;background:var(--paper);}
    .dm-input{flex:1;padding:12px 16px;border:1.5px solid var(--line);border-radius:999px;font-size:14px;outline:none;}
    .dm-input:focus{border-color:var(--ember);}
    .dm-send-btn{padding:12px 24px;border-radius:999px;border:none;background:linear-gradient(135deg,var(--coral),var(--ember));color:#fff;font-weight:700;cursor:pointer;}
    .dm-send-btn:hover{filter:brightness(1.1);}
    .dm-send-btn:active{transform:scale(0.95);}
    
    @media(max-width:600px){.dm-modal{max-width:100%;height:80vh;border-radius:0;}}
    
    @media(max-width:440px){.modal{max-width:100%;}}
  </style>
</head>
<body>

<!-- BACKDROP -->
<div class="backdrop" onclick="window.history.back();"></div>

<!-- MODAL -->
<div class="modal">
  <!-- PROFILE BANNER -->
  <div class="profile-banner">
    <button class="close-btn" aria-label="Close" onclick="window.history.back();">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
    
    <div class="banner-avatar">
      <?php if ($pictureSrc): ?>
        <img src="<?= $pictureSrc ?>" alt="<?= $displayName ?>">
      <?php else: ?>
        <?= $initial ?>
      <?php endif; ?>
    </div>
    
    <h2><?= $displayName ?></h2>
    <p class="handle">@<?= $username ?></p>
    <p class="since">Member since <?= $memberSince ?></p>
    <span class="member-badge">
      <span class="dot"></span> Agape House Member
    </span>
    
    <?php if ($isOwnProfile): ?>
      <div>
        <a href="<?= BASE_URL ?>/portal" class="btn-edit">Edit my profile</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- PROFILE BODY -->
  <div class="profile-body">
    <p class="section-label">Profile Info</p>
    
    <div class="info-row">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <div>
        <div class="label">Display Name</div>
        <div class="value"><?= $displayName ?></div>
      </div>
    </div>

    <div class="info-row">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M16 8a4 4 0 1 0-1.17 6.83c.36.36.94.36 1.3 0M16 8v3a2.5 2.5 0 0 0 5 0V12a9 9 0 1 0-4.5 7.79"/>
      </svg>
      <div>
        <div class="label">Username</div>
        <div class="value">@<?= $username ?></div>
      </div>
    </div>

    <div class="info-row">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <path d="M16 2v4M8 2v4M3 10h18"/>
      </svg>
      <div>
        <div class="label">Member since</div>
        <div class="value"><?= $memberSince ?></div>
      </div>
    </div>

    <div class="stats-follow">
      <div class="stats">
        <div class="stat-item">
          <strong>0</strong>
          <span>Following</span>
        </div>
        <div class="stat-item">
          <strong>1</strong>
          <span>Followers</span>
        </div>
      </div>
      
      <?php if (!$isOwnProfile && $memberAuth->isLoggedIn()): ?>
        <button class="btn-follow" id="followBtn">Unfollow</button>
      <?php endif; ?>
    </div>
    
    <?php if (!$isOwnProfile && $memberAuth->isLoggedIn()): ?>
    <div style="margin-top:16px;">
      <button 
        class="btn-message" 
        id="messageBtn" 
        data-member-id="<?= htmlspecialchars($profileData['id']) ?>" 
        data-member-name="<?= $displayName ?>"
        onclick="console.log('Button clicked via onclick!'); return false;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Message
      </button>
    </div>
    <?php else: ?>
      <!-- DEBUG: Button not shown because: -->
      <?php if ($isOwnProfile): ?>
        <!-- This is your own profile -->
      <?php elseif (!$memberAuth->isLoggedIn()): ?>
        <!-- You are not logged in -->
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script>
  const BASE_URL = '<?= BASE_URL ?>/api';
  
  // Debug information
  console.log('=== Member Profile Script Loaded ===');
  console.log('BASE_URL:', BASE_URL);
  console.log('Current member logged in:', <?= $memberAuth->isLoggedIn() ? 'true' : 'false' ?>);
  console.log('Is own profile:', <?= $isOwnProfile ? 'true' : 'false' ?>);
  console.log('Profile data ID:', '<?= $profileData['id'] ?? 'undefined' ?>');
  console.log('SessionStorage test:', sessionStorage.getItem('test') === null ? 'Available' : 'Available');
  
  const followBtn = document.getElementById('followBtn');
  if (followBtn) {
    followBtn.addEventListener('click', () => {
      const isFollowing = followBtn.classList.toggle('following');
      followBtn.textContent = isFollowing ? 'Following' : 'Follow';
    });
  }
  
  // Message button - open direct message modal
  const messageBtn = document.getElementById('messageBtn');
  if (messageBtn) {
    console.log('Message button found, attaching listener');
    messageBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      
      const targetMemberId = messageBtn.dataset.memberId;
      const targetMemberName = messageBtn.dataset.memberName;
      
      console.log('Message button clicked for member:', targetMemberId, targetMemberName);
      
      if (!targetMemberId) {
        alert('Member ID not found. Please refresh and try again.');
        return;
      }
      
      messageBtn.disabled = true;
      const originalHTML = messageBtn.innerHTML;
      messageBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Opening...';
      
      try {
        // Start conversation
        const url = BASE_URL + '/messages/start/' + targetMemberId;
        console.log('Fetching:', url);
        
        const res = await fetch(url, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        });
        
        console.log('Response status:', res.status);
        
        if (!res.ok) {
          const errorText = await res.text();
          console.error('Server error response:', errorText);
          throw new Error(`Server returned ${res.status}: ${errorText}`);
        }
        
        const data = await res.json();
        console.log('Response data:', data);
        
        if (data.status === 'success') {
          console.log('✓ Conversation created successfully');
          
          // Open message modal RIGHT HERE on this page
          openDirectMessageModalOnProfile(data.data.conversation_id, data.data.other_member);
          
          // Reset button
          messageBtn.disabled = false;
          messageBtn.innerHTML = originalHTML;
        } else {
          alert(data.message || 'Could not start conversation.');
          messageBtn.disabled = false;
          messageBtn.innerHTML = originalHTML;
        }
      } catch (error) {
        console.error('Error starting conversation:', error);
        alert('Network error: ' + error.message + '. Check console for details.');
        messageBtn.disabled = false;
        messageBtn.innerHTML = originalHTML;
      }
    });
  } else {
    console.log('Message button NOT found in DOM');
  }
  
  // ========================================================================
  // DIRECT MESSAGE MODAL - Create and manage DM modal on profile page
  // ========================================================================
  
  async function openDirectMessageModalOnProfile(conversationId, otherMember) {
    console.log('Opening DM modal on profile page:', conversationId, otherMember);
    
    // Create modal if it doesn't exist
    let modal = document.getElementById('profileDmModal');
    if (!modal) {
      createProfileDmModal();
      modal = document.getElementById('profileDmModal');
    }
    
    // Set modal data
    const titleEl = modal.querySelector('.dm-title');
    const threadEl = modal.querySelector('.dm-thread');
    const inputEl = modal.querySelector('.dm-input');
    const sendBtn = modal.querySelector('.dm-send-btn');
    
    if (titleEl) titleEl.textContent = otherMember.display_name;
    
    // Store conversation info
    modal.dataset.conversationId = conversationId;
    modal.dataset.otherMemberId = otherMember.id;
    
    // Load messages
    await loadProfileDmMessages(conversationId, threadEl, otherMember);
    
    // Show modal
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    
    // Focus input
    setTimeout(() => inputEl && inputEl.focus(), 100);
    
    // Send message handler
    sendBtn.onclick = async () => {
      const body = inputEl.value.trim();
      if (!body) return;
      
      try {
        const res = await fetch(BASE_URL + '/messages/conversation/' + conversationId, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ body })
        });
        
        const data = await res.json();
        if (data.status === 'success') {
          // Add message to thread
          addMessageToThread(threadEl, data.data, true);
          inputEl.value = '';
          threadEl.scrollTop = threadEl.scrollHeight;
        }
      } catch (error) {
        console.error('Failed to send message:', error);
        alert('Failed to send message');
      }
    };
    
    // Enter to send
    inputEl.onkeypress = (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendBtn.click();
      }
    };
    
    // Close button
    modal.querySelector('.dm-close-btn').onclick = () => {
      modal.hidden = true;
      document.body.style.overflow = '';
    };
  }
  
  async function loadProfileDmMessages(conversationId, threadEl, otherMember) {
    threadEl.innerHTML = '<div style="padding:20px;text-align:center;color:#999;">Loading messages...</div>';
    
    try {
      const res = await fetch(BASE_URL + '/messages/conversation/' + conversationId, {
        credentials: 'include'
      });
      const data = await res.json();
      
      if (data.status === 'success' && data.data.messages) {
        const messages = data.data.messages;
        if (messages.length === 0) {
          threadEl.innerHTML = '<div style="padding:20px;text-align:center;color:#999;">No messages yet. Say hi!</div>';
        } else {
          threadEl.innerHTML = '';
          messages.forEach(msg => addMessageToThread(threadEl, msg, false));
          threadEl.scrollTop = threadEl.scrollHeight;
        }
      }
    } catch (error) {
      console.error('Failed to load messages:', error);
      threadEl.innerHTML = '<div style="padding:20px;text-align:center;color:#f00;">Failed to load messages</div>';
    }
  }
  
  function addMessageToThread(threadEl, msg, isNew) {
    const currentUserId = <?= $memberAuth->isLoggedIn() ? $memberAuth->current()['id'] : 'null' ?>;
    const isOwn = msg.sender_id === currentUserId;
    
    const bubble = document.createElement('div');
    bubble.className = 'dm-bubble ' + (isOwn ? 'dm-bubble-own' : 'dm-bubble-other');
    bubble.innerHTML = `
      <div class="dm-bubble-body">${escapeHtml(msg.body)}</div>
      <div class="dm-bubble-time">${formatTime(msg.created_at)}</div>
    `;
    
    threadEl.appendChild(bubble);
    if (isNew) threadEl.scrollTop = threadEl.scrollHeight;
  }
  
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return date.toLocaleDateString();
  }
  
  function createProfileDmModal() {
    const modal = document.createElement('div');
    modal.id = 'profileDmModal';
    modal.hidden = true;
    modal.innerHTML = `
      <div class="dm-overlay"></div>
      <div class="dm-modal">
        <div class="dm-header">
          <h3 class="dm-title">Message</h3>
          <button class="dm-close-btn" aria-label="Close">×</button>
        </div>
        <div class="dm-thread"></div>
        <div class="dm-footer">
          <input type="text" class="dm-input" placeholder="Type a message..." />
          <button class="dm-send-btn">Send</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
</script>
</body>
</html>
