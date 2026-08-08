<?php
/**
 * Admin Panel — Prayer Moderation + Media Management
 */
declare(strict_types=1);

// session_start() is already called in index.php
// $authService is already wired up in index.php

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path   = '/' . ltrim(str_replace(BASE_URL . '/admin', '', $uri), '/');

// ---- Logout ----
if ($method === 'POST' && $path === '/logout') {
    $authService->logout();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// ---- Require login ----
$authService->requireLogin(BASE_URL . '/login');

$currentAdmin = $authService->current();
$isLoggedIn   = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600&family=Source+Sans+3:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --plum:       #332039;
      --plum-deep:  #20142A;
      --plum-black: #150D1B;
      --ember:      #C1542E;
      --coral:      #E08152;
      --gold:       #D9A544;
      --sage:       #6E8F6E;
      --approve:    #3E7A4E;
      --reject:     #B23A2E;
      --cream:      #FBF6EC;
      --paper:      #FFFDF8;
      --ink:        #241C1F;
      --ink-soft:   #6B6058;
      --line:       #E9E1D2;
      --white:      #FFFFFF;
      --gold-dim:   rgba(217,165,68,.15);
      --radius:     6px;
      /* legacy aliases so existing JS-generated cards keep working */
      --night:      #150D1B;
      --dusk:       #332039;
      --horizon:    #C1542E;
      --sun:        #E08152;
      --sun-light:  #FBF6EC;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Source Sans 3', system-ui, sans-serif; background: var(--cream); color: var(--ink); line-height: 1.6; -webkit-font-smoothing: antialiased; }

    /* ── Top Bar ── */
    .admin-header {
      background: var(--plum-black);
      border-bottom: 2px solid var(--ember);
      position: sticky; top: 0; z-index: 100;
    }
    .admin-header .inner {
      padding: 0 32px;
      display: flex; align-items: center; justify-content: space-between; height: 60px;
    }
    .admin-brand { display: flex; align-items: center; gap: 12px; }
    .admin-brand-mark {
      width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
      background: radial-gradient(circle at 35% 30%, var(--coral), var(--ember) 70%);
      box-shadow: 0 0 0 2px rgba(255,255,255,0.18);
    }
    .admin-brand-name { font-family: 'Fraunces', serif; font-weight: 600; font-size: 15px; color: var(--cream); line-height: 1.2; }
    .admin-brand-tag  { font-family: 'IBM Plex Mono', monospace; font-size: 9px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--gold); margin-top: 1px; display: block; }
    .admin-header .admin-meta { display: flex; align-items: center; gap: 16px; }
    .admin-user { font-size: 12.5px; font-weight: 600; color: rgba(251,246,236,0.75); display: flex; align-items: center; gap: 7px; }
    .admin-user-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--sage); flex-shrink: 0; }
    .admin-header .logout-form { margin: 0; }
    .admin-header .logout-form button {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--reject); color: var(--white); border: none;
      padding: 8px 18px; border-radius: 8px;
      cursor: pointer; font-size: 12.5px; font-weight: 700;
      font-family: inherit; transition: filter .18s ease;
    }
    .admin-header .logout-form button:hover { filter: brightness(1.12); }

    /* ── Sidebar ── */
    .admin-layout { display: flex; min-height: calc(100vh - 60px); }
    .sidebar {
      width: 260px; background: var(--paper); border-right: 1px solid var(--line);
      flex-shrink: 0; overflow-y: auto; position: sticky; top: 60px; height: calc(100vh - 60px);
    }
    .sidebar-inner { padding: 24px 16px; }
    .tab-btn {
      display: flex; align-items: center; gap: 10px; width: 100%;
      padding: 12px 16px; background: none; border: none;
      border-left: 3px solid transparent;
      font-size: 14px; font-weight: 600; color: var(--ink-soft);
      cursor: pointer; font-family: inherit; text-align: left;
      transition: all .2s ease; border-radius: 8px; margin-bottom: 4px;
    }
    .tab-btn:hover { color: var(--ink); background: rgba(51,32,57,0.04); }
    .tab-btn.active {
      color: var(--plum-deep); background: rgba(193,84,46,0.08);
      border-left-color: var(--ember);
    }
    .tab-btn svg { flex-shrink: 0; }

    /* ── Container ── */
    .container { flex: 1; padding: 36px 32px; overflow-x: auto; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .section-title { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 600; color: var(--plum-deep); margin-bottom: 24px; }

    /* ── Login ── */
    .login-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-card { background: var(--paper); border-radius: 14px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 6px 28px rgba(51,32,57,.12); border: 1px solid var(--line); }
    .login-card h2 { font-family: 'Fraunces', serif; margin-bottom: 24px; font-size: 22px; color: var(--plum-deep); }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
    .field input, .field select, .field textarea {
      width: 100%; padding: 10px 12px; border: 1px solid var(--line);
      border-radius: var(--radius); font-size: 14px; font-family: inherit;
      background: var(--white); color: var(--ink); transition: border-color .2s, box-shadow .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus {
      outline: none; border-color: var(--ember); box-shadow: 0 0 0 3px rgba(193,84,46,.18);
    }
    .field textarea { resize: vertical; }
    .btn-login {
      width: 100%; padding: 12px; background: var(--plum); color: var(--cream);
      border: none; border-radius: var(--radius); font-size: 15px; font-weight: 700;
      cursor: pointer; margin-top: 8px; font-family: inherit; transition: filter .18s ease;
    }
    .btn-login:hover { filter: brightness(1.15); }
    .error-msg { background: rgba(178,58,46,.08); color: var(--reject); padding: 12px; border-radius: var(--radius); margin-bottom: 16px; font-size: 14px; border: 1px solid rgba(178,58,46,.25); }

    /* ── Prayer cards ── */
    .prayer-card {
      background: var(--paper); border-radius: 14px; padding: 22px 24px;
      margin-bottom: 14px; border: 1px solid var(--line);
      box-shadow: 0 6px 18px rgba(42,36,28,.05);
      transition: box-shadow .2s, transform .2s;
    }
    .prayer-card:hover { box-shadow: 0 8px 24px rgba(42,36,28,.1); transform: translateY(-1px); }
    .prayer-card .p-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; color: var(--ink-soft); }
    .prayer-card .p-name { font-family: 'Fraunces', serif; font-weight: 700; color: var(--plum-deep); font-size: 15px; }
    .cat-badge { background: rgba(110,143,110,.14); color: var(--sage); padding: 3px 10px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-left: 8px; }
    .prayer-card .p-body { color: var(--ink); margin-bottom: 16px; font-size: 13.5px; line-height: 1.65; }
    .prayer-actions { display: flex; gap: 10px; }
    .btn-approve {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--approve); color: var(--white); border: none;
      padding: 8px 18px; border-radius: 8px; cursor: pointer;
      font-weight: 700; font-size: 12.5px; font-family: inherit; transition: filter .18s ease, transform .18s ease;
    }
    .btn-reject {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--reject); color: var(--white); border: none;
      padding: 8px 18px; border-radius: 8px; cursor: pointer;
      font-weight: 700; font-size: 12.5px; font-family: inherit; transition: filter .18s ease, transform .18s ease;
    }
    .btn-approve:hover { filter: brightness(1.12); transform: translateY(-1px); }
    .btn-reject:hover  { filter: brightness(1.12); transform: translateY(-1px); }

    /* ── Media table ── */
    .media-table { width: 100%; border-collapse: collapse; background: var(--paper); border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(42,36,28,.05); }
    .media-table th { background: var(--plum-black); color: var(--cream); font-family: 'IBM Plex Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; padding: 13px 16px; text-align: left; font-weight: 500; }
    .media-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; color: var(--ink); }
    .media-table tr:last-child td { border-bottom: none; }
    .media-table tr:hover td { background: rgba(251,246,236,.6); }
    .type-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .type-sermon     { background: rgba(193,84,46,.1);  color: var(--ember); }
    .type-devotional { background: rgba(110,143,110,.14); color: var(--sage); }
    .type-testimony  { background: rgba(217,165,68,.15); color: #7a5200; }
    .type-worship    { background: rgba(224,129,82,.12); color: var(--coral); }
    .url-cell { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--ember); font-size: 12px; }
    .url-cell.empty { color: var(--reject); font-style: italic; }
    .btn-edit {
      background: var(--plum); color: var(--cream); border: none;
      padding: 6px 14px; border-radius: 8px; cursor: pointer;
      font-size: 12px; font-weight: 700; font-family: inherit; white-space: nowrap; transition: filter .18s ease;
    }
    .btn-edit:hover { filter: brightness(1.2); }

    /* ── Edit modal ── */
    .edit-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .edit-modal[hidden] { display: none; }
    .edit-backdrop { position: absolute; inset: 0; background: rgba(21,13,27,.55); backdrop-filter: blur(2px); }
    .edit-box {
      position: relative; background: var(--paper); border-radius: 14px;
      padding: 32px; width: 100%; max-width: 560px;
      box-shadow: 0 16px 48px rgba(51,32,57,.2); border: 1px solid var(--line);
    }
    .edit-box h3 { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; margin-bottom: 22px; color: var(--plum-deep); }
    .edit-box .field input { font-size: 13px; }
    .edit-box .field label { font-size: 12px; color: var(--ink-soft); }
    .edit-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; }
    .btn-save {
      background: var(--plum); color: var(--cream); border: none;
      padding: 10px 26px; border-radius: 8px; font-weight: 700;
      cursor: pointer; font-family: inherit; transition: filter .18s ease;
    }
    .btn-cancel {
      background: var(--cream); color: var(--ink-soft); border: 1px solid var(--line);
      padding: 10px 20px; border-radius: 8px; font-weight: 600;
      cursor: pointer; font-family: inherit; transition: background .18s;
    }
    .btn-save:hover   { filter: brightness(1.2); }
    .btn-cancel:hover { background: var(--line); }
    .save-msg { font-size: 13px; margin-top: 10px; min-height: 20px; }
    .save-msg.ok  { color: var(--approve); }
    .save-msg.err { color: var(--reject); }

    .empty-state { text-align: center; padding: 64px 20px; color: var(--ink-soft); font-size: 15px; }
    .loading-state { text-align: center; padding: 40px 20px; color: var(--ink-soft); }

    /* ── Events ── */
    .event-admin-card {
      background: var(--paper); border-radius: 14px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 6px 18px rgba(42,36,28,.05);
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
      transition: box-shadow .2s, transform .2s;
    }
    .event-admin-card:hover { box-shadow: 0 8px 24px rgba(42,36,28,.1); transform: translateY(-1px); }
    .event-admin-meta { flex: 1; min-width: 0; }
    .event-admin-title { font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; color: var(--plum-deep); margin-bottom: 4px; }
    .event-admin-detail { font-size: 13px; color: var(--ink-soft); }
    .event-admin-count { background: rgba(193,84,46,.1); color: var(--ember); padding: 4px 12px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .btn-view-reg {
      background: var(--plum); color: var(--cream); border: none;
      padding: 7px 16px; border-radius: 8px; font-size: 12.5px;
      font-weight: 700; cursor: pointer; font-family: inherit; white-space: nowrap; transition: filter .18s ease;
    }
    .btn-view-reg:hover { filter: brightness(1.2); }
    .reg-table { width: 100%; border-collapse: collapse; background: var(--paper); border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(42,36,28,.05); }
    .reg-table th { background: var(--plum-black); color: var(--cream); font-family: 'IBM Plex Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; padding: 13px 16px; text-align: left; font-weight: 500; }
    .reg-table td { padding: 12px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    .reg-table tr:last-child td { border-bottom: none; }
    .reg-table tr:hover td { background: rgba(251,246,236,.6); }
    .join-badge-online    { background: rgba(193,84,46,.1); color: var(--ember); padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .join-badge-in_person { background: rgba(110,143,110,.14); color: var(--sage); padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }

    /* ── Users Panel ── */
    .users-table { width: 100%; border-collapse: collapse; background: var(--paper); border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(42,36,28,.05); }
    .users-table th { background: var(--plum-black); color: var(--cream); font-family: 'IBM Plex Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; padding: 13px 16px; text-align: left; font-weight: 500; }
    .users-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; color: var(--ink); }
    .users-table tr:last-child td { border-bottom: none; }
    .users-table tr:hover td { background: rgba(251,246,236,.6); }
    .user-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: var(--line); }
    .user-info { display: flex; align-items: center; gap: 12px; }
    .user-details { display: flex; flex-direction: column; gap: 2px; }
    .user-name { font-weight: 600; color: var(--plum-deep); }
    .user-email { font-size: 12px; color: var(--ink-soft); }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-active   { background: rgba(110,143,110,.14); color: var(--sage); }
    .status-inactive { background: rgba(217,165,68,.15); color: #7a5200; }
    .status-banned   { background: rgba(178,58,46,.1); color: var(--reject); }
    .btn-user-action {
      background: var(--plum); color: var(--cream); border: none;
      padding: 6px 14px; border-radius: 8px; cursor: pointer;
      font-size: 12px; font-weight: 700; font-family: inherit; white-space: nowrap; transition: filter .18s ease;
    }
    .btn-user-action:hover { filter: brightness(1.2); }

    /* ── User Detail Modal ── */
    .user-detail-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .user-detail-modal[hidden] { display: none; }
    .user-detail-backdrop { position: absolute; inset: 0; background: rgba(21,13,27,.55); backdrop-filter: blur(2px); }
    .user-detail-box {
      position: relative; background: var(--paper); border-radius: 14px;
      padding: 32px; width: 100%; max-width: 600px;
      box-shadow: 0 16px 48px rgba(51,32,57,.2); border: 1px solid var(--line);
    }
    .user-detail-box h3 { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; margin-bottom: 22px; color: var(--plum-deep); }
    .user-detail-section { margin-bottom: 20px; }
    .user-detail-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-soft); margin-bottom: 6px; }
    .user-detail-value { font-size: 14px; color: var(--ink); }
    .user-detail-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; flex-wrap: wrap; }
    .btn-status-change {
      padding: 8px 16px; border-radius: 8px; border: none;
      font-size: 12.5px; font-weight: 700; font-family: inherit;
      cursor: pointer; transition: filter .18s ease;
    }
    .btn-status-active   { background: var(--approve); color: var(--white); }
    .btn-status-inactive { background: var(--gold); color: var(--white); }
    .btn-status-banned   { background: var(--reject); color: var(--white); }
    .btn-status-change:hover { filter: brightness(1.12); }
    
    /* Search input focus */
    #usersSearchInput:focus { outline: none; border-color: var(--ember); box-shadow: 0 0 0 3px rgba(193,84,46,.18); }

    /* ── Announcements ── */
    .btn-new-ann {
      background: var(--plum); color: var(--cream); border: none;
      padding: 10px 22px; border-radius: 8px; font-size: 13px;
      font-weight: 700; cursor: pointer; font-family: inherit; transition: filter .18s ease;
    }
    .btn-new-ann:hover { filter: brightness(1.2); }

    .ann-admin-card {
      background: var(--paper); border-radius: 14px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 6px 18px rgba(42,36,28,.05);
      display: flex; align-items: flex-start; gap: 16px;
      transition: box-shadow .2s, transform .2s;
    }
    .ann-admin-card:hover { box-shadow: 0 8px 24px rgba(42,36,28,.1); transform: translateY(-1px); }
    .ann-admin-card .ann-badge { flex-shrink: 0; padding: 3px 10px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .ann-badge-Ministry  { background: rgba(193,84,46,.1);   color: var(--ember); }
    .ann-badge-Events    { background: rgba(110,143,110,.14); color: var(--sage); }
    .ann-badge-Community { background: rgba(217,165,68,.15);  color: #7a5200; }
    .ann-badge-Urgent    { background: rgba(178,58,46,.1);    color: var(--reject); }
    .ann-admin-body { flex: 1; min-width: 0; }
    .ann-admin-title { font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; color: var(--plum-deep); margin-bottom: 4px; }
    .ann-admin-excerpt { font-size: 13px; color: var(--ink-soft); line-height: 1.55; }
    .ann-admin-meta { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink-soft); margin-top: 6px; opacity: .7; }
    .ann-pinned-chip {
      display: inline-block; background: var(--gold-dim); color: #7a5200;
      border: 1px solid rgba(217,165,68,.4); padding: 1px 8px;
      border-radius: 999px; font-size: 11px; font-weight: 600; margin-left: 8px;
    }
    .ann-admin-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .btn-ann-edit {
      background: var(--plum); color: var(--cream); border: none;
      padding: 6px 14px; border-radius: 8px; cursor: pointer;
      font-size: 12px; font-weight: 700; font-family: inherit; transition: filter .18s ease;
    }
    .btn-ann-delete {
      background: var(--reject); color: var(--white); border: none;
      padding: 6px 14px; border-radius: 8px; cursor: pointer;
      font-size: 12px; font-weight: 700; font-family: inherit; transition: filter .18s ease;
    }
    .btn-ann-edit:hover   { filter: brightness(1.2); }
    .btn-ann-delete:hover { filter: brightness(1.12); }

    /* announcement / event form modal */
    .ann-form-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .ann-form-modal[hidden] { display: none; }
    .ann-form-backdrop { position: absolute; inset: 0; background: rgba(21,13,27,.55); backdrop-filter: blur(2px); }
    .ann-form-box {
      position: relative; background: var(--paper); border-radius: 14px;
      padding: 32px; width: 100%; max-width: 600px;
      box-shadow: 0 16px 48px rgba(51,32,57,.2); border: 1px solid var(--line);
      max-height: 90vh; overflow-y: auto;
    }
    .ann-form-box h3 { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; margin-bottom: 22px; color: var(--plum-deep); }
    .ann-form-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; }
    .ann-form-msg { font-size: 13px; margin-top: 10px; min-height: 20px; }
    .ann-form-msg.ok  { color: var(--approve); }
    .ann-form-msg.err { color: var(--reject); }

    /* ── Quiz admin ── */
    .quiz-admin-card {
      background: var(--paper); border-radius: 14px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 6px 18px rgba(42,36,28,.05);
      display: flex; align-items: flex-start; gap: 16px;
      transition: box-shadow .2s, transform .2s;
    }
    .quiz-admin-card:hover { box-shadow: 0 8px 24px rgba(42,36,28,.1); transform: translateY(-1px); }
    .quiz-admin-meta { flex: 1; min-width: 0; }
    .quiz-admin-title { font-family: 'Fraunces', serif; font-size: 15px; font-weight: 600; color: var(--plum-deep); margin-bottom: 3px; }
    .quiz-admin-desc  { font-size: 13px; color: var(--ink-soft); }
    .quiz-admin-count { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink-soft); margin-top: 4px; }
    .quiz-cat-badge   { background: rgba(193,84,46,.1); color: var(--ember); padding: 3px 10px; border-radius: 999px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; flex-shrink: 0; }
    .quiz-admin-actions { display: flex; gap: 8px; flex-shrink: 0; }

    /* ── Quiz form modal ── */
    .quiz-form-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: flex-start; justify-content: center; padding: 24px 20px; overflow-y: auto; }
    .quiz-form-modal[hidden] { display: none; }
    .quiz-form-backdrop { position: fixed; inset: 0; background: rgba(10,27,51,.6); backdrop-filter: blur(2px); }
    .quiz-form-box {
      position: relative; background: #1e2533; border-radius: 14px;
      padding: 32px; width: 100%; max-width: 680px;
      box-shadow: 0 20px 60px rgba(10,27,51,.4); border: 1px solid rgba(255,255,255,.08);
      color: #e8eef4;
    }
    .quiz-form-box h3 { font-family: 'Fraunces', serif; font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 4px; }
    .quiz-form-box .quiz-form-sub { font-size: 13px; color: #8fa9c4; margin-bottom: 28px; }

    .quiz-form-row { display: grid; grid-template-columns: 1fr 220px; gap: 16px; margin-bottom: 16px; }
    .quiz-form-row .qf-field { display: flex; flex-direction: column; gap: 6px; }
    .qf-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .qf-field label { font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #8fa9c4; }
    .qf-field input, .qf-field select, .qf-field textarea {
      background: #252d3d; border: 1px solid rgba(255,255,255,.1); border-radius: 8px;
      padding: 10px 14px; font-size: 14px; color: #e8eef4; font-family: inherit;
      transition: border-color .2s, box-shadow .2s;
    }
    .qf-field input:focus, .qf-field select:focus, .qf-field textarea:focus {
      outline: none; border-color: #3e7cb1; box-shadow: 0 0 0 3px rgba(62,124,177,.25);
    }
    .qf-field select option { background: #252d3d; }
    .qf-checkbox { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .qf-checkbox input { width: auto; accent-color: #3e7cb1; }
    .qf-checkbox label { font-size: 13px; color: #8fa9c4; cursor: pointer; }

    .quiz-form-divider { height: 1px; background: rgba(255,255,255,.08); margin: 24px 0 20px; }
    .quiz-form-section-title { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 16px; }

    /* Question block */
    .quiz-question-block {
      background: #252d3d; border-radius: 10px; padding: 18px 20px;
      margin-bottom: 14px; border: 1px solid rgba(255,255,255,.07);
    }
    .quiz-qblock-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .quiz-qblock-label  { font-size: 12px; font-weight: 600; color: #8fa9c4; text-transform: uppercase; letter-spacing: .06em; }
    .quiz-qblock-remove { background: none; border: none; color: #8fa9c4; cursor: pointer; font-size: 18px; line-height: 1; padding: 0; transition: color .15s; }
    .quiz-qblock-remove:hover { color: #ef5350; }
    .quiz-qblock-qtxt {
      width: 100%; background: #1a2030; border: 1px solid rgba(255,255,255,.1);
      border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #e8eef4;
      font-family: inherit; margin-bottom: 14px; transition: border-color .2s;
    }
    .quiz-qblock-qtxt:focus { outline: none; border-color: #3e7cb1; }
    .quiz-choices-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .quiz-choice-row { display: flex; align-items: center; gap: 8px; }
    .quiz-choice-row input[type="radio"] { flex-shrink: 0; accent-color: #3e7cb1; cursor: pointer; }
    .quiz-choice-row input[type="text"] {
      flex: 1; background: #1a2030; border: 1px solid rgba(255,255,255,.1);
      border-radius: 6px; padding: 9px 12px; font-size: 13px; color: #e8eef4;
      font-family: inherit; transition: border-color .2s;
    }
    .quiz-choice-row input[type="text"]:focus { outline: none; border-color: #3e7cb1; }
    .quiz-choice-hint { font-size: 11px; color: #8fa9c4; margin-top: 10px; }

    .quiz-add-q-btn {
      width: 100%; padding: 13px; background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.1); border-radius: 8px;
      color: #8fa9c4; font-size: 13px; font-weight: 600; font-family: inherit;
      cursor: pointer; transition: background .15s, color .15s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .quiz-add-q-btn:hover { background: rgba(255,255,255,.1); color: #e8eef4; }

    .quiz-form-footer { display: flex; gap: 12px; margin-top: 28px; }
    .quiz-btn-draft {
      flex: 1; padding: 13px; background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.15); border-radius: 8px;
      color: #e8eef4; font-size: 14px; font-weight: 600; font-family: inherit;
      cursor: pointer; transition: background .15s;
    }
    .quiz-btn-draft:hover { background: rgba(255,255,255,.12); }
    .quiz-btn-publish {
      flex: 1; padding: 13px; background: #2563eb;
      border: none; border-radius: 8px;
      color: #fff; font-size: 14px; font-weight: 600; font-family: inherit;
      cursor: pointer; transition: background .15s;
    }
    .quiz-btn-publish:hover { background: #1d4ed8; }
    .quiz-form-msg { font-size: 13px; margin-top: 10px; min-height: 18px; text-align: center; }
    .quiz-form-msg.ok  { color: #4caf50; }
    .quiz-form-msg.err { color: #ef5350; }

    /* ── Confirm Dialog Modal ── */
    .confirm-modal { position: fixed; inset: 0; z-index: 600; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .confirm-modal[hidden] { display: none; }
    .confirm-backdrop { position: absolute; inset: 0; background: rgba(21,13,27,.6); backdrop-filter: blur(3px); }
    .confirm-box {
      position: relative; background: var(--paper); border-radius: 14px;
      padding: 32px 28px 24px; width: 100%; max-width: 400px;
      box-shadow: 0 20px 60px rgba(51,32,57,.25); border: 1px solid var(--line);
      text-align: center;
    }
    .confirm-icon {
      width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 22px; margin: 0 auto 16px; background: rgba(178,58,46,.1);
    }
    .confirm-box h4 { font-family: 'Fraunces', serif; font-size: 17px; font-weight: 600; color: var(--plum-deep); margin-bottom: 8px; }
    .confirm-box p  { font-size: 14px; color: var(--ink-soft); margin-bottom: 24px; line-height: 1.5; }
    .confirm-actions { display: flex; gap: 10px; justify-content: center; }
    .confirm-actions .btn-cancel { min-width: 100px; }
    .confirm-actions .btn-confirm-ok {
      min-width: 100px; padding: 10px 20px; background: var(--reject); color: var(--white);
      border: none; border-radius: 8px; font-weight: 700; font-size: 14px;
      cursor: pointer; font-family: inherit; transition: filter .18s ease;
    }
    .confirm-actions .btn-confirm-ok:hover { filter: brightness(1.12); }

    @media (max-width: 720px) {
      .admin-header .inner { padding: 0 18px; }
      .admin-layout { flex-direction: column; }
      .sidebar { width: 100%; height: auto; position: static; border-right: none; border-bottom: 1px solid var(--line); }
      .sidebar-inner { padding: 12px 18px; display: flex; overflow-x: auto; gap: 4px; }
      .tab-btn { margin-bottom: 0; width: auto; white-space: nowrap; }
      .container { padding: 26px 18px 60px; }
    }
  </style>
</head>
<body>

<!-- ── Admin Dashboard ───────────────────────────────────────────── -->
<div class="admin-header">
  <div class="inner">
    <div class="admin-brand">
      <div class="admin-brand-mark"></div>
      <div>
        <span class="admin-brand-name"><?= APP_NAME ?></span>
        <span class="admin-brand-tag">Admin Panel</span>
      </div>
    </div>
    <div class="admin-meta">
      <span class="admin-user">
        <span class="admin-user-dot"></span>
        <?= htmlspecialchars($currentAdmin['display_name'] ?? $currentAdmin['username'] ?? 'Admin') ?>
      </span>
      <form method="POST" action="<?= BASE_URL ?>/admin/logout" class="logout-form">
        <button type="submit">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </button>
      </form>
    </div>
  </div>
</div>

<div class="admin-layout">
  <!-- ── Sidebar Navigation ── -->
  <div class="sidebar">
    <div class="sidebar-inner">
      <button class="tab-btn active" data-tab="prayers">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        Prayer Requests
      </button>
      <button class="tab-btn" data-tab="articles">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Articles
      </button>
      <button class="tab-btn" data-tab="media-pending">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
        Videos
      </button>
      <button class="tab-btn" data-tab="media">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        All Media
      </button>
      <button class="tab-btn" data-tab="announcements">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4v6l2 4H6l2-4V4h8z"/><path d="M12 14v6"/></svg>
        Announcements
      </button>
      <button class="tab-btn" data-tab="gallery">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        Gallery
      </button>
      <button class="tab-btn" data-tab="events">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Events
      </button>
      <button class="tab-btn" data-tab="quizzes">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        Quizzes
      </button>
      <button class="tab-btn" data-tab="messages">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Messages
      </button>
      <button class="tab-btn" data-tab="users">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Users
      </button>
    </div>
  </div>

<div class="container">

  <!-- ── Prayer Panel ── -->
  <div class="tab-panel active" id="tab-prayers">
    <div class="section-title">Pending Prayer Requests</div>
    <div id="pendingList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Articles Pending Panel ── -->
  <div class="tab-panel" id="tab-articles">
    <div class="section-title">Pending Articles</div>
    <div id="articlePendingList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Videos Pending Panel ── -->
  <div class="tab-panel" id="tab-media-pending">
    <div class="section-title">Pending Videos</div>
    <div id="videoPendingList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Media Panel ── -->
  <div class="tab-panel" id="tab-media">
    <div class="section-title">All Approved Media · Video URLs</div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      Click <strong>Edit</strong> on any row to set or update its YouTube / video URL.
      Paste a full YouTube link like <code style="background:var(--paper);padding:2px 6px;border-radius:4px;font-size:13px;">https://www.youtube.com/watch?v=XXXXX</code>.
    </p>
    <div id="mediaList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Announcements Panel ── -->
  <div class="tab-panel" id="tab-announcements">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
      <div class="section-title" style="margin-bottom:0;">Announcements</div>
      <button class="btn-new-ann" id="openNewAnnBtn">+ New Announcement</button>
    </div>
    <div id="annAdminList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Gallery Panel ── -->
  <div class="tab-panel" id="tab-gallery">
    <div class="section-title">Pending Gallery Photos</div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      Review member-submitted photos. Once approved, they will appear in the community gallery feed.
    </p>
    <div id="galleryPendingList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Events Panel ── -->
  <div class="tab-panel" id="tab-events">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
      <div class="section-title" style="margin-bottom:0;">Events &amp; Registrations</div>
      <button class="btn-new-ann" id="openNewEventBtn">+ New Event</button>
    </div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      Click <strong>View Registrants</strong> on any event to see who has signed up and how they plan to join.
    </p>
    <div id="eventsAdminList"><div class="loading-state">Loading…</div></div>
    <div id="eventsRegistrantBox" style="display:none; margin-top:32px;">
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px;">
        <h3 id="eventsRegistrantTitle" style="font-family:'Fraunces',serif;font-size:17px; font-weight:600;color:var(--night);"></h3>
        <button class="btn-cancel" id="eventsRegistrantClose" style="padding:6px 14px; font-size:13px;">✕ Close</button>
      </div>
      <div id="eventsRegistrantContent"></div>
    </div>
  </div>

  <!-- ── Quizzes Panel ── -->
  <div class="tab-panel" id="tab-quizzes">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
      <div class="section-title" style="margin-bottom:0;">Quizzes</div>
      <button class="btn-new-ann" id="openNewQuizBtn">+ New Quiz</button>
    </div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      Quizzes created here are saved to the browser and appear on the Quizzes page immediately.
    </p>
    <div id="quizAdminList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Messages Panel ── -->
  <div class="tab-panel" id="tab-messages">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
      <div class="section-title" style="margin-bottom:0;">Connect Messages</div>
      <span id="msgUnreadBadge" style="font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; background:var(--horizon,#e07b3a); color:#fff; display:none;"></span>
    </div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      Messages sent by visitors through the Connect form. Click <strong>Reply</strong> to open the live chat thread.
    </p>
    <div id="messagesList"><div class="loading-state">Loading…</div></div>
  </div>

  <!-- ── Users Panel ── -->
  <div class="tab-panel" id="tab-users">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
      <div class="section-title" style="margin-bottom:0;">Registered Users</div>
      <div style="font-family:'IBM Plex Mono', monospace; font-size:13px; color:var(--ink-soft);">
        Total: <span id="usersTotalCount">0</span> | Showing: <span id="usersShowingCount">0</span>
      </div>
    </div>
    <p style="margin-bottom:20px; font-size:14px; color:var(--ink-soft);">
      All registered members of the website. You can view their details and manage their account status.
    </p>
    
    <!-- Search Bar -->
    <div style="margin-bottom:20px;">
      <div style="position:relative; max-width:500px;">
        <input type="text" id="usersSearchInput" placeholder="Search by name, username, or email..." 
               style="width:100%; padding:12px 40px 12px 16px; border:1.5px solid var(--line); border-radius:8px; 
                      font-size:14px; font-family:inherit; background:var(--white); color:var(--ink); 
                      transition:border-color .2s, box-shadow .2s;">
        <svg style="position:absolute; right:14px; top:50%; transform:translateY(-50%); pointer-events:none; opacity:0.5;" 
             width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
      </div>
      <div id="usersSearchMsg" style="font-size:12px; color:var(--ink-soft); margin-top:8px;"></div>
    </div>
    
    <div id="usersList"><div class="loading-state">Loading…</div></div>
  </div>

</div><!-- /container -->

</div><!-- /admin-layout -->

<!-- ── Admin Chat Modal ──────────────────────────────────────────────────── -->
<div class="ann-form-modal" id="adminChatModal" hidden>
  <div class="ann-form-backdrop" id="adminChatBackdrop"></div>
  <div class="ann-form-box" style="max-width:580px;display:flex;flex-direction:column;max-height:88vh;padding:24px;">
    <!-- Header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-shrink:0;margin-bottom:14px;">
      <div>
        <h3 id="adminChatTitle" style="margin:0;font-size:17px;font-family:'Fraunces',serif;">Chat</h3>
        <p id="adminChatMeta" style="margin:3px 0 0;font-size:12px;color:var(--ink-soft,#888);"></p>
      </div>
      <button class="btn-cancel" id="adminChatClose" style="padding:5px 12px;font-size:13px;flex-shrink:0;">✕ Close</button>
    </div>
    <!-- Original message bubble -->
    <div id="adminChatOriginal" style="background:var(--paper,#f5f0e8);border-radius:8px;padding:12px 14px;font-size:13px;line-height:1.65;margin-bottom:14px;border-left:3px solid #CD7642;flex-shrink:0;"></div>
    <!-- Thread -->
    <div id="adminChatThread" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:2px 0 6px;min-height:80px;"></div>
    <!-- Guest notice -->
    <div id="adminChatNoMember" style="font-size:12px;color:var(--ink-soft,#888);padding:8px 0 4px;display:none;">
      ⚠ This visitor submitted without a member account — they won't receive a push notification, but the reply is saved in the thread.
    </div>
    <!-- Reply form -->
    <div style="border-top:1px solid var(--border,#e8e3dc);padding-top:14px;flex-shrink:0;margin-top:10px;">
      <textarea id="adminChatInput" rows="3" placeholder="Write your reply…" maxlength="3000"
        style="width:100%;resize:vertical;font-family:inherit;font-size:14px;border:1.5px solid var(--border,#e8e3dc);border-radius:8px;padding:10px 12px;color:var(--ink,#1a1a1a);background:var(--surface,#fff);box-sizing:border-box;outline:none;"></textarea>
      <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:10px;">
        <span id="adminChatMsg" style="font-size:12px;color:#CD7642;flex:1;"></span>
        <button class="btn-save" id="adminChatSend">Send reply</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Announcement Form Modal ── -->
<div class="ann-form-modal" id="annFormModal" hidden>
  <div class="ann-form-backdrop" id="annFormBackdrop"></div>
  <div class="ann-form-box">
    <h3 id="annFormTitle">New Announcement</h3>
    <input type="hidden" id="annFormId">

    <div class="field">
      <label for="annFTitle">Title</label>
      <input type="text" id="annFTitle" placeholder="Announcement headline" maxlength="255">
    </div>
    <div class="field">
      <label for="annFBody">Body</label>
      <textarea id="annFBody" rows="5" placeholder="Write the full announcement here…"></textarea>
    </div>
    <div class="field">
      <label for="annFCategory">Category</label>
      <select id="annFCategory">
        <option value="Ministry">Ministry</option>
        <option value="Events">Events</option>
        <option value="Community">Community</option>
        <option value="Urgent">Urgent</option>
      </select>
    </div>
    <div class="field" style="display:flex; align-items:center; gap:10px;">
      <input type="checkbox" id="annFPinned" style="width:auto; margin:0;">
      <label for="annFPinned" style="margin:0; font-size:13px; font-weight:600; cursor:pointer;">Pin this announcement (shows as featured banner)</label>
    </div>

    <div class="ann-form-actions">
      <button class="btn-cancel" id="annFormCancel">Cancel</button>
      <button class="btn-save"   id="annFormSave">Save</button>
    </div>
    <div class="ann-form-msg" id="annFormMsg"></div>
  </div>
</div>

<!-- ── Event Form Modal ── -->
<div class="ann-form-modal" id="eventFormModal" hidden>
  <div class="ann-form-backdrop" id="eventFormBackdrop"></div>
  <div class="ann-form-box">
    <h3 id="eventFormTitle">New Event</h3>

    <div class="field">
      <label for="evFTitle">Title *</label>
      <input type="text" id="evFTitle" placeholder="Event name" maxlength="255">
    </div>
    <div class="field">
      <label for="evFDescription">Description</label>
      <textarea id="evFDescription" rows="4" placeholder="Describe the event…"></textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
      <div class="field" id="evDateWrap">
        <label for="evFDate">Event Date *</label>
        <input type="date" id="evFDate">
      </div>
      <div class="field">
        <label for="evFTime">Start Time *</label>
        <input type="time" id="evFTime">
      </div>
    </div>

    <div class="field">
      <label for="evFLocation">Location</label>
      <input type="text" id="evFLocation" placeholder="e.g. Agape House Sanctuary" maxlength="255">
    </div>

    <div class="field" style="display:flex; align-items:center; gap:10px;">
      <input type="checkbox" id="evFLivestream" style="width:auto; margin:0;">
      <label for="evFLivestream" style="margin:0; font-size:13px; font-weight:600; cursor:pointer;">Has Livestream</label>
    </div>

    <div class="field" style="display:flex; align-items:center; gap:10px;">
      <input type="checkbox" id="evFRecurring" style="width:auto; margin:0;">
      <label for="evFRecurring" style="margin:0; font-size:13px; font-weight:600; cursor:pointer;">Recurring weekly event</label>
    </div>

    <div class="field" id="evRecurDayWrap" style="display:none;">
      <label for="evFRecurDay">Day of the Week *</label>
      <select id="evFRecurDay">
        <option value="Sunday">Sunday</option>
        <option value="Monday">Monday</option>
        <option value="Tuesday">Tuesday</option>
        <option value="Wednesday">Wednesday</option>
        <option value="Thursday">Thursday</option>
        <option value="Friday">Friday</option>
        <option value="Saturday">Saturday</option>
      </select>
    </div>

    <div class="ann-form-actions">
      <button class="btn-cancel" id="eventFormCancel">Cancel</button>
      <button class="btn-save"   id="eventFormSave">Save</button>
    </div>
    <div class="ann-form-msg" id="eventFormMsg"></div>
  </div>
</div>

<!-- ── Edit Modal ── -->
<div class="edit-modal" id="editModal" hidden>
  <div class="edit-backdrop" id="editBackdrop"></div>
  <div class="edit-box">
    <h3 id="editModalTitle">Edit Media</h3>
    <input type="hidden" id="editId">

    <div class="field">
      <label for="editVideoUrl">YouTube / Video URL</label>
      <input type="url" id="editVideoUrl" placeholder="https://www.youtube.com/watch?v=...">
    </div>
    <div class="field">
      <label for="editThumbnail">Thumbnail URL (optional)</label>
      <input type="url" id="editThumbnail" placeholder="https://img.youtube.com/vi/VIDEO_ID/hqdefault.jpg">
    </div>

    <div class="edit-actions">
      <button class="btn-cancel" id="editCancel">Cancel</button>
      <button class="btn-save"   id="editSave">Save changes</button>
    </div>
    <div class="save-msg" id="saveMsg"></div>
  </div>
</div>

<!-- ── Quiz Form Modal ── -->
<div class="quiz-form-modal" id="quizFormModal" hidden>
  <div class="quiz-form-backdrop" id="quizFormBackdrop"></div>
  <div class="quiz-form-box">
    <h3>New quiz</h3>
    <p class="quiz-form-sub">Add a title, pick a category, then build out questions below.</p>

    <div class="quiz-form-row">
      <div class="qf-field">
        <label for="qfTitle">Quiz title</label>
        <input type="text" id="qfTitle" placeholder="e.g. Life of Jesus" maxlength="120">
      </div>
      <div class="qf-field">
        <label for="qfCategory">Category</label>
        <select id="qfCategory">
          <option value="New Testament">New Testament</option>
          <option value="Old Testament">Old Testament</option>
          <option value="Gospels">Gospels</option>
          <option value="Epistles">Epistles</option>
          <option value="Prophecy">Prophecy</option>
          <option value="General">General</option>
          <option value="Daily Challenge">Daily Challenge</option>
        </select>
      </div>
    </div>

    <div class="qf-field">
      <label for="qfDesc">Description</label>
      <input type="text" id="qfDesc" placeholder="e.g. Key moments from the Gospels" maxlength="200">
    </div>

    <div class="qf-checkbox">
      <input type="checkbox" id="qfDailyChallenge">
      <label for="qfDailyChallenge">Set as today's daily challenge</label>
    </div>

    <div class="quiz-form-divider"></div>

    <div class="quiz-form-section-title">Questions</div>
    <div id="qfQuestionList"></div>

    <button class="quiz-add-q-btn" id="qfAddQuestionBtn">
      <span style="font-size:16px;">☐</span> Add question
    </button>

    <div class="quiz-form-msg" id="quizFormMsg"></div>

    <div class="quiz-form-footer">
      <button class="quiz-btn-draft"   id="qfDraftBtn">Save as draft</button>
      <button class="quiz-btn-publish" id="qfPublishBtn">Publish quiz</button>
    </div>
  </div>
</div>

<!-- ── Confirm Dialog Modal ── -->
<div class="confirm-modal" id="confirmModal" hidden>
  <div class="confirm-backdrop" id="confirmBackdrop"></div>
  <div class="confirm-box">
    <div class="confirm-icon" id="confirmIcon">⚠️</div>
    <h4 id="confirmTitle">Are you sure?</h4>
    <p  id="confirmMessage">This action cannot be undone.</p>
    <div class="confirm-actions">
      <button class="btn-cancel"     id="confirmCancelBtn">Cancel</button>
      <button class="btn-confirm-ok" id="confirmOkBtn">Confirm</button>
    </div>
  </div>
</div>

<!-- ── User Detail Modal ── -->
<div class="user-detail-modal" id="userDetailModal" hidden>
  <div class="user-detail-backdrop" id="userDetailBackdrop"></div>
  <div class="user-detail-box">
    <h3>User Details</h3>
    
    <div class="user-detail-section" style="display:flex; align-items:center; gap:16px; border-bottom:1px solid var(--line); padding-bottom:20px;">
      <img id="userDetailAvatar" class="user-avatar" style="width:60px; height:60px;" src="" alt="Avatar">
      <div>
        <div style="font-family:'Fraunces',serif; font-size:18px; font-weight:600; color:var(--plum-deep);" id="userDetailDisplayName"></div>
        <div style="font-size:13px; color:var(--ink-soft); margin-top:2px;" id="userDetailUsername"></div>
      </div>
    </div>

    <div class="user-detail-section">
      <div class="user-detail-label">Email</div>
      <div class="user-detail-value" id="userDetailEmail"></div>
    </div>

    <div class="user-detail-section">
      <div class="user-detail-label">Status</div>
      <div id="userDetailStatus"></div>
    </div>

    <div class="user-detail-section">
      <div class="user-detail-label">Member Since</div>
      <div class="user-detail-value" id="userDetailCreated"></div>
    </div>

    <div class="user-detail-section">
      <div class="user-detail-label">Last Login</div>
      <div class="user-detail-value" id="userDetailLastLogin"></div>
    </div>

    <input type="hidden" id="userDetailId">
    <input type="hidden" id="userDetailCurrentStatus">

    <div class="user-detail-actions">
      <button class="btn-cancel" id="userDetailClose">Close</button>
      <button class="btn-status-change" id="userResetPassword" style="background: var(--ember); color: var(--white);">Reset Password</button>
      <button class="btn-status-change btn-status-active" id="userSetActive">Set Active</button>
      <button class="btn-status-change btn-status-inactive" id="userSetInactive">Set Inactive</button>
      <button class="btn-status-change btn-status-banned" id="userSetBanned">Ban User</button>
    </div>
  </div>
</div>

<!-- ── Reset Password Modal ── -->
<div class="user-detail-modal" id="resetPasswordModal" hidden>
  <div class="user-detail-backdrop" id="resetPasswordBackdrop"></div>
  <div class="user-detail-box" style="max-width: 500px;">
    <h3>Reset User Password</h3>
    
    <p style="font-size: 14px; color: var(--ink-soft); margin-bottom: 20px;">
      Enter a new password for <strong id="resetPasswordUsername"></strong>. The user will be able to login with this new password immediately.
    </p>

    <div class="field">
      <label for="resetNewPassword">New Password</label>
      <input type="password" id="resetNewPassword" placeholder="Enter new password (min 8 characters)" minlength="8">
    </div>

    <div class="field">
      <label for="resetConfirmPassword">Confirm Password</label>
      <input type="password" id="resetConfirmPassword" placeholder="Re-enter new password">
    </div>

    <div id="resetPasswordMsg" style="font-size: 13px; margin-top: 10px; min-height: 20px;"></div>

    <input type="hidden" id="resetPasswordUserId">

    <div class="user-detail-actions" style="margin-top: 20px;">
      <button class="btn-cancel" id="resetPasswordCancel">Cancel</button>
      <button class="btn-status-change" id="resetPasswordConfirm" style="background: var(--ember); color: var(--white);">Reset Password</button>
    </div>
  </div>
</div>

<script>
const API = '/DigitalEvangelization/api';

// ── Confirm Modal helper ──────────────────────────────────────────────────────
let _confirmResolve = null;

function showConfirm({ title = 'Are you sure?', message = 'This action cannot be undone.', okLabel = 'Confirm', danger = true } = {}) {
  return new Promise(resolve => {
    _confirmResolve = resolve;
    document.getElementById('confirmTitle').textContent   = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmOkBtn').textContent   = okLabel;
    document.getElementById('confirmOkBtn').style.background = danger ? 'var(--reject)' : 'var(--plum)';
    document.getElementById('confirmOkBtn').onmouseover = function() { this.style.filter = 'brightness(1.12)'; };
    document.getElementById('confirmOkBtn').onmouseout  = function() { this.style.filter = ''; };
    document.getElementById('confirmModal').hidden = false;
  });
}

function _closeConfirm(result) {
  document.getElementById('confirmModal').hidden = true;
  if (_confirmResolve) { _confirmResolve(result); _confirmResolve = null; }
}

document.getElementById('confirmOkBtn').addEventListener('click',    () => _closeConfirm(true));
document.getElementById('confirmCancelBtn').addEventListener('click', () => _closeConfirm(false));
document.getElementById('confirmBackdrop').addEventListener('click',  () => _closeConfirm(false));

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function loadTabContent(tabName) {
  // Trigger lazy-loading for each tab type
  switch(tabName) {
    case 'articles':
      if (!document.getElementById('articlePendingList')._loaded) {
        document.getElementById('articlePendingList')._loaded = true;
        loadArticlesPending();
      }
      break;
    case 'media-pending':
      if (!document.getElementById('videoPendingList')._loaded) {
        document.getElementById('videoPendingList')._loaded = true;
        loadVideosPending();
      }
      break;
    case 'media':
      if (!document.getElementById('mediaList')._loaded) {
        document.getElementById('mediaList')._loaded = true;
        loadAllMedia();
      }
      break;
    case 'gallery':
      if (!document.getElementById('galleryPendingList')._loaded) {
        document.getElementById('galleryPendingList')._loaded = true;
        loadGalleryPending();
      }
      break;
    case 'announcements':
      if (!document.getElementById('annAdminList')._loaded) {
        document.getElementById('annAdminList')._loaded = true;
        loadAnnouncements();
      }
      break;
    case 'events':
      if (!document.getElementById('eventsAdminList')._loaded) {
        document.getElementById('eventsAdminList')._loaded = true;
        loadAdminEvents();
      }
      break;
    case 'quizzes':
      if (!document.getElementById('quizAdminList')._loaded) {
        document.getElementById('quizAdminList')._loaded = true;
        loadQuizAdmin();
      }
      break;
    case 'messages':
      if (!document.getElementById('messagesList')._loaded) {
        document.getElementById('messagesList')._loaded = true;
        loadMessages();
      }
      break;
    case 'users':
      if (!document.getElementById('usersList')._loaded) {
        document.getElementById('usersList')._loaded = true;
        loadUsers();
      }
      break;
  }
}

function switchTab(tabName) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  
  const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
  const targetPanel = document.getElementById('tab-' + tabName);
  
  if (targetBtn && targetPanel) {
    targetBtn.classList.add('active');
    targetPanel.classList.add('active');
    // Save the active tab to localStorage
    localStorage.setItem('adminActiveTab', tabName);
    // Load content for this tab
    loadTabContent(tabName);
  }
}

// Restore last active tab on page load
const savedTab = localStorage.getItem('adminActiveTab');
if (savedTab) {
  switchTab(savedTab);
}

// Set up click listeners for all tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    switchTab(btn.dataset.tab);
  });
});

// ── Prayer Panel ──────────────────────────────────────────────────────────────
async function loadPending() {
  const container = document.getElementById('pendingList');
  const res  = await fetch(API + '/prayers/pending');
  const json = await res.json();

  if (json.status !== 'success' || !json.data.length) {
    container.innerHTML = '<div class="empty-state">No pending requests — you\'re all caught up!</div>';
    return;
  }

  container.innerHTML = json.data.map(p => `
    <div class="prayer-card" data-id="${p.id}">
      <div class="p-meta">
        <div>
          <span class="p-name">${escHtml(p.name)}</span>
          <span class="cat-badge">${escHtml(p.category)}</span>
        </div>
        <div>${new Date(p.created_at).toLocaleString()}</div>
      </div>
      <div class="p-body">${escHtml(p.body)}</div>
      <div class="prayer-actions">
        <button class="btn-approve" onclick="handlePrayer(${p.id}, 'approve')">✓ Approve</button>
        <button class="btn-reject"  onclick="handlePrayer(${p.id}, 'reject')">✗ Reject</button>
      </div>
    </div>
  `).join('');
}

async function handlePrayer(id, action) {
  if (action === 'reject') {
    const ok = await showConfirm({ title: 'Reject prayer request?', message: 'This request will be removed from the pending list.', okLabel: 'Reject' });
    if (!ok) return;
  }
  const card = document.querySelector(`.prayer-card[data-id="${id}"]`);
  const res  = await fetch(API + `/prayers/${id}/${action}`, { method: 'POST' });
  const json = await res.json();
  if (json.status === 'success') {
    card.style.transition = 'opacity 0.3s';
    card.style.opacity    = '0';
    setTimeout(() => {
      card.remove();
      if (!document.querySelector('.prayer-card')) {
        document.getElementById('pendingList').innerHTML =
          '<div class="empty-state">No pending requests — you\'re all caught up!</div>';
      }
    }, 300);
  } else {
    alert('Error: ' + json.message);
  }
}

loadPending();

// ── Articles Pending Panel ────────────────────────────────────────────────────
async function loadArticlesPending() {
  const container = document.getElementById('articlePendingList');
  try {
    const res  = await fetch(API + '/articles/pending');
    const json = await res.json();

    if (json.status !== 'success' || !json.data.length) {
      container.innerHTML = '<div class="empty-state">No pending articles — all clear!</div>';
      return;
    }

    container.innerHTML = json.data.map(a => `
      <div class="prayer-card" data-id="${a.id}">
        <div class="p-meta">
          <div>
            <span class="p-name">${escHtml(a.posted_by)}</span>
            <span class="cat-badge">${escHtml(a.read_minutes)} min read</span>
          </div>
          <div>${new Date(a.published_at).toLocaleString()}</div>
        </div>
        <div style="font-weight:600; font-size:15px; margin-bottom:6px; color:var(--night);">${escHtml(a.title)}</div>
        <div class="p-body" style="font-style:italic; color:var(--ink-soft);">${escHtml(a.excerpt)}</div>
        <details style="margin-bottom:14px;">
          <summary style="cursor:pointer; font-size:13px; color:var(--horizon); margin-bottom:6px;">Read full body</summary>
          <div style="font-size:13px; line-height:1.7; white-space:pre-wrap; border-top:1px solid var(--line); padding-top:10px; margin-top:6px;">${escHtml(a.body)}</div>
        </details>
        <div class="prayer-actions">
          <button class="btn-approve" onclick="handleArticle(${a.id}, 'approve')">✓ Approve &amp; Publish</button>
          <button class="btn-reject"  onclick="handleArticle(${a.id}, 'reject')">✗ Reject</button>
        </div>
      </div>
    `).join('');
  } catch (e) {
    container.innerHTML = '<div class="empty-state">Could not load pending articles.</div>';
  }
}

async function handleArticle(id, action) {
  if (action === 'reject') {
    const ok = await showConfirm({ title: 'Reject this article?', message: 'The article will be removed from the pending list.', okLabel: 'Reject' });
    if (!ok) return;
  }
  const card = document.querySelector(`#articlePendingList .prayer-card[data-id="${id}"]`);
  try {
    const res  = await fetch(API + `/articles/${id}/${action}`, { method: 'POST' });
    const json = await res.json();
    if (json.status === 'success') {
      card.style.transition = 'opacity 0.3s';
      card.style.opacity    = '0';
      setTimeout(() => {
        card.remove();
        if (!document.querySelector('#articlePendingList .prayer-card')) {
          document.getElementById('articlePendingList').innerHTML =
            '<div class="empty-state">No pending articles — all clear!</div>';
        }
      }, 300);
    } else {
      alert('Error: ' + json.message);
    }
  } catch (e) {
    alert('Network error.');
  }
}

// Lazy-load articles tab when first clicked (handled by switchTab function now)


// ── Videos Pending Panel ──────────────────────────────────────────────────────
async function loadVideosPending() {
  const container = document.getElementById('videoPendingList');
  try {
    const res  = await fetch(API + '/media/pending');
    const json = await res.json();

    if (json.status !== 'success' || !json.data.length) {
      container.innerHTML = '<div class="empty-state">No pending videos — all clear!</div>';
      return;
    }

    container.innerHTML = json.data.map(v => {
      const hasUrl = v.video_url && v.video_url.trim();
      return `
        <div class="prayer-card" data-id="${v.id}">
          <div class="p-meta">
            <div>
              <span class="p-name">${escHtml(v.posted_by)}</span>
              <span class="cat-badge type-${escHtml(v.type)}">${escHtml(v.type)}</span>
              ${v.series ? `<span class="cat-badge" style="margin-left:4px;">${escHtml(v.series)}</span>` : ''}
            </div>
            <div>${new Date(v.published_at).toLocaleString()}</div>
          </div>
          <div style="font-weight:600; font-size:15px; margin-bottom:6px; color:var(--night);">${escHtml(v.title)}</div>
          <div class="p-body">${escHtml(v.description)}</div>
          ${hasUrl ? `
            <div style="margin-bottom:14px;">
              <a href="${escHtml(v.video_url)}" target="_blank" rel="noopener"
                 style="font-size:13px; color:var(--horizon); word-break:break-all;">${escHtml(v.video_url)}</a>
            </div>` : `
            <div style="margin-bottom:14px; font-size:13px; color:#c62828; font-style:italic;">No video URL provided</div>`}
          <div class="prayer-actions">
            <button class="btn-approve" onclick="handleVideo(${v.id}, 'approve')">✓ Approve &amp; Publish</button>
            <button class="btn-reject"  onclick="handleVideo(${v.id}, 'reject')">✗ Reject</button>
          </div>
        </div>
      `;
    }).join('');
  } catch (e) {
    container.innerHTML = '<div class="empty-state">Could not load pending videos.</div>';
  }
}

async function handleVideo(id, action) {
  if (action === 'reject') {
    const ok = await showConfirm({ title: 'Reject this video?', message: 'The video will be removed from the pending list.', okLabel: 'Reject' });
    if (!ok) return;
  }
  const card = document.querySelector(`#videoPendingList .prayer-card[data-id="${id}"]`);
  try {
    const res  = await fetch(API + `/media/${id}/${action}`, { method: 'POST' });
    const json = await res.json();
    if (json.status === 'success') {
      card.style.transition = 'opacity 0.3s';
      card.style.opacity    = '0';
      setTimeout(() => {
        card.remove();
        if (!document.querySelector('#videoPendingList .prayer-card')) {
          document.getElementById('videoPendingList').innerHTML =
            '<div class="empty-state">No pending videos — all clear!</div>';
        }
        // Reload approved media list if it's already been loaded
        if (document.getElementById('mediaList')._loaded) {
          loadMedia();
        }
      }, 300);
    } else {
      alert('Error: ' + json.message);
    }
  } catch (e) {
    alert('Network error.');
  }
}

// Lazy-load videos pending tab when first clicked (handled by switchTab function now)


// ── Gallery Pending Panel ─────────────────────────────────────────────────────
async function loadGalleryPending() {
  const container = document.getElementById('galleryPendingList');
  try {
    const res  = await fetch(API + '/gallery/pending');
    const json = await res.json();

    if (json.status !== 'success' || !json.data.length) {
      container.innerHTML = '<div class="empty-state">No pending photos — all clear!</div>';
      return;
    }

    container.innerHTML = json.data.map(g => {
      return `
        <div class="prayer-card" data-id="${g.id}" style="display:flex;gap:18px;align-items:flex-start;">
          <div style="flex-shrink:0;width:180px;height:180px;border-radius:8px;overflow:hidden;background:var(--line);">
            <img src="${escHtml(g.image_url)}" alt="${escHtml(g.title)}" 
                 style="width:100%;height:100%;object-fit:cover;">
          </div>
          <div style="flex:1;min-width:0;">
            <div class="p-meta">
              <div>
                <span class="p-name">${escHtml(g.display_name || g.username)}</span>
              </div>
              <div>${new Date(g.created_at).toLocaleString()}</div>
            </div>
            <div style="font-weight:600; font-size:15px; margin-bottom:6px; color:var(--night);">${escHtml(g.title)}</div>
            ${g.description ? `<div class="p-body">${escHtml(g.description)}</div>` : ''}
            <div class="prayer-actions" style="margin-top:14px;">
              <button class="btn-approve" onclick="handleGallery(${g.id}, 'approve')">✓ Approve &amp; Publish</button>
              <button class="btn-reject"  onclick="handleGallery(${g.id}, 'reject')">✗ Reject</button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  } catch (e) {
    container.innerHTML = '<div class="empty-state">Could not load pending photos.</div>';
  }
}

async function handleGallery(id, action) {
  if (action === 'reject') {
    const ok = await showConfirm({ 
      title: 'Reject this photo?', 
      message: 'The photo will be removed from the pending list and won\'t appear in the gallery.', 
      okLabel: 'Reject' 
    });
    if (!ok) return;
  }
  const card = document.querySelector(`#galleryPendingList .prayer-card[data-id="${id}"]`);
  try {
    const res  = await fetch(API + `/gallery/${id}/${action}`, { method: 'POST' });
    const json = await res.json();
    if (json.status === 'success') {
      card.style.transition = 'opacity 0.3s';
      card.style.opacity    = '0';
      setTimeout(() => {
        card.remove();
        if (!document.querySelector('#galleryPendingList .prayer-card')) {
          document.getElementById('galleryPendingList').innerHTML =
            '<div class="empty-state">No pending photos — all clear!</div>';
        }
      }, 300);
    } else {
      alert('Error: ' + json.message);
    }
  } catch (e) {
    alert('Network error.');
  }
}

// Lazy-load gallery pending tab when first clicked (handled by switchTab function now)


let allMedia = [];

async function loadMedia() {
  const container = document.getElementById('mediaList');
  const res  = await fetch(API + '/media');
  const json = await res.json();

  if (json.status !== 'success' || !json.data.length) {
    container.innerHTML = '<div class="empty-state">No media found.</div>';
    return;
  }

  allMedia = json.data;
  container._loaded = true;

  const rows = json.data.map(m => {
    const hasUrl = m.video_url && m.video_url.trim();
    return `
      <tr data-id="${m.id}">
        <td>${m.id}</td>
        <td><strong>${escHtml(m.title)}</strong><br><small style="color:var(--ink-soft)">${escHtml(m.series || '')}</small></td>
        <td><span class="type-badge type-${escHtml(m.type)}">${escHtml(m.type)}</span></td>
        <td>${escHtml(m.duration_label)}</td>
        <td class="url-cell ${hasUrl ? '' : 'empty'}" title="${escHtml(m.video_url || '')}">
          ${hasUrl ? escHtml(m.video_url) : '— not set —'}
        </td>
        <td><button class="btn-edit" onclick="openEditModal(${m.id})">Edit</button></td>
      </tr>
    `;
  }).join('');

  container.innerHTML = `
    <table class="media-table">
      <thead>
        <tr>
          <th>#</th><th>Title</th><th>Type</th><th>Duration</th><th>Video URL</th><th></th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  `;
}

loadMedia();

// ── Edit Modal ────────────────────────────────────────────────────────────────
function openEditModal(id) {
  const m = allMedia.find(x => x.id === id);
  if (!m) return;

  document.getElementById('editId').value        = id;
  document.getElementById('editModalTitle').textContent = `Edit: ${m.title}`;
  document.getElementById('editVideoUrl').value  = m.video_url   || '';
  document.getElementById('editThumbnail').value = m.thumbnail   || '';
  document.getElementById('saveMsg').textContent = '';
  document.getElementById('saveMsg').className   = 'save-msg';
  document.getElementById('editModal').hidden    = false;
}

function closeEditModal() {
  document.getElementById('editModal').hidden = true;
}

document.getElementById('editCancel').addEventListener('click', closeEditModal);
document.getElementById('editBackdrop').addEventListener('click', closeEditModal);

document.getElementById('editSave').addEventListener('click', async () => {
  const id        = parseInt(document.getElementById('editId').value, 10);
  const video_url = document.getElementById('editVideoUrl').value.trim();
  const thumbnail = document.getElementById('editThumbnail').value.trim();
  const saveMsg   = document.getElementById('saveMsg');
  const saveBtn   = document.getElementById('editSave');

  saveBtn.disabled  = true;
  saveBtn.textContent = 'Saving…';
  saveMsg.textContent = '';

  try {
    const res  = await fetch(API + `/media/${id}`, {
      method:  'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ video_url, thumbnail }),
    });
    const json = await res.json();

    if (json.status === 'success') {
      // Update local cache
      const idx = allMedia.findIndex(x => x.id === id);
      if (idx !== -1) allMedia[idx] = json.data;

      // Update table row
      const row     = document.querySelector(`.media-table tr[data-id="${id}"]`);
      const urlCell = row.querySelector('.url-cell');
      const hasUrl  = video_url.length > 0;
      urlCell.textContent  = hasUrl ? video_url : '— not set —';
      urlCell.className    = `url-cell ${hasUrl ? '' : 'empty'}`;
      urlCell.title        = video_url;

      saveMsg.textContent = '✓ Saved!';
      saveMsg.className   = 'save-msg ok';
      setTimeout(closeEditModal, 900);
    } else {
      saveMsg.textContent = '✗ ' + json.message;
      saveMsg.className   = 'save-msg err';
    }
  } catch (e) {
    saveMsg.textContent = '✗ Network error.';
    saveMsg.className   = 'save-msg err';
  } finally {
    saveBtn.disabled    = false;
    saveBtn.textContent = 'Save changes';
  }
});
// ── Announcements Panel ──────────────────────────────────────────────────────

let allAnnouncements = [];

async function loadAnnouncements() {
  const container = document.getElementById('annAdminList');
  const res  = await fetch(API + '/announcements');
  const json = await res.json();

  if (json.status !== 'success') {
    container.innerHTML = '<div class="empty-state">Could not load announcements.</div>';
    return;
  }

  allAnnouncements = json.data || [];

  if (!allAnnouncements.length) {
    container.innerHTML = '<div class="empty-state">No announcements yet. Create the first one!</div>';
    return;
  }

  container.innerHTML = allAnnouncements.map(a => {
    const d     = new Date(a.published_at);
    const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    return `
      <div class="ann-admin-card" data-id="${a.id}">
        <span class="ann-badge ann-badge-${escHtml(a.category)}">${escHtml(a.category)}</span>
        <div class="ann-admin-body">
          <div class="ann-admin-title">
            ${escHtml(a.title)}
            ${a.is_pinned ? '<span class="ann-pinned-chip">★ Pinned</span>' : ''}
          </div>
          <div class="ann-admin-excerpt">${escHtml(a.body.length > 140 ? a.body.slice(0, 140) + '…' : a.body)}</div>
          <div class="ann-admin-meta">${dateStr}</div>
        </div>
        <div class="ann-admin-actions">
          <button class="btn-ann-edit"   onclick="openAnnForm(${a.id})">Edit</button>
          <button class="btn-ann-delete" onclick="deleteAnn(${a.id})">Delete</button>
        </div>
      </div>
    `;
  }).join('');
}

// ── Ann Form Modal ────────────────────────────────────────────────────────────

function openAnnForm(id = null) {
  const isEdit = id !== null;
  document.getElementById('annFormTitle').textContent = isEdit ? 'Edit Announcement' : 'New Announcement';
  document.getElementById('annFormId').value          = id ?? '';
  document.getElementById('annFormMsg').textContent   = '';
  document.getElementById('annFormMsg').className     = 'ann-form-msg';

  if (isEdit) {
    const a = allAnnouncements.find(x => x.id === id);
    if (a) {
      document.getElementById('annFTitle').value        = a.title;
      document.getElementById('annFBody').value         = a.body;
      document.getElementById('annFCategory').value     = a.category;
      document.getElementById('annFPinned').checked     = !!a.is_pinned;
    }
  } else {
    document.getElementById('annFTitle').value    = '';
    document.getElementById('annFBody').value     = '';
    document.getElementById('annFCategory').value = 'Ministry';
    document.getElementById('annFPinned').checked = false;
  }

  document.getElementById('annFormModal').hidden = false;
  document.getElementById('annFTitle').focus();
}

function closeAnnForm() {
  document.getElementById('annFormModal').hidden = true;
}

document.getElementById('openNewAnnBtn').addEventListener('click', () => openAnnForm());
document.getElementById('annFormCancel').addEventListener('click', closeAnnForm);
document.getElementById('annFormBackdrop').addEventListener('click', closeAnnForm);

document.getElementById('annFormSave').addEventListener('click', async () => {
  const id       = document.getElementById('annFormId').value;
  const title    = document.getElementById('annFTitle').value.trim();
  const body     = document.getElementById('annFBody').value.trim();
  const category = document.getElementById('annFCategory').value;
  const is_pinned = document.getElementById('annFPinned').checked;
  const msgEl    = document.getElementById('annFormMsg');
  const saveBtn  = document.getElementById('annFormSave');

  if (!title) { msgEl.textContent = 'Title is required.'; msgEl.className = 'ann-form-msg err'; return; }
  if (!body)  { msgEl.textContent = 'Body is required.'; msgEl.className = 'ann-form-msg err'; return; }

  saveBtn.disabled = true; saveBtn.textContent = 'Saving…'; msgEl.textContent = '';

  const isEdit = id !== '';
  const url    = API + '/announcements' + (isEdit ? '/' + id : '');
  const method = isEdit ? 'PATCH' : 'POST';

  try {
    const res  = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, body, category, is_pinned }),
    });
    const json = await res.json();

    if (json.status === 'success') {
      msgEl.textContent = isEdit ? '✓ Updated!' : '✓ Created!';
      msgEl.className   = 'ann-form-msg ok';
      setTimeout(() => { closeAnnForm(); loadAnnouncements(); }, 700);
    } else {
      msgEl.textContent = '✗ ' + json.message;
      msgEl.className   = 'ann-form-msg err';
    }
  } catch (e) {
    msgEl.textContent = '✗ Network error.';
    msgEl.className   = 'ann-form-msg err';
  } finally {
    saveBtn.disabled    = false;
    saveBtn.textContent = 'Save';
  }
});

async function deleteAnn(id) {
  const ok = await showConfirm({ title: 'Delete announcement?', message: 'This cannot be undone.', okLabel: 'Delete' });
  if (!ok) return;
  const res  = await fetch(API + '/announcements/' + id, { method: 'DELETE' });
  const json = await res.json();
  if (json.status === 'success') {
    loadAnnouncements();
  } else {
    alert('Error: ' + json.message);
  }
}

// Lazy-load announcements tab when first clicked (handled by switchTab function now)


// ── Events Panel ─────────────────────────────────────────────────────────────

async function loadAdminEvents() {
  const container = document.getElementById('eventsAdminList');
  try {
    const res  = await fetch(API + '/events/all');
    const json = await res.json();

    container._loaded = true;

    if (json.status !== 'success' || !json.data.length) {
      container.innerHTML = '<div class="empty-state">No events found.</div>';
      return;
    }

    container.innerHTML = json.data.map(e => {
      const isRecurring = e.is_recurring == 1;
      const dayLabel    = isRecurring
        ? e.recur_day + 's · ' + formatAdminTime(e.start_time)
        : new Date(e.event_date + 'T00:00:00').toLocaleDateString('en-US', {weekday:'short', month:'short', day:'numeric'}) + ' · ' + formatAdminTime(e.start_time);
      const count = parseInt(e.registrations_count ?? 0, 10);
      return `
        <div class="event-admin-card" data-id="${e.id}">
          <div class="event-admin-meta">
            <div class="event-admin-title">${escHtml(e.title)}</div>
            <div class="event-admin-detail">
              ${escHtml(dayLabel)}
              ${e.location ? ' · ' + escHtml(e.location) : ''}
              ${e.has_livestream == 1 ? ' · <em>Livestream</em>' : ''}
              ${isRecurring ? ' · <em>Recurring</em>' : ''}
            </div>
          </div>
          <span class="event-admin-count">${count} registrant${count !== 1 ? 's' : ''}</span>
          <button class="btn-view-reg" onclick="loadRegistrants(${e.id}, this.dataset.title)" data-title="${escHtml(e.title)}">
            View Registrants
          </button>
          <button class="btn-ann-delete" onclick="deleteEvent(${e.id})" style="white-space:nowrap;">
            Delete
          </button>
        </div>
      `;
    }).join('');
  } catch (err) {
    container.innerHTML = '<div class="empty-state">Could not load events.</div>';
  }
}

async function deleteEvent(id) {
  const ok = await showConfirm({
    title:   'Delete this event?',
    message: 'All registrations for this event will also be removed. This cannot be undone.',
    okLabel: 'Delete',
  });
  if (!ok) return;

  try {
    const res  = await fetch(API + `/events/${id}`, { method: 'DELETE' });
    const json = await res.json();
    if (json.status === 'success') {
      const card = document.querySelector(`.event-admin-card[data-id="${id}"]`);
      if (card) {
        card.style.transition = 'opacity 0.3s';
        card.style.opacity    = '0';
        setTimeout(() => {
          card.remove();
          // Hide the registrant box if it was open for this event
          document.getElementById('eventsRegistrantBox').style.display = 'none';
          if (!document.querySelector('.event-admin-card')) {
            document.getElementById('eventsAdminList').innerHTML =
              '<div class="empty-state">No events found.</div>';
          }
        }, 300);
      }
    } else {
      alert('Error: ' + json.message);
    }
  } catch (e) {
    alert('Network error.');
  }
}

async function loadRegistrants(eventId, eventTitle) {
  const box       = document.getElementById('eventsRegistrantBox');
  const titleEl   = document.getElementById('eventsRegistrantTitle');
  const contentEl = document.getElementById('eventsRegistrantContent');

  titleEl.textContent   = 'Registrants: ' + eventTitle;
  contentEl.innerHTML   = '<div class="loading-state">Loading…</div>';
  box.style.display     = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'start' });

  try {
    const res  = await fetch(API + `/events/${eventId}/registrations`);
    const json = await res.json();

    if (json.status !== 'success') {
      contentEl.innerHTML = '<div class="empty-state">Could not load registrants.</div>';
      return;
    }

    const regs = json.data.registrations || [];
    if (!regs.length) {
      contentEl.innerHTML = '<div class="empty-state">No one has registered for this event yet.</div>';
      return;
    }

    const rows = regs.map((r, i) => `
      <tr>
        <td>${i + 1}</td>
        <td><strong>${escHtml(r.display_name)}</strong><br><small style="color:var(--ink-soft);">@${escHtml(r.username)}</small></td>
        <td>${escHtml(r.email)}</td>
        <td><span class="join-badge-${escHtml(r.join_type)}">${r.join_type === 'online' ? '📺 Online' : '📍 In Person'}</span></td>
        <td>${new Date(r.registered_at).toLocaleString()}</td>
      </tr>
    `).join('');

    contentEl.innerHTML = `
      <table class="reg-table">
        <thead>
          <tr><th>#</th><th>Member</th><th>Email</th><th>Join Type</th><th>Registered At</th></tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
      <p style="margin-top:12px; font-size:13px; color:var(--ink-soft);">${regs.length} total registrant${regs.length !== 1 ? 's' : ''}</p>
    `;
  } catch (err) {
    contentEl.innerHTML = '<div class="empty-state">Network error.</div>';
  }
}

document.getElementById('eventsRegistrantClose').addEventListener('click', () => {
  document.getElementById('eventsRegistrantBox').style.display = 'none';
});

function formatAdminTime(t) {
  if (!t) return '';
  const [h, m] = t.split(':').map(Number);
  const ampm = h >= 12 ? 'PM' : 'AM';
  return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${ampm}`;
}

// Lazy-load events tab when first clicked (handled by switchTab function now)


// ── Event Form Modal ─────────────────────────────────────────────────────────

function openEventForm() {
  document.getElementById('eventFormTitle').textContent = 'New Event';
  document.getElementById('evFTitle').value        = '';
  document.getElementById('evFDescription').value  = '';
  document.getElementById('evFDate').value         = '';
  document.getElementById('evFTime').value         = '';
  document.getElementById('evFLocation').value     = '';
  document.getElementById('evFLivestream').checked = false;
  document.getElementById('evFRecurring').checked  = false;
  document.getElementById('evRecurDayWrap').style.display = 'none';
  document.getElementById('evDateWrap').style.display     = '';
  document.getElementById('eventFormMsg').textContent     = '';
  document.getElementById('eventFormMsg').className       = 'ann-form-msg';
  document.getElementById('eventFormModal').hidden        = false;
  document.getElementById('evFTitle').focus();
}

function closeEventForm() {
  document.getElementById('eventFormModal').hidden = true;
}

document.getElementById('openNewEventBtn').addEventListener('click', openEventForm);
document.getElementById('eventFormCancel').addEventListener('click', closeEventForm);
document.getElementById('eventFormBackdrop').addEventListener('click', closeEventForm);

// Toggle recurring day picker and hide/show the date field
document.getElementById('evFRecurring').addEventListener('change', function () {
  const isRecurring = this.checked;
  document.getElementById('evRecurDayWrap').style.display = isRecurring ? '' : 'none';
  document.getElementById('evDateWrap').style.display     = isRecurring ? 'none' : '';
});

document.getElementById('eventFormSave').addEventListener('click', async () => {
  const title        = document.getElementById('evFTitle').value.trim();
  const description  = document.getElementById('evFDescription').value.trim();
  const event_date   = document.getElementById('evFDate').value;
  const start_time   = document.getElementById('evFTime').value;
  const location     = document.getElementById('evFLocation').value.trim();
  const has_livestream = document.getElementById('evFLivestream').checked;
  const is_recurring   = document.getElementById('evFRecurring').checked;
  const recur_day      = document.getElementById('evFRecurDay').value;

  const msgEl   = document.getElementById('eventFormMsg');
  const saveBtn = document.getElementById('eventFormSave');

  msgEl.textContent = '';

  if (!title) {
    msgEl.textContent = 'Title is required.';
    msgEl.className   = 'ann-form-msg err';
    return;
  }
  if (!start_time) {
    msgEl.textContent = 'Start time is required.';
    msgEl.className   = 'ann-form-msg err';
    return;
  }
  if (!is_recurring && !event_date) {
    msgEl.textContent = 'Event date is required for non-recurring events.';
    msgEl.className   = 'ann-form-msg err';
    return;
  }

  saveBtn.disabled    = true;
  saveBtn.textContent = 'Saving…';

  try {
    const res  = await fetch(API + '/events', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, description, event_date, start_time, location, has_livestream, is_recurring, recur_day }),
    });
    const json = await res.json();

    if (json.status === 'success') {
      msgEl.textContent = '✓ Event created!';
      msgEl.className   = 'ann-form-msg ok';
      setTimeout(() => {
        closeEventForm();
        // Reload the events list
        document.getElementById('eventsAdminList')._loaded = false;
        loadAdminEvents();
      }, 700);
    } else {
      msgEl.textContent = '✗ ' + json.message;
      msgEl.className   = 'ann-form-msg err';
    }
  } catch (e) {
    msgEl.textContent = '✗ Network error.';
    msgEl.className   = 'ann-form-msg err';
  } finally {
    saveBtn.disabled    = false;
    saveBtn.textContent = 'Save';
  }
});
// ── Quizzes Panel ────────────────────────────────────────────────────────────

const QUIZ_STORAGE_KEY = 'adminQuizzes';

function getStoredQuizzes() {
  try { return JSON.parse(localStorage.getItem(QUIZ_STORAGE_KEY) || '[]'); } catch { return []; }
}
function saveStoredQuizzes(list) {
  localStorage.setItem(QUIZ_STORAGE_KEY, JSON.stringify(list));
}

function loadQuizAdmin() {
  const container = document.getElementById('quizAdminList');
  const quizzes   = getStoredQuizzes();

  if (!quizzes.length) {
    container.innerHTML = '<div class="empty-state">No custom quizzes yet. Create the first one!</div>';
    return;
  }

  container.innerHTML = quizzes.map((q, i) => `
    <div class="quiz-admin-card">
      <span class="quiz-cat-badge">${escHtml(q.category || 'General')}</span>
      <div class="quiz-admin-meta">
        <div class="quiz-admin-title">${escHtml(q.title)}</div>
        <div class="quiz-admin-desc">${escHtml(q.description || '')}</div>
        <div class="quiz-admin-count">${q.questions.length} question${q.questions.length !== 1 ? 's' : ''}
          ${q.isDraft ? ' · <em style="color:#d4a847;">Draft</em>' : ' · <em style="color:#2e7d32;">Published</em>'}
        </div>
      </div>
      <div class="quiz-admin-actions">
        <button class="btn-ann-edit"   onclick="editQuiz(${i})">Edit</button>
        <button class="btn-ann-delete" onclick="deleteQuiz(${i})">Delete</button>
      </div>
    </div>
  `).join('');
}

async function deleteQuiz(idx) {
  const ok = await showConfirm({ title: 'Delete this quiz?', message: 'This cannot be undone.', okLabel: 'Delete' });
  if (!ok) return;
  const list = getStoredQuizzes();
  list.splice(idx, 1);
  saveStoredQuizzes(list);
  loadQuizAdmin();
}

// ── Quiz Form ─────────────────────────────────────────────────────────────────
let _editQuizIdx = null;
let _qfQuestionCount = 0;

function openQuizForm(editIdx = null) {
  _editQuizIdx     = editIdx;
  _qfQuestionCount = 0;
  document.getElementById('qfTitle').value           = '';
  document.getElementById('qfDesc').value            = '';
  document.getElementById('qfCategory').value        = 'New Testament';
  document.getElementById('qfDailyChallenge').checked = false;
  document.getElementById('qfQuestionList').innerHTML = '';
  document.getElementById('quizFormMsg').textContent  = '';
  document.getElementById('quizFormMsg').className    = 'quiz-form-msg';

  if (editIdx !== null) {
    const q = getStoredQuizzes()[editIdx];
    if (q) {
      document.getElementById('qfTitle').value           = q.title;
      document.getElementById('qfDesc').value            = q.description || '';
      document.getElementById('qfCategory').value        = q.category || 'New Testament';
      document.getElementById('qfDailyChallenge').checked = !!q.isDailyChallenge;
      q.questions.forEach(ques => addQuestionBlock(ques));
    }
  }

  // Always start with at least one question block
  if (_qfQuestionCount === 0) addQuestionBlock();

  document.getElementById('quizFormModal').hidden = false;
  document.getElementById('qfTitle').focus();
}

function closeQuizForm() {
  document.getElementById('quizFormModal').hidden = true;
}

function addQuestionBlock(data = null) {
  _qfQuestionCount++;
  const n   = _qfQuestionCount;
  const uid = 'qq' + n;

  const choices = data ? data.choices : ['', '', '', ''];
  const answer  = data ? data.answer  : -1;

  const choiceHtml = choices.map((c, ci) => `
    <div class="quiz-choice-row">
      <input type="radio" name="${uid}_correct" value="${ci}" ${answer === ci ? 'checked' : ''} id="${uid}_r${ci}">
      <input type="text" class="qf-choice-text" data-choice="${ci}" placeholder="Choice ${ci + 1}" value="${escHtml(c)}">
    </div>
  `).join('');

  const block = document.createElement('div');
  block.className = 'quiz-question-block';
  block.dataset.quid = uid;
  block.innerHTML = `
    <div class="quiz-qblock-header">
      <span class="quiz-qblock-label">Question ${n}</span>
      <button class="quiz-qblock-remove" title="Remove question" onclick="removeQuestionBlock(this)">×</button>
    </div>
    <input type="text" class="quiz-qblock-qtxt" placeholder="Type your question here…" value="${data ? escHtml(data.q) : ''}">
    <div class="quiz-choices-grid">${choiceHtml}</div>
    <p class="quiz-choice-hint">Select the radio button next to the correct answer.</p>
  `;

  document.getElementById('qfQuestionList').appendChild(block);
}

function removeQuestionBlock(btn) {
  const block = btn.closest('.quiz-question-block');
  block.remove();
  // Re-label remaining blocks
  document.querySelectorAll('.quiz-question-block').forEach((b, i) => {
    b.querySelector('.quiz-qblock-label').textContent = `Question ${i + 1}`;
  });
}

function collectQuizData() {
  const title       = document.getElementById('qfTitle').value.trim();
  const description = document.getElementById('qfDesc').value.trim();
  const category    = document.getElementById('qfCategory').value;
  const isDailyChallenge = document.getElementById('qfDailyChallenge').checked;

  const questions = [];
  let error = null;

  document.querySelectorAll('.quiz-question-block').forEach((block, qi) => {
    const qText   = block.querySelector('.quiz-qblock-qtxt').value.trim();
    const choices = [...block.querySelectorAll('.qf-choice-text')].map(i => i.value.trim());
    const radios  = [...block.querySelectorAll('input[type="radio"]')];
    const checked = radios.find(r => r.checked);
    const answer  = checked ? parseInt(checked.value, 10) : -1;

    if (!qText)              error = error || `Question ${qi + 1} is missing its text.`;
    if (choices.some(c => !c)) error = error || `Question ${qi + 1} has empty choice(s).`;
    if (answer === -1)       error = error || `Question ${qi + 1} needs a correct answer selected.`;

    questions.push({ q: qText, choices, answer });
  });

  return { title, description, category, isDailyChallenge, questions, error };
}

function saveQuiz(isDraft) {
  const msgEl = document.getElementById('quizFormMsg');
  const { title, description, category, isDailyChallenge, questions, error } = collectQuizData();

  if (!title) {
    msgEl.textContent = 'Quiz title is required.';
    msgEl.className   = 'quiz-form-msg err';
    return;
  }
  if (!isDraft && error) {
    msgEl.textContent = error;
    msgEl.className   = 'quiz-form-msg err';
    return;
  }
  if (!isDraft && !questions.length) {
    msgEl.textContent = 'Add at least one question before publishing.';
    msgEl.className   = 'quiz-form-msg err';
    return;
  }

  const quizObj = {
    id:              'custom-' + Date.now(),
    title,
    description,
    category,
    isDailyChallenge,
    isDraft,
    questions,
  };

  const list = getStoredQuizzes();
  if (_editQuizIdx !== null) {
    quizObj.id = list[_editQuizIdx].id; // preserve id on edit
    list[_editQuizIdx] = quizObj;
  } else {
    list.push(quizObj);
  }
  saveStoredQuizzes(list);

  msgEl.textContent = isDraft ? '✓ Saved as draft.' : '✓ Quiz published!';
  msgEl.className   = 'quiz-form-msg ok';

  setTimeout(() => {
    closeQuizForm();
    loadQuizAdmin();
  }, 700);
}

function editQuiz(idx) {
  openQuizForm(idx);
}

document.getElementById('openNewQuizBtn').addEventListener('click', () => openQuizForm());
document.getElementById('quizFormBackdrop').addEventListener('click', closeQuizForm);
document.getElementById('qfAddQuestionBtn').addEventListener('click', () => addQuestionBlock());
document.getElementById('qfDraftBtn').addEventListener('click',   () => saveQuiz(true));
document.getElementById('qfPublishBtn').addEventListener('click', () => saveQuiz(false));

// Lazy-load quizzes tab when first clicked (handled by switchTab function now)


// ── Messages Panel ────────────────────────────────────────────────────────────

let _chatMsgId = null; // currently open contact_message id in admin chat

async function loadMessages() {
  const container = document.getElementById('messagesList');
  const badge     = document.getElementById('msgUnreadBadge');

  container.innerHTML = '<div class="loading-state">Loading…</div>';

  try {
    const res  = await fetch(API + '/contact');
    const json = await res.json();

    if (json.status !== 'success' || !json.data.length) {
      container.innerHTML = '<div class="empty-state">No messages yet.</div>';
      badge.style.display = 'none';
      return;
    }

    const messages = json.data;
    const unread   = messages.filter(m => m.status === 'unread').length;

    if (unread > 0) {
      badge.textContent   = unread + ' unread';
      badge.style.display = 'inline-block';
    } else {
      badge.style.display = 'none';
    }

    const statusLabel = s =>
      s === 'unread'  ? `<span style="font-size:11px;font-weight:700;color:var(--horizon,#e07b3a);">UNREAD</span>` :
      s === 'replied' ? `<span style="font-size:11px;font-weight:600;color:#43a047;">replied</span>` :
                        `<span style="font-size:11px;color:#888;">read</span>`;

    container.innerHTML = messages.map(m => `
      <div class="prayer-card msg-card" data-msg-id="${m.id}"
           style="${m.status === 'unread' ? 'border-left:3px solid var(--horizon,#e07b3a);' : m.status === 'replied' ? 'border-left:3px solid #43a047;opacity:.92;' : 'opacity:.82;'}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
          <div>
            <span style="font-weight:600;font-size:15px;">${escHtml(m.name)}</span>
            <span style="font-size:12px;color:var(--ink-soft);margin-left:8px;">${escHtml(m.email)}</span>
            ${m.member_id ? `<span style="font-size:11px;padding:1px 6px;border-radius:10px;background:#e8f4fd;color:#1565c0;margin-left:6px;">member</span>` : ''}
          </div>
          <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <span style="font-size:11px;padding:2px 8px;border-radius:12px;background:var(--paper,#f5f5f0);color:var(--ink-soft);">${escHtml(m.reason)}</span>
            <span style="font-size:11px;color:var(--ink-soft);">${escHtml(formatDate(m.created_at))}</span>
            ${statusLabel(m.status)}
          </div>
        </div>
        <p style="font-size:14px;color:var(--ink,#1a1a1a);line-height:1.6;white-space:pre-wrap;margin:0 0 12px;">${escHtml(m.message)}</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="btn-approve msg-reply-btn" data-id="${m.id}" data-name="${escHtml(m.name)}" data-email="${escHtml(m.email)}" data-reason="${escHtml(m.reason)}" data-body="${escHtml(m.message)}" data-member="${m.member_id||0}" style="font-size:12px;padding:5px 14px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Reply / Chat
          </button>
        </div>
      </div>
    `).join('');

    container.querySelectorAll('.msg-reply-btn').forEach(btn => {
      btn.addEventListener('click', () => openAdminChat(
        parseInt(btn.dataset.id, 10),
        btn.dataset.name,
        btn.dataset.email,
        btn.dataset.reason,
        btn.dataset.body,
        parseInt(btn.dataset.member, 10)
      ));
    });

  } catch (err) {
    container.innerHTML = `<div class="empty-state">Failed to load messages. ${escHtml(err.message)}</div>`;
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
       + ' ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

// ── Admin Chat Modal ──────────────────────────────────────────────────────────

const adminChatModal    = document.getElementById('adminChatModal');
const adminChatBackdrop = document.getElementById('adminChatBackdrop');
const adminChatClose    = document.getElementById('adminChatClose');
const adminChatTitle    = document.getElementById('adminChatTitle');
const adminChatMeta     = document.getElementById('adminChatMeta');
const adminChatOriginal = document.getElementById('adminChatOriginal');
const adminChatThread   = document.getElementById('adminChatThread');
const adminChatNoMember = document.getElementById('adminChatNoMember');
const adminChatInput    = document.getElementById('adminChatInput');
const adminChatSend     = document.getElementById('adminChatSend');
const adminChatMsg      = document.getElementById('adminChatMsg');

function buildChatBubble(msg) {
  const isAdmin = msg.sender_type === 'admin';
  const name    = isAdmin ? 'Admin' : (escHtml(msg.sender_name) || 'Member');
  const time    = formatDate(msg.created_at);
  return `
    <div style="display:flex;flex-direction:column;align-items:${isAdmin ? 'flex-end' : 'flex-start'};">
      <div style="max-width:80%;background:${isAdmin ? '#CD7642' : 'var(--paper,#f5f0e8)'};
                  color:${isAdmin ? '#fff' : 'var(--ink,#1a1a1a)'};
                  border-radius:${isAdmin ? '14px 14px 4px 14px' : '14px 14px 14px 4px'};
                  padding:10px 14px;font-size:13px;line-height:1.6;white-space:pre-wrap;word-break:break-word;">
        ${escHtml(msg.body)}
      </div>
      <span style="font-size:11px;color:var(--ink-soft,#888);margin-top:3px;">${name} · ${time}</span>
    </div>`;
}

async function openAdminChat(id, name, email, reason, originalBody, memberId) {
  _chatMsgId = id;
  adminChatTitle.textContent   = `Chat with ${name}`;
  adminChatMeta.textContent    = `${email} · ${reason}`;
  adminChatOriginal.textContent = originalBody;
  adminChatInput.value          = '';
  adminChatMsg.textContent      = '';
  adminChatNoMember.style.display = memberId ? 'none' : 'block';
  adminChatThread.innerHTML = '<div style="color:var(--ink-soft,#888);font-size:13px;">Loading thread…</div>';
  adminChatModal.hidden = false;

  // Load existing thread messages
  try {
    const res  = await fetch(API + `/contact/${id}/thread`);
    const json = await res.json();
    if (json.status === 'success' && json.data.messages.length) {
      adminChatThread.innerHTML = json.data.messages.map(buildChatBubble).join('');
    } else {
      adminChatThread.innerHTML = '<div style="color:var(--ink-soft,#888);font-size:13px;">No replies yet — start the conversation.</div>';
    }
    adminChatThread.scrollTop = adminChatThread.scrollHeight;
  } catch {
    adminChatThread.innerHTML = '<div style="color:var(--ink-soft,#888);font-size:13px;">Could not load thread.</div>';
  }
}

function closeAdminChat() {
  adminChatModal.hidden = true;
  _chatMsgId = null;
}

adminChatClose.addEventListener('click',    closeAdminChat);
adminChatBackdrop.addEventListener('click', closeAdminChat);

adminChatSend.addEventListener('click', async () => {
  const body = adminChatInput.value.trim();
  if (!body || !_chatMsgId) return;

  adminChatSend.disabled    = true;
  adminChatSend.textContent = 'Sending…';
  adminChatMsg.textContent  = '';

  try {
    const res  = await fetch(API + `/contact/${_chatMsgId}/reply`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ body }),
    });
    const json = await res.json();

    if (json.status === 'success') {
      // Append new bubble
      const hasEmpty = adminChatThread.querySelector('div[style*="No replies"]');
      if (hasEmpty) adminChatThread.innerHTML = '';

      adminChatThread.insertAdjacentHTML('beforeend', buildChatBubble(json.data));
      adminChatThread.scrollTop = adminChatThread.scrollHeight;
      adminChatInput.value      = '';
      adminChatMsg.textContent  = '✓ Reply sent';
      setTimeout(() => { adminChatMsg.textContent = ''; }, 2500);

      // Refresh the message list so status updates to 'replied'
      loadMessages();
    } else {
      adminChatMsg.textContent = json.message || 'Failed to send.';
    }
  } catch {
    adminChatMsg.textContent = 'Network error. Try again.';
  } finally {
    adminChatSend.disabled    = false;
    adminChatSend.textContent = 'Send reply';
  }
});

// Submit with Ctrl/Cmd+Enter
adminChatInput.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault();
    adminChatSend.click();
  }
});

// Lazy-load messages tab when first clicked (handled by switchTab function now)


// ── Users Panel ───────────────────────────────────────────────────────────────

let allUsers = [];
let filteredUsers = [];

async function loadUsers() {
  const container = document.getElementById('usersList');
  const countEl   = document.getElementById('usersTotalCount');

  container.innerHTML = '<div class="loading-state">Loading…</div>';

  try {
    const res  = await fetch(API + '/members/all');
    const json = await res.json();

    if (json.status !== 'success') {
      container.innerHTML = '<div class="empty-state">Could not load users.</div>';
      return;
    }

    allUsers = json.data || [];
    filteredUsers = [...allUsers]; // Initialize filtered list
    container._loaded = true;
    countEl.textContent = allUsers.length;

    if (!allUsers.length) {
      container.innerHTML = '<div class="empty-state">No registered users yet.</div>';
      return;
    }

    renderUsersTable();
  } catch (err) {
    container.innerHTML = `<div class="empty-state">Failed to load users. ${escHtml(err.message)}</div>`;
  }
}

function renderUsersTable() {
  const container = document.getElementById('usersList');
  const showingCountEl = document.getElementById('usersShowingCount');
  
  showingCountEl.textContent = filteredUsers.length;

  if (!filteredUsers.length) {
    container.innerHTML = '<div class="empty-state">No users found matching your search.</div>';
    return;
  }

  const rows = filteredUsers.map(u => {
    // Handle profile picture - only show if it exists and is not null/empty
    let avatar = '/DigitalEvangelization/public/images/agape1.jpg';
    if (u.profile_picture && u.profile_picture.trim() !== '') {
      // Check if it's already a full URL or just a filename
      if (u.profile_picture.startsWith('http') || u.profile_picture.startsWith('/')) {
        avatar = u.profile_picture;
      } else {
        avatar = `/DigitalEvangelization/public/uploads/avatars/${u.profile_picture}`;
      }
    }
    
    const statusClass = `status-${u.status}`;
    const createdDate = new Date(u.created_at).toLocaleDateString('en-US', { 
      month: 'short', day: 'numeric', year: 'numeric' 
    });
    const lastLogin = u.last_login 
      ? new Date(u.last_login).toLocaleDateString('en-US', { 
          month: 'short', day: 'numeric', year: 'numeric' 
        })
      : 'Never';

    return `
      <tr data-user-id="${u.id}">
        <td>${u.id}</td>
        <td>
          <div class="user-info">
            <img src="${avatar}" alt="${escHtml(u.display_name)}" class="user-avatar" onerror="this.src='/DigitalEvangelization/public/images/agape1.jpg'">
            <div class="user-details">
              <div class="user-name">${escHtml(u.display_name)}</div>
              <div class="user-email">@${escHtml(u.username)}</div>
            </div>
          </div>
        </td>
        <td>${escHtml(u.email)}</td>
        <td><span class="status-badge ${statusClass}">${escHtml(u.status)}</span></td>
        <td>${createdDate}</td>
        <td>${lastLogin}</td>
        <td><button class="btn-user-action" onclick="openUserDetail(${u.id})">View Details</button></td>
      </tr>
    `;
  }).join('');

  container.innerHTML = `
    <table class="users-table">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Email</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Last Login</th>
          <th></th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  `;
}

// Search functionality
let searchTimeout;
document.getElementById('usersSearchInput').addEventListener('input', (e) => {
  clearTimeout(searchTimeout);
  const query = e.target.value.trim().toLowerCase();
  const msgEl = document.getElementById('usersSearchMsg');

  if (!query) {
    filteredUsers = [...allUsers];
    msgEl.textContent = '';
    renderUsersTable();
    return;
  }

  // Debounce search
  searchTimeout = setTimeout(() => {
    filteredUsers = allUsers.filter(u => {
      const displayName = u.display_name.toLowerCase();
      const username = u.username.toLowerCase();
      const email = u.email.toLowerCase();
      
      return displayName.includes(query) || 
             username.includes(query) || 
             email.includes(query);
    });

    if (filteredUsers.length === 0) {
      msgEl.textContent = `No users found matching "${e.target.value}"`;
      msgEl.style.color = 'var(--ink-soft)';
    } else {
      msgEl.textContent = `Found ${filteredUsers.length} user${filteredUsers.length === 1 ? '' : 's'}`;
      msgEl.style.color = 'var(--approve)';
    }

    renderUsersTable();
  }, 300);
});

// ── User Detail Modal ─────────────────────────────────────────────────────────

function openUserDetail(userId) {
  const user = allUsers.find(u => u.id === userId);
  if (!user) return;

  // Handle profile picture - only show if it exists and is not null/empty
  let avatar = '/DigitalEvangelization/public/images/agape1.jpg';
  if (user.profile_picture && user.profile_picture.trim() !== '') {
    // Check if it's already a full URL or just a filename
    if (user.profile_picture.startsWith('http') || user.profile_picture.startsWith('/')) {
      avatar = user.profile_picture;
    } else {
      avatar = `/DigitalEvangelization/public/uploads/avatars/${user.profile_picture}`;
    }
  }
  
  const createdDate = new Date(user.created_at).toLocaleString('en-US', { 
    month: 'long', day: 'numeric', year: 'numeric', 
    hour: 'numeric', minute: '2-digit' 
  });
  
  const lastLogin = user.last_login 
    ? new Date(user.last_login).toLocaleString('en-US', { 
        month: 'long', day: 'numeric', year: 'numeric', 
        hour: 'numeric', minute: '2-digit' 
      })
    : 'Never logged in';

  document.getElementById('userDetailId').value = userId;
  document.getElementById('userDetailCurrentStatus').value = user.status;
  const avatarImg = document.getElementById('userDetailAvatar');
  avatarImg.src = avatar;
  avatarImg.onerror = function() { this.src = '/DigitalEvangelization/public/images/agape1.jpg'; };
  document.getElementById('userDetailDisplayName').textContent = user.display_name;
  document.getElementById('userDetailUsername').textContent = '@' + user.username;
  document.getElementById('userDetailEmail').textContent = user.email;
  
  const statusBadge = `<span class="status-badge status-${user.status}">${user.status}</span>`;
  document.getElementById('userDetailStatus').innerHTML = statusBadge;
  
  document.getElementById('userDetailCreated').textContent = createdDate;
  document.getElementById('userDetailLastLogin').textContent = lastLogin;

  // Show/hide status buttons based on current status
  document.getElementById('userSetActive').style.display = user.status === 'active' ? 'none' : 'inline-block';
  document.getElementById('userSetInactive').style.display = user.status === 'inactive' ? 'none' : 'inline-block';
  document.getElementById('userSetBanned').style.display = user.status === 'banned' ? 'none' : 'inline-block';

  document.getElementById('userDetailModal').hidden = false;
}

function closeUserDetail() {
  document.getElementById('userDetailModal').hidden = true;
}

document.getElementById('userDetailClose').addEventListener('click', closeUserDetail);
document.getElementById('userDetailBackdrop').addEventListener('click', closeUserDetail);

async function changeUserStatus(userId, newStatus) {
  const statusLabels = {
    'active': 'activate',
    'inactive': 'set as inactive',
    'banned': 'ban'
  };

  const ok = await showConfirm({ 
    title: `${statusLabels[newStatus]} this user?`, 
    message: `This will change the user's account status to "${newStatus}".`, 
    okLabel: 'Confirm',
    danger: newStatus === 'banned'
  });
  
  if (!ok) return;

  try {
    const res = await fetch(API + `/members/${userId}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: newStatus })
    });
    
    const json = await res.json();

    if (json.status === 'success') {
      // Update local cache
      const idx = allUsers.findIndex(u => u.id === userId);
      if (idx !== -1) allUsers[idx].status = newStatus;

      // Reload users list
      loadUsers();
      closeUserDetail();
    } else {
      alert('Error: ' + json.message);
    }
  } catch (err) {
    alert('Network error: ' + err.message);
  }
}

document.getElementById('userSetActive').addEventListener('click', () => {
  const userId = parseInt(document.getElementById('userDetailId').value, 10);
  changeUserStatus(userId, 'active');
});

document.getElementById('userSetInactive').addEventListener('click', () => {
  const userId = parseInt(document.getElementById('userDetailId').value, 10);
  changeUserStatus(userId, 'inactive');
});

document.getElementById('userSetBanned').addEventListener('click', () => {
  const userId = parseInt(document.getElementById('userDetailId').value, 10);
  changeUserStatus(userId, 'banned');
});

// ── Reset Password Modal ──────────────────────────────────────────────────────

document.getElementById('userResetPassword').addEventListener('click', () => {
  const userId = parseInt(document.getElementById('userDetailId').value, 10);
  const user = allUsers.find(u => u.id === userId);
  if (!user) return;

  document.getElementById('resetPasswordUserId').value = userId;
  document.getElementById('resetPasswordUsername').textContent = user.display_name + ' (@' + user.username + ')';
  document.getElementById('resetNewPassword').value = '';
  document.getElementById('resetConfirmPassword').value = '';
  document.getElementById('resetPasswordMsg').textContent = '';
  document.getElementById('resetPasswordMsg').className = '';
  
  document.getElementById('resetPasswordModal').hidden = false;
  document.getElementById('resetNewPassword').focus();
});

function closeResetPasswordModal() {
  document.getElementById('resetPasswordModal').hidden = true;
  
  // Reset the modal to its initial state
  document.getElementById('resetNewPassword').style.display = 'block';
  document.getElementById('resetConfirmPassword').style.display = 'block';
  document.querySelectorAll('#resetPasswordModal .field').forEach(f => f.style.display = 'block');
  document.getElementById('resetPasswordConfirm').style.display = 'inline-block';
  document.querySelector('#resetPasswordModal h3').textContent = 'Reset User Password';
  document.getElementById('resetPasswordMsg').innerHTML = '';
}

document.getElementById('resetPasswordCancel').addEventListener('click', closeResetPasswordModal);
document.getElementById('resetPasswordBackdrop').addEventListener('click', closeResetPasswordModal);

document.getElementById('resetPasswordConfirm').addEventListener('click', async () => {
  const userId = parseInt(document.getElementById('resetPasswordUserId').value, 10);
  const newPassword = document.getElementById('resetNewPassword').value.trim();
  const confirmPassword = document.getElementById('resetConfirmPassword').value.trim();
  const msgEl = document.getElementById('resetPasswordMsg');
  const btn = document.getElementById('resetPasswordConfirm');

  // Validation
  if (!newPassword) {
    msgEl.textContent = 'Please enter a new password.';
    msgEl.style.color = 'var(--reject)';
    return;
  }

  if (newPassword.length < 8) {
    msgEl.textContent = 'Password must be at least 8 characters.';
    msgEl.style.color = 'var(--reject)';
    return;
  }

  if (newPassword !== confirmPassword) {
    msgEl.textContent = 'Passwords do not match.';
    msgEl.style.color = 'var(--reject)';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Resetting...';
  msgEl.textContent = '';

  try {
    const res = await fetch(API + `/members/${userId}/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ new_password: newPassword })
    });
    
    const json = await res.json();

    if (json.status === 'success') {
      const user = allUsers.find(u => u.id === userId);
      const userEmail = user ? user.email : 'the user';
      
      msgEl.innerHTML = `
        <div style="background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; padding: 14px; margin-top: 10px;">
          <div style="color: #14532d; font-weight: 600; margin-bottom: 8px;">✓ Password Reset Successfully!</div>
          <div style="color: #166534; font-size: 13px; line-height: 1.6; margin-bottom: 12px;">
            <strong>✉️ Email Sent:</strong> A notification email with the new password has been sent to <strong>${escHtml(userEmail)}</strong>. The user will receive instructions to log in and change their password.
          </div>
          <div style="background: #fff; border: 1px solid #86efac; border-radius: 6px; padding: 10px; margin-bottom: 10px;">
            <div style="font-size: 11px; color: #166534; font-weight: 600; margin-bottom: 4px;">NEW TEMPORARY PASSWORD:</div>
            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 14px; color: #14532d; font-weight: 600;" id="resetPasswordDisplay">${escHtml(newPassword)}</div>
          </div>
          <button onclick="copyResetPassword()" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; transition: background .2s;">
            📋 Copy Password
          </button>
          <div style="font-size: 12px; color: #166534; margin-top: 12px; padding-top: 12px; border-top: 1px solid #86efac;">
            <div style="margin-bottom: 8px;"><strong>📧 User Email:</strong> ${escHtml(userEmail)}</div>
            <div style="font-size: 11px; color: #166534; opacity: 0.9; background: #f0fdf4; padding: 8px; border-radius: 4px;">
              <strong>Note:</strong> The user has been notified via email and can log in immediately. They should change this temporary password after logging in for security.
            </div>
          </div>
        </div>
      `;
      msgEl.style.color = '';
      
      // Hide the form fields and show only the success message
      document.getElementById('resetNewPassword').style.display = 'none';
      document.getElementById('resetConfirmPassword').style.display = 'none';
      document.querySelectorAll('#resetPasswordModal .field').forEach(f => f.style.display = 'none');
      btn.style.display = 'none';
      
      // Update the modal title
      document.querySelector('#resetPasswordModal h3').textContent = 'Password Reset Complete';
      
      // Reload users list to refresh any status
      setTimeout(() => {
        loadUsers();
      }, 500);
    } else {
      msgEl.textContent = '✗ ' + (json.message || 'Failed to reset password.');
      msgEl.style.color = 'var(--reject)';
    }
  } catch (err) {
    msgEl.textContent = '✗ Network error: ' + err.message;
    msgEl.style.color = 'var(--reject)';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Reset Password';
  }
});

// Copy password to clipboard
window.copyResetPassword = function() {
  const passwordText = document.getElementById('resetPasswordDisplay').textContent;
  navigator.clipboard.writeText(passwordText).then(() => {
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = '✓ Copied!';
    btn.style.background = '#15803d';
    setTimeout(() => {
      btn.textContent = originalText;
      btn.style.background = '#16a34a';
    }, 2000);
  }).catch(() => {
    alert('Failed to copy. Please select and copy manually.');
  });
};

// Allow Enter key to submit in reset password modal
document.getElementById('resetConfirmPassword').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    document.getElementById('resetPasswordConfirm').click();
  }
});

// Lazy-load users tab when first clicked (handled by switchTab function now)

</script>
</body>
</html>
