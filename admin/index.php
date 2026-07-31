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
  <title>Admin — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --night:     #0A1B33;
      --dusk:      #1B3E68;
      --horizon:   #3E7CB1;
      --sun:       #7FC4E8;
      --sun-light: #D3EEFB;
      --paper:     #F3F7FA;
      --ink:       #14202E;
      --ink-soft:  #55677A;
      --line:      #DCE6ED;
      --white:     #FFFFFF;
      --gold:      #D4A847;
      --gold-dim:  rgba(212,168,71,.15);
      --radius:    6px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Work Sans', system-ui, sans-serif; background: var(--paper); color: var(--ink); line-height: 1.6; -webkit-font-smoothing: antialiased; }

    /* ── Header ── */
    .admin-header {
      background: var(--night);
      border-bottom: 1px solid rgba(127,196,232,.15);
      position: sticky; top: 0; z-index: 100;
    }
    .admin-header .inner {
      max-width: 1200px; margin: 0 auto; padding: 0 32px;
      display: flex; align-items: center; justify-content: space-between; height: 60px;
    }
    .admin-brand { display: flex; align-items: center; gap: 10px; }
    .admin-brand-mark {
      width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
      background: radial-gradient(circle at 35% 30%, var(--sun-light), var(--sun) 60%, var(--horizon) 100%);
    }
    .admin-brand-name { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; color: var(--white); line-height: 1; }
    .admin-brand-tag  { font-size: 9px; letter-spacing: .14em; text-transform: uppercase; color: #8FA9C4; margin-top: 3px; display: block; }
    .admin-header .admin-meta { display: flex; align-items: center; gap: 16px; }
    .admin-user { font-size: 13px; color: #A9BFD6; display: flex; align-items: center; gap: 6px; }
    .admin-user-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--sun); flex-shrink: 0; }
    .admin-header .logout-form { margin: 0; }
    .admin-header .logout-form button {
      background: #c62828; color: var(--white); border: none;
      padding: 7px 18px; border-radius: var(--radius);
      cursor: pointer; font-size: 13px; font-weight: 600;
      font-family: inherit; transition: background .2s;
    }
    .admin-header .logout-form button:hover { background: #a31a1a; }

    /* ── Tabs ── */
    .tab-bar { background: var(--white); border-bottom: 1px solid var(--line); }
    .tab-bar .inner { max-width: 1200px; margin: 0 auto; padding: 0 32px; display: flex; gap: 0; }
    .tab-btn {
      padding: 15px 20px; background: none; border: none;
      border-bottom: 3px solid transparent; font-size: 13px; font-weight: 600;
      color: var(--ink-soft); cursor: pointer; font-family: inherit;
      transition: color .2s;
    }
    .tab-btn:hover { color: var(--ink); }
    .tab-btn.active { color: var(--night); border-bottom-color: var(--sun); }

    /* ── Container ── */
    .container { max-width: 1200px; margin: 0 auto; padding: 36px 32px; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .section-title { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 600; color: var(--ink); margin-bottom: 24px; }

    /* ── Login ── */
    .login-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-card { background: var(--white); border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 4px 24px rgba(10,27,51,.1); border: 1px solid var(--line); }
    .login-card h2 { font-family: 'Fraunces', serif; margin-bottom: 24px; font-size: 22px; color: var(--night); }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
    .field input, .field select, .field textarea {
      width: 100%; padding: 10px 12px; border: 1px solid var(--line);
      border-radius: var(--radius); font-size: 14px; font-family: inherit;
      background: var(--white); color: var(--ink); transition: border-color .2s, box-shadow .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus {
      outline: none; border-color: var(--sun); box-shadow: 0 0 0 3px rgba(127,196,232,.2);
    }
    .field textarea { resize: vertical; }
    .btn-login {
      width: 100%; padding: 12px; background: var(--night); color: var(--white);
      border: none; border-radius: var(--radius); font-size: 15px; font-weight: 600;
      cursor: pointer; margin-top: 8px; font-family: inherit; transition: background .2s;
    }
    .btn-login:hover { background: var(--dusk); }
    .error-msg { background: #fff0f0; color: #c62828; padding: 12px; border-radius: var(--radius); margin-bottom: 16px; font-size: 14px; border: 1px solid #ffcdd2; }

    /* ── Prayer cards ── */
    .prayer-card {
      background: var(--white); border-radius: 10px; padding: 22px 24px;
      margin-bottom: 14px; border: 1px solid var(--line);
      box-shadow: 0 1px 4px rgba(10,27,51,.06);
      transition: box-shadow .2s, transform .2s;
    }
    .prayer-card:hover { box-shadow: 0 4px 16px rgba(10,27,51,.1); transform: translateY(-1px); }
    .prayer-card .p-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; color: var(--ink-soft); }
    .prayer-card .p-name { font-weight: 600; color: var(--night); font-size: 14px; }
    .cat-badge { background: rgba(127,196,232,.18); color: var(--dusk); padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-left: 8px; letter-spacing: .03em; }
    .prayer-card .p-body { color: var(--ink); margin-bottom: 16px; font-size: 14px; line-height: 1.65; }
    .prayer-actions { display: flex; gap: 10px; }
    .btn-approve {
      background: #2e7d32; color: var(--white); border: none;
      padding: 8px 20px; border-radius: var(--radius); cursor: pointer;
      font-weight: 600; font-size: 13px; font-family: inherit; transition: background .2s;
    }
    .btn-reject {
      background: #c62828; color: var(--white); border: none;
      padding: 8px 20px; border-radius: var(--radius); cursor: pointer;
      font-weight: 600; font-size: 13px; font-family: inherit; transition: background .2s;
    }
    .btn-approve:hover { background: #1b5e20; }
    .btn-reject:hover  { background: #a31a1a; }

    /* ── Media table ── */
    .media-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 10px; overflow: hidden; border: 1px solid var(--line); box-shadow: 0 1px 4px rgba(10,27,51,.06); }
    .media-table th { background: var(--night); color: var(--sun-light); font-size: 11px; text-transform: uppercase; letter-spacing: 0.07em; padding: 13px 16px; text-align: left; font-weight: 600; }
    .media-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; color: var(--ink); }
    .media-table tr:last-child td { border-bottom: none; }
    .media-table tr:hover td { background: var(--paper); }
    .type-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .type-sermon     { background: rgba(62,124,177,.12); color: var(--dusk); }
    .type-devotional { background: rgba(91,155,143,.12); color: #2e6b61; }
    .type-testimony  { background: rgba(212,168,71,.15); color: #7a5c00; }
    .type-worship    { background: rgba(127,196,232,.15); color: var(--horizon); }
    .url-cell { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--horizon); font-size: 12px; }
    .url-cell.empty { color: #c62828; font-style: italic; }
    .btn-edit {
      background: var(--dusk); color: var(--white); border: none;
      padding: 6px 14px; border-radius: var(--radius); cursor: pointer;
      font-size: 12px; font-weight: 600; font-family: inherit; white-space: nowrap; transition: background .2s;
    }
    .btn-edit:hover { background: var(--night); }

    /* ── Edit modal ── */
    .edit-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .edit-modal[hidden] { display: none; }
    .edit-backdrop { position: absolute; inset: 0; background: rgba(10,27,51,.55); backdrop-filter: blur(2px); }
    .edit-box {
      position: relative; background: var(--white); border-radius: 12px;
      padding: 32px; width: 100%; max-width: 560px;
      box-shadow: 0 16px 48px rgba(10,27,51,.2); border: 1px solid var(--line);
    }
    .edit-box h3 { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; margin-bottom: 22px; color: var(--night); }
    .edit-box .field input { font-size: 13px; }
    .edit-box .field label { font-size: 12px; color: var(--ink-soft); }
    .edit-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; }
    .btn-save {
      background: var(--night); color: var(--white); border: none;
      padding: 10px 26px; border-radius: var(--radius); font-weight: 600;
      cursor: pointer; font-family: inherit; transition: background .2s;
    }
    .btn-cancel {
      background: var(--paper); color: var(--ink-soft); border: 1px solid var(--line);
      padding: 10px 20px; border-radius: var(--radius); font-weight: 600;
      cursor: pointer; font-family: inherit; transition: background .2s;
    }
    .btn-save:hover   { background: var(--dusk); }
    .btn-cancel:hover { background: var(--line); }
    .save-msg { font-size: 13px; margin-top: 10px; min-height: 20px; }
    .save-msg.ok  { color: #2e7d32; }
    .save-msg.err { color: #c62828; }

    .empty-state { text-align: center; padding: 64px 20px; color: var(--ink-soft); font-size: 15px; }
    .loading-state { text-align: center; padding: 40px 20px; color: var(--ink-soft); }

    /* ── Events ── */
    .event-admin-card {
      background: var(--white); border-radius: 10px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 1px 4px rgba(10,27,51,.06);
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
      transition: box-shadow .2s, transform .2s;
    }
    .event-admin-card:hover { box-shadow: 0 4px 16px rgba(10,27,51,.1); transform: translateY(-1px); }
    .event-admin-meta { flex: 1; min-width: 0; }
    .event-admin-title { font-size: 15px; font-weight: 600; color: var(--night); margin-bottom: 4px; }
    .event-admin-detail { font-size: 13px; color: var(--ink-soft); }
    .event-admin-count { background: rgba(62,124,177,.12); color: var(--dusk); padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; white-space: nowrap; }
    .btn-view-reg {
      background: var(--dusk); color: var(--white); border: none;
      padding: 7px 16px; border-radius: var(--radius); font-size: 13px;
      font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; transition: background .2s;
    }
    .btn-view-reg:hover { background: var(--night); }
    .reg-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 10px; overflow: hidden; border: 1px solid var(--line); box-shadow: 0 1px 4px rgba(10,27,51,.06); }
    .reg-table th { background: var(--night); color: var(--sun-light); font-size: 11px; text-transform: uppercase; letter-spacing: 0.07em; padding: 13px 16px; text-align: left; font-weight: 600; }
    .reg-table td { padding: 12px 16px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
    .reg-table tr:last-child td { border-bottom: none; }
    .reg-table tr:hover td { background: var(--paper); }
    .join-badge-online    { background: rgba(62,124,177,.12); color: var(--dusk); padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .join-badge-in_person { background: rgba(91,155,143,.12); color: #2e6b61; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }

    /* ── Announcements ── */
    .btn-new-ann {
      background: var(--night); color: var(--white); border: none;
      padding: 10px 22px; border-radius: var(--radius); font-size: 13px;
      font-weight: 600; cursor: pointer; font-family: inherit; transition: background .2s;
    }
    .btn-new-ann:hover { background: var(--dusk); }

    .ann-admin-card {
      background: var(--white); border-radius: 10px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 1px 4px rgba(10,27,51,.06);
      display: flex; align-items: flex-start; gap: 16px;
      transition: box-shadow .2s, transform .2s;
    }
    .ann-admin-card:hover { box-shadow: 0 4px 16px rgba(10,27,51,.1); transform: translateY(-1px); }
    .ann-admin-card .ann-badge { flex-shrink: 0; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .ann-badge-Ministry  { background: rgba(62,124,177,.12);  color: var(--dusk); }
    .ann-badge-Events    { background: rgba(91,155,143,.12);  color: #2e6b61; }
    .ann-badge-Community { background: rgba(212,168,71,.15);  color: #7a5c00; }
    .ann-badge-Urgent    { background: rgba(198,40,40,.1);    color: #c62828; }
    .ann-admin-body { flex: 1; min-width: 0; }
    .ann-admin-title { font-size: 15px; font-weight: 600; color: var(--night); margin-bottom: 4px; }
    .ann-admin-excerpt { font-size: 13px; color: var(--ink-soft); line-height: 1.55; }
    .ann-admin-meta { font-size: 12px; color: var(--ink-soft); margin-top: 6px; opacity: .7; }
    .ann-pinned-chip {
      display: inline-block; background: var(--gold-dim); color: #7a5c00;
      border: 1px solid rgba(212,168,71,.4); padding: 1px 8px;
      border-radius: 10px; font-size: 11px; font-weight: 600; margin-left: 8px;
    }
    .ann-admin-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .btn-ann-edit {
      background: var(--dusk); color: var(--white); border: none;
      padding: 6px 14px; border-radius: var(--radius); cursor: pointer;
      font-size: 12px; font-weight: 600; font-family: inherit; transition: background .2s;
    }
    .btn-ann-delete {
      background: #c62828; color: var(--white); border: none;
      padding: 6px 14px; border-radius: var(--radius); cursor: pointer;
      font-size: 12px; font-weight: 600; font-family: inherit; transition: background .2s;
    }
    .btn-ann-edit:hover   { background: var(--night); }
    .btn-ann-delete:hover { background: #a31a1a; }

    /* announcement / event form modal */
    .ann-form-modal { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .ann-form-modal[hidden] { display: none; }
    .ann-form-backdrop { position: absolute; inset: 0; background: rgba(10,27,51,.55); backdrop-filter: blur(2px); }
    .ann-form-box {
      position: relative; background: var(--white); border-radius: 12px;
      padding: 32px; width: 100%; max-width: 600px;
      box-shadow: 0 16px 48px rgba(10,27,51,.2); border: 1px solid var(--line);
      max-height: 90vh; overflow-y: auto;
    }
    .ann-form-box h3 { font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600; margin-bottom: 22px; color: var(--night); }
    .ann-form-actions { display: flex; gap: 10px; margin-top: 22px; justify-content: flex-end; }
    .ann-form-msg { font-size: 13px; margin-top: 10px; min-height: 20px; }
    .ann-form-msg.ok  { color: #2e7d32; }
    .ann-form-msg.err { color: #c62828; }

    /* ── Quiz admin ── */
    .quiz-admin-card {
      background: var(--white); border-radius: 10px; padding: 18px 22px;
      margin-bottom: 12px; border: 1px solid var(--line);
      box-shadow: 0 1px 4px rgba(10,27,51,.06);
      display: flex; align-items: flex-start; gap: 16px;
      transition: box-shadow .2s, transform .2s;
    }
    .quiz-admin-card:hover { box-shadow: 0 4px 16px rgba(10,27,51,.1); transform: translateY(-1px); }
    .quiz-admin-meta { flex: 1; min-width: 0; }
    .quiz-admin-title { font-size: 15px; font-weight: 600; color: var(--night); margin-bottom: 3px; }
    .quiz-admin-desc  { font-size: 13px; color: var(--ink-soft); }
    .quiz-admin-count { font-size: 12px; color: var(--ink-soft); margin-top: 4px; }
    .quiz-cat-badge   { background: rgba(62,124,177,.12); color: var(--dusk); padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; flex-shrink: 0; }
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
        <button type="submit">Logout</button>
      </form>
    </div>
  </div>
</div>

<div class="tab-bar">
  <div class="inner">
    <button class="tab-btn active" data-tab="prayers">🙏 Prayer Requests</button>
    <button class="tab-btn" data-tab="articles">📝 Articles</button>
    <button class="tab-btn" data-tab="media-pending">🎬 Videos</button>
    <button class="tab-btn" data-tab="media">🎞 All Media</button>
    <button class="tab-btn" data-tab="announcements">📢 Announcements</button>
    <button class="tab-btn" data-tab="events">📅 Events</button>
    <button class="tab-btn" data-tab="quizzes">🧩 Quizzes</button>
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

</div><!-- /container -->

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

<script>
const API = '/DigitalEvangelization/api';

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
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
  if (action === 'reject' && !confirm('Reject this prayer request?')) return;
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
  if (action === 'reject' && !confirm('Reject this article?')) return;
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

// Lazy-load articles tab when first clicked
document.querySelectorAll('.tab-btn').forEach(btn => {
  if (btn.dataset.tab === 'articles') {
    btn.addEventListener('click', () => {
      if (!document.getElementById('articlePendingList')._loaded) {
        document.getElementById('articlePendingList')._loaded = true;
        loadArticlesPending();
      }
    });
  }
});

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
  if (action === 'reject' && !confirm('Reject this video?')) return;
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

// Lazy-load videos pending tab when first clicked
document.querySelectorAll('.tab-btn').forEach(btn => {
  if (btn.dataset.tab === 'media-pending') {
    btn.addEventListener('click', () => {
      if (!document.getElementById('videoPendingList')._loaded) {
        document.getElementById('videoPendingList')._loaded = true;
        loadVideosPending();
      }
    });
  }
});
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
  if (!confirm('Delete this announcement? This cannot be undone.')) return;
  const res  = await fetch(API + '/announcements/' + id, { method: 'DELETE' });
  const json = await res.json();
  if (json.status === 'success') {
    loadAnnouncements();
  } else {
    alert('Error: ' + json.message);
  }
}

// Lazy-load announcements tab when first clicked
document.querySelectorAll('.tab-btn').forEach(btn => {
  if (btn.dataset.tab === 'announcements') {
    btn.addEventListener('click', () => {
      if (!document.getElementById('annAdminList')._loaded) {
        document.getElementById('annAdminList')._loaded = true;
        loadAnnouncements();
      }
    });
  }
});

// ── Events Panel ─────────────────────────────────────────────────────────────

async function loadAdminEvents() {
  const container = document.getElementById('eventsAdminList');
  try {
    const res  = await fetch(API + '/events/all');
    const json = await res.json();

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
          <button class="btn-view-reg" onclick="loadRegistrants(${e.id}, ${escHtml(JSON.stringify(e.title))})">
            View Registrants
          </button>
        </div>
      `;
    }).join('');
  } catch (err) {
    container.innerHTML = '<div class="empty-state">Could not load events.</div>';
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

// Lazy-load events tab when first clicked
document.querySelectorAll('.tab-btn').forEach(btn => {
  if (btn.dataset.tab === 'events') {
    btn.addEventListener('click', () => {
      if (!document.getElementById('eventsAdminList')._loaded) {
        document.getElementById('eventsAdminList')._loaded = true;
        loadAdminEvents();
      }
    });
  }
});

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

function deleteQuiz(idx) {
  if (!confirm('Delete this quiz? This cannot be undone.')) return;
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

// Lazy-load quizzes tab when first clicked
document.querySelectorAll('.tab-btn').forEach(btn => {
  if (btn.dataset.tab === 'quizzes') {
    btn.addEventListener('click', () => {
      if (!document.getElementById('quizAdminList')._loaded) {
        document.getElementById('quizAdminList')._loaded = true;
        loadQuizAdmin();
      }
    });
  }
});
</script>
</body>
</html>
