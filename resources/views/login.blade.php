<!doctype html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Global Voice — Kirish</title>
<link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml" sizes="any"/>
<link rel="shortcut icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml"/>
<meta name="theme-color" content="#be123c">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.icon{display:inline-block;width:1em;height:1em;vertical-align:-.125em;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.icon-sprite{position:absolute;width:0;height:0;overflow:hidden}

:root{
  --blue:#e11d48;
  --blue-d:#9f1239;
  --cyan:#fb7185;
  --indigo:#f97316;
  --text:#e2e8f0;
  --muted:#64748b;
  --dim:#334155;
  --surface:rgba(255,255,255,0.05);
  --border:rgba(255,255,255,0.09);
}

html,body{
  height:100%;
  font-family:'Poppins',sans-serif;
  background:#070d1a;
  color:var(--text);
  overflow:hidden;
}

/* ===== WELCOME SCREEN ===== */
#welcome{
  position:fixed;inset:0;z-index:9999;
  display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  background:#070d1a;overflow:hidden;
}
#welcome.hide{animation:wOut .25s ease forwards}
@keyframes wOut{to{opacity:0;pointer-events:none}}

.w-bg-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(225,29,72,.06) 1px,transparent 1px),
    linear-gradient(90deg,rgba(225,29,72,.06) 1px,transparent 1px);
  background-size:52px 52px;
}
.w-orb{position:absolute;border-radius:50%;pointer-events:none}
.w-orb1{
  width:460px;height:460px;top:-140px;left:-120px;
  background:radial-gradient(circle,rgba(225,29,72,.22),transparent 68%);
  animation:wDrift1 7s ease-in-out infinite;
}
.w-orb2{
  width:360px;height:360px;bottom:-80px;right:-80px;
  background:radial-gradient(circle,rgba(251,113,133,.16),transparent 68%);
  animation:wDrift2 9s ease-in-out infinite;
}
@keyframes wDrift1{0%,100%{transform:translate(0,0)}50%{transform:translate(22px,28px)}}
@keyframes wDrift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-18px,-22px)}}

.w-body{position:relative;z-index:2;text-align:center}
.w-ring-wrap{position:relative;width:100px;height:100px;margin:0 auto 28px}
.w-ring{
  position:absolute;inset:0;border-radius:50%;
  border:1.5px solid rgba(225,29,72,.35);
  animation:wRingPop 1.8s ease-out infinite;
}
.w-ring:nth-child(2){animation-delay:.6s}
.w-ring:nth-child(3){animation-delay:1.2s}
@keyframes wRingPop{0%{transform:scale(.5);opacity:.8}100%{transform:scale(1.6);opacity:0}}
.w-logo{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,var(--blue),var(--blue-d));
  border-radius:50%;font-size:32px;color:#fff;
  animation:wLogoIn .3s cubic-bezier(.34,1.56,.64,1) .05s both;
}
@keyframes wLogoIn{from{transform:scale(.4);opacity:0}to{transform:scale(1);opacity:1}}

.w-title{
  font-family:'Orbitron',sans-serif;
  font-size:clamp(2rem,6vw,3.4rem);
  font-weight:700;color:#fff;letter-spacing:4px;
  opacity:0;animation:wFadeUp .3s ease .25s forwards;
}
.w-title span{color:var(--blue)}
.w-sub{
  font-size:15px;color:var(--muted);margin-top:10px;
  opacity:0;animation:wFadeUp .25s ease .45s forwards;
}
.w-progress{
  width:180px;height:2px;background:var(--border);
  border-radius:2px;margin:28px auto 0;overflow:hidden;
  opacity:0;animation:wFadeUp .2s ease .58s forwards;
}
.w-bar{
  height:100%;width:0;
  background:linear-gradient(90deg,var(--blue),var(--cyan));
  animation:wBarFill .28s ease .68s forwards;
}
@keyframes wBarFill{to{width:100%}}
@keyframes wFadeUp{
  from{opacity:0;transform:translateY(14px)}
  to{opacity:1;transform:translateY(0)}
}

/* ===== PAGE ===== */
#page{
  position:relative;height:100vh;
  display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .25s ease;
}
#page.on{opacity:1}

/* ===== BACKGROUND ===== */
.bg-wrap{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}
.bg-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(225,29,72,.045) 1px,transparent 1px),
    linear-gradient(90deg,rgba(225,29,72,.045) 1px,transparent 1px);
  background-size:52px 52px;
}
.bg-orb{position:absolute;border-radius:50%}
.bg-orb1{
  width:600px;height:600px;top:-180px;left:-160px;
  background:radial-gradient(circle,rgba(225,29,72,.16),transparent 65%);
  animation:bgDA 14s ease-in-out infinite;
}
.bg-orb2{
  width:500px;height:500px;bottom:-120px;right:-100px;
  background:radial-gradient(circle,rgba(251,113,133,.12),transparent 65%);
  animation:bgDB 18s ease-in-out infinite;
}
.bg-orb3{
  width:340px;height:340px;top:50%;left:50%;
  transform:translate(-50%,-50%);
  background:radial-gradient(circle,rgba(249,115,22,.1),transparent 68%);
  animation:bgDA 11s ease-in-out 3s infinite reverse;
}
@keyframes bgDA{0%,100%{transform:translate(0,0)}50%{transform:translate(20px,26px)}}
@keyframes bgDB{0%,100%{transform:translate(0,0)}50%{transform:translate(-18px,-22px)}}

/* ===== LEFT — Globe ===== */
.side-left{
  position:fixed;left:0;top:0;bottom:0;
  width:calc(50% - 215px);
  z-index:1;pointer-events:none;
  display:flex;align-items:center;justify-content:flex-end;
  padding-right:30px;overflow:hidden;
}
.globe-wrap{width:min(400px,90%);height:min(400px,90%);flex-shrink:0;filter:hue-rotate(138deg) saturate(1.45)}
.globe-wrap svg{width:100%;height:100%}

.gRA{animation:gRA 14s linear infinite;transform-origin:160px 160px}
.gRB{animation:gRB 20s linear infinite;transform-origin:160px 160px}
.gRC{animation:gRC 10s linear infinite;transform-origin:160px 160px}
@keyframes gRA{to{transform:rotate(360deg)}}
@keyframes gRB{to{transform:rotate(-360deg)}}
@keyframes gRC{to{transform:rotate(360deg)}}

.gDA{animation:gdA 5s linear infinite;transform-origin:160px 160px}
.gDB{animation:gdB 8s linear infinite;transform-origin:160px 160px}
.gDC{animation:gdC 6.5s linear infinite;transform-origin:160px 160px}
@keyframes gdA{to{transform:rotate(360deg)}}
@keyframes gdB{to{transform:rotate(-360deg)}}
@keyframes gdC{to{transform:rotate(360deg)}}

.gCore{animation:gCore 3.2s ease-in-out infinite;transform-origin:160px 160px}
@keyframes gCore{0%,100%{transform:scale(1);opacity:.88}50%{transform:scale(1.07);opacity:1}}

.gSig{stroke-dasharray:6 4;animation:gSigM 2.4s linear infinite}
@keyframes gSigM{to{stroke-dashoffset:-40}}

/* ===== CENTER — Login card ===== */
.login-wrap{position:relative;z-index:10;width:420px;max-width:92vw}

.login-card{
  background:rgba(10,18,36,.84);
  backdrop-filter:blur(24px);
  border:1px solid rgba(225,29,72,.22);
  border-radius:22px;
  padding:2.6rem 2.2rem;
  box-shadow:
    0 0 0 1px rgba(225,29,72,.07) inset,
    0 32px 80px rgba(0,0,0,.55),
    0 0 60px rgba(225,29,72,.10);
}

.card-top{display:flex;align-items:center;gap:11px;margin-bottom:1.8rem}
.card-logo-circle{
  width:42px;height:42px;border-radius:50%;
  background:linear-gradient(135deg,var(--blue),var(--blue-d));
  display:flex;align-items:center;justify-content:center;
  font-size:20px;color:#fff;flex-shrink:0;
}
.card-brand{
  font-family:'Orbitron',sans-serif;
  font-size:13px;font-weight:700;
  color:#e2e8f0;letter-spacing:2px;
}
.card-online{
  margin-left:auto;display:flex;align-items:center;gap:5px;
  font-size:11px;color:var(--muted);
}
.online-dot{
  width:6px;height:6px;border-radius:50%;background:#22c55e;
  animation:odBlink 2.5s infinite;
}
@keyframes odBlink{0%,100%{opacity:1}50%{opacity:.3}}

.card-title{font-size:21px;font-weight:600;color:#f1f5f9;margin-bottom:4px}
.card-sub{font-size:12px;color:var(--muted);margin-bottom:1.7rem}

.field-lbl{
  font-size:11px;font-weight:500;letter-spacing:.7px;
  text-transform:uppercase;color:#94a3b8;
  margin-bottom:6px;display:block;
}
.field-wrap{position:relative;margin-bottom:1.1rem}
.fi{
  position:absolute;left:12px;top:50%;
  transform:translateY(-50%);
  font-size:16px;color:var(--blue);pointer-events:none;
}
.eye-btn{
  position:absolute;right:12px;top:50%;
  transform:translateY(-50%);
  background:none;border:none;cursor:pointer;
  color:var(--dim);font-size:16px;padding:0;line-height:1;
  transition:color .2s;
}
.eye-btn:hover{color:var(--text)}
.field-input{
  width:100%;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  border-radius:11px;
  color:var(--text);
  font-family:'Poppins',sans-serif;font-size:13px;
  padding:11px 38px;
  outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}
.field-input::placeholder{color:#2a3a50}
.field-input:focus{
  border-color:rgba(225,29,72,.55);
  background:rgba(225,29,72,.07);
  box-shadow:0 0 0 3px rgba(225,29,72,.1);
}
.field-input.err{border-color:rgba(239,68,68,.45);background:rgba(239,68,68,.05)}
.err-msg{font-size:11px;color:#f87171;margin-top:3px;display:none}

.remember-row{display:flex;align-items:center;gap:8px;margin-bottom:1.4rem}
.remember-row input[type=checkbox]{width:14px;height:14px;accent-color:var(--blue);cursor:pointer}
.remember-lbl{font-size:12px;color:var(--muted);cursor:pointer}

.login-btn{
  width:100%;
  background:linear-gradient(135deg,var(--blue),var(--blue-d));
  border:none;border-radius:11px;
  color:#fff;font-family:'Poppins',sans-serif;
  font-size:14px;font-weight:500;
  padding:12px;cursor:pointer;letter-spacing:.5px;
  display:flex;align-items:center;justify-content:center;gap:8px;
  transition:opacity .2s,transform .15s,box-shadow .2s;
}
.login-btn:hover{opacity:.88;transform:translateY(-1px);box-shadow:0 8px 28px rgba(225,29,72,.35)}
.login-btn:active{transform:scale(.98)}
.btn-spinner{
  display:none;width:16px;height:16px;
  border:2px solid rgba(255,255,255,.3);border-top-color:#fff;
  border-radius:50%;animation:spin .7s linear infinite;
}
.login-btn.loading .btn-text{display:none}
.login-btn.loading .btn-spinner{display:block}
.btn-text{display:inline-flex;align-items:center;gap:8px}
@keyframes spin{to{transform:rotate(360deg)}}

.sep{display:flex;align-items:center;gap:10px;margin:1.2rem 0}
.sep-line{flex:1;height:1px;background:var(--border)}
.sep-txt{font-size:11px;color:var(--dim)}

.sec-badge{display:flex;align-items:center;justify-content:center;gap:6px}
.sec-txt{font-size:11px;color:var(--dim)}

.alert-box{
  display:none;
  background:rgba(239,68,68,.1);
  border:1px solid rgba(239,68,68,.28);
  border-radius:9px;
  padding:10px 14px;
  font-size:12px;color:#fca5a5;
  margin-bottom:1rem;
  align-items:center;gap:8px;
}

/* Theme button */
.theme-btn{
  position:fixed;top:18px;left:18px;z-index:200;
  width:42px;height:42px;border-radius:50%;
  background:rgba(255,255,255,.06);
  border:1px solid var(--border);
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  font-size:18px;color:var(--text);
  transition:background .2s,transform .2s;
}
.theme-btn:hover{background:rgba(255,255,255,.12);transform:scale(1.08)}
.theme-btn:focus-visible{outline:3px solid rgba(225,29,72,.45);outline-offset:3px}

/* Light mode */
body.light,body.light #page{background:#fff1f2}
body.light .login-card{
  background:rgba(255,255,255,.93);
  border-color:rgba(225,29,72,.22);
  box-shadow:0 32px 80px rgba(0,0,0,.1);
}
body.light .card-title{color:#0f172a}
body.light .card-brand{color:#1e293b}
body.light .card-sub{color:#64748b}
body.light .field-input{
  background:rgba(255,255,255,.8);color:#1e293b;
  border-color:rgba(0,0,0,.1);
}
body.light .field-input::placeholder{color:#cbd5e1}
body.light .theme-btn{
  background:#fff;color:#9f1239;border-color:#fb7185;
  box-shadow:0 4px 14px rgba(15,23,42,.16);
}
body.light .theme-btn:hover{background:#ffe4e6}
body.light .bg-grid{
  background-image:
    linear-gradient(rgba(225,29,72,.10) 1px,transparent 1px),
    linear-gradient(90deg,rgba(225,29,72,.10) 1px,transparent 1px);
}
/* ===== RESPONSIVE ===== */

/* Tablet — yon animatsiyalar yashirinadi */
@media(max-width:960px){
  .side-left{display:none}
  #page{background:#070d1a}
}

/* Kichik telefon — login karta to'liq ekran */
@media(max-width:600px){
  html,body{overflow-y:auto}
  #page{
    height:auto;min-height:100vh;
    align-items:flex-start;
    padding:20px 0 40px;
  }
  .login-wrap{width:100%;max-width:100%;padding:0 16px}
  .login-card{
    border-radius:18px;
    padding:2rem 1.4rem;
  }
  .card-brand{font-size:11px;letter-spacing:1.5px}
  .card-title{font-size:19px}
  .field-input{font-size:14px;padding:13px 40px}
  .login-btn{padding:13px;font-size:14px}

  /* Welcome screen telefonda */
  .w-title{font-size:2rem;letter-spacing:2px}
  .w-sub{font-size:13px}
  .w-ring-wrap{width:80px;height:80px}
  .w-logo{font-size:26px}
}

/* Juda kichik ekranlar */
@media(max-width:380px){
  .login-card{padding:1.6rem 1.1rem}
  .card-top{margin-bottom:1.3rem}
  .card-logo-circle{width:36px;height:36px;font-size:17px}
  .card-title{font-size:17px}
  .card-sub{margin-bottom:1.3rem}
  .field-input{padding:11px 36px}
}
</style>
</head>
<body>

<!-- Local SVG icons: always available, including after a refresh. -->
<svg class="icon-sprite" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
  <symbol id="icon-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9S14.4 18.5 12 21M12 3c-2.4 2.5-3.6 5.5-3.6 9s1.2 6.5 3.6 9"/></symbol>
  <symbol id="icon-moon" viewBox="0 0 24 24"><path d="M20.4 15.3A9 9 0 0 1 8.7 3.6 9 9 0 1 0 20.4 15.3z"/></symbol>
  <symbol id="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></symbol>
  <symbol id="icon-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></symbol>
  <symbol id="icon-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21a7 7 0 0 1 14 0"/></symbol>
  <symbol id="icon-lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 15v2"/></symbol>
  <symbol id="icon-eye" viewBox="0 0 24 24"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.5"/></symbol>
  <symbol id="icon-eye-off" viewBox="0 0 24 24"><path d="M3 3l18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.4 17.4 0 0 1-3.1 3.7M6.1 6.1A17.5 17.5 0 0 0 2.5 12S6 18 12 18a10.8 10.8 0 0 0 3-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></symbol>
  <symbol id="icon-login" viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></symbol>
  <symbol id="icon-shield" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.7-2.8 8.3-7 10-4.2-1.7-7-5.3-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></symbol>
</svg>

<!-- ===== WELCOME ===== -->
<div id="welcome">
  <div class="w-bg-grid"></div>
  <div class="w-orb w-orb1"></div>
  <div class="w-orb w-orb2"></div>
  <div class="w-body">
    <div class="w-ring-wrap">
      <div class="w-ring"></div>
      <div class="w-ring"></div>
      <div class="w-ring"></div>
      <div class="w-logo"><svg class="icon" aria-hidden="true"><use href="#icon-globe"/></svg></div>
    </div>
    <h1 class="w-title">GLOBAL <span>VOICE</span></h1>
    <p class="w-sub">Xush kelibsiz</p>
    <div class="w-progress"><div class="w-bar"></div></div>
  </div>
</div>

<!-- ===== PAGE ===== -->
<div id="page">

  <div class="bg-wrap" aria-hidden="true">
    <div class="bg-grid"></div>
    <div class="bg-orb bg-orb1"></div>
    <div class="bg-orb bg-orb2"></div>
    <div class="bg-orb bg-orb3"></div>
  </div>

  <button class="theme-btn" id="themeBtn" aria-label="Mavzuni o'zgartirish">
    <svg class="icon" aria-hidden="true"><use id="themeIcon" href="#icon-moon"/></svg>
  </button>

  <!-- LEFT — Globe -->
  <div class="side-left" aria-hidden="true">
    <div class="globe-wrap">
      <svg viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <radialGradient id="glG" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="#3b82f6" stop-opacity=".2"/>
            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
          </radialGradient>
          <radialGradient id="gbG" cx="36%" cy="32%" r="68%">
            <stop offset="0%" stop-color="#1e3a8a"/>
            <stop offset="55%" stop-color="#1d4ed8"/>
            <stop offset="100%" stop-color="#0c1233"/>
          </radialGradient>
          <radialGradient id="shG" cx="30%" cy="25%" r="42%">
            <stop offset="0%" stop-color="#fff" stop-opacity=".18"/>
            <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
          </radialGradient>
          <linearGradient id="r1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#3b82f6" stop-opacity=".8"/>
            <stop offset="50%" stop-color="#22d3ee" stop-opacity=".35"/>
            <stop offset="100%" stop-color="#3b82f6" stop-opacity=".8"/>
          </linearGradient>
          <linearGradient id="r2" x1="100%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#818cf8" stop-opacity=".7"/>
            <stop offset="50%" stop-color="#3b82f6" stop-opacity=".25"/>
            <stop offset="100%" stop-color="#818cf8" stop-opacity=".7"/>
          </linearGradient>
          <linearGradient id="r3" x1="0%" y1="100%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#22d3ee" stop-opacity=".55"/>
            <stop offset="100%" stop-color="#3b82f6" stop-opacity=".15"/>
          </linearGradient>
        </defs>

        <circle cx="160" cy="160" r="150" fill="url(#glG)"/>

        <g class="gRA">
          <ellipse cx="160" cy="160" rx="138" ry="36" fill="none" stroke="url(#r1)" stroke-width="1.2" opacity=".75"/>
        </g>
        <g class="gRB">
          <ellipse cx="160" cy="160" rx="128" ry="32" fill="none" stroke="url(#r2)" stroke-width="1" opacity=".6" transform="rotate(65 160 160)"/>
        </g>
        <g class="gRC">
          <ellipse cx="160" cy="160" rx="142" ry="24" fill="none" stroke="url(#r3)" stroke-width=".8" opacity=".5" transform="rotate(-42 160 160)"/>
        </g>

        <g class="gCore">
          <circle cx="160" cy="160" r="92" fill="url(#gbG)"/>
          <ellipse cx="160" cy="160" rx="92" ry="24" fill="none" stroke="#3b82f6" stroke-width=".55" opacity=".32"/>
          <ellipse cx="160" cy="136" rx="78" ry="17" fill="none" stroke="#3b82f6" stroke-width=".45" opacity=".2"/>
          <ellipse cx="160" cy="184" rx="78" ry="17" fill="none" stroke="#3b82f6" stroke-width=".45" opacity=".2"/>
          <ellipse cx="160" cy="160" rx="26" ry="92" fill="none" stroke="#3b82f6" stroke-width=".55" opacity=".28"/>
          <ellipse cx="160" cy="160" rx="64" ry="92" fill="none" stroke="#3b82f6" stroke-width=".45" opacity=".18"/>
          <circle cx="160" cy="160" r="92" fill="url(#shG)"/>
          <circle cx="160" cy="160" r="92" fill="none" stroke="#3b82f6" stroke-width="1.1" opacity=".45"/>
          <g fill="#60a5fa" opacity=".52">
            <ellipse cx="142" cy="146" rx="18" ry="12"/>
            <ellipse cx="170" cy="140" rx="13" ry="8"/>
            <ellipse cx="130" cy="168" rx="10" ry="7" opacity=".45"/>
            <ellipse cx="182" cy="165" rx="15" ry="9"/>
            <ellipse cx="155" cy="180" rx="8"  ry="5" opacity=".4"/>
            <ellipse cx="192" cy="150" rx="8"  ry="6" opacity=".48"/>
          </g>
        </g>

        <g class="gDA">
          <circle cx="160" cy="22"  r="5" fill="#3b82f6"/>
          <circle cx="160" cy="22"  r="9" fill="#3b82f6" opacity=".2"/>
        </g>
        <g class="gDB">
          <circle cx="298" cy="160" r="4" fill="#22d3ee"/>
          <circle cx="298" cy="160" r="7" fill="#22d3ee" opacity=".2"/>
        </g>
        <g class="gDC">
          <circle cx="160" cy="298" r="3.5" fill="#818cf8"/>
          <circle cx="160" cy="298" r="7"   fill="#818cf8" opacity=".2"/>
        </g>

        <line class="gSig" x1="226" y1="106" x2="270" y2="65"  stroke="#22d3ee" stroke-width=".8" opacity=".5"/>
        <circle cx="270" cy="65"  r="3" fill="#22d3ee" opacity=".7"/>
        <line class="gSig" x1="96"  y1="118" x2="52"  y2="78"  stroke="#3b82f6" stroke-width=".8" opacity=".45"/>
        <circle cx="52"  cy="78"  r="3" fill="#3b82f6" opacity=".65"/>
        <line class="gSig" x1="210" y1="212" x2="252" y2="255" stroke="#818cf8" stroke-width=".8" opacity=".4"/>
        <circle cx="252" cy="255" r="3" fill="#818cf8" opacity=".6"/>

        <g transform="translate(160 160)" fill="none" stroke="#93c5fd" stroke-width="1.3" opacity=".85">
          <circle r="10"/>
          <path d="M-10 0h20M0-10c3 3 3 7 0 10M0-10c-3 3-3 7 0 10"/>
        </g>
        <text x="160" y="300" text-anchor="middle" font-size="10" fill="#3b82f6" opacity=".55" font-family="'Orbitron',sans-serif" letter-spacing="3">GLOBAL VOICE</text>
      </svg>
    </div>
  </div>

  <!-- CENTER — Login card -->
  <div class="login-wrap">
    <div class="login-card">

      <div class="card-top">
        <div class="card-logo-circle">
          <svg class="icon" aria-hidden="true"><use href="#icon-globe"/></svg>
        </div>
        <span class="card-brand">GLOBAL VOICE</span>
        <div class="card-online">
          <div class="online-dot"></div>
          <span>Online</span>
        </div>
      </div>

      <h1 class="card-title">Tizimga kirish</h1>
      <p class="card-sub">Login va parolingizni kiriting</p>

      <div class="alert-box" id="alertBox" role="alert">
        <svg class="icon" aria-hidden="true"><use href="#icon-alert"/></svg>
        <span id="alertMsg">Login yoki parol noto'g'ri.</span>
      </div>

      @if(session('error'))
        <div class="alert-box" style="display:flex">
          <svg class="icon" aria-hidden="true"><use href="#icon-alert"/></svg>
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login_post') }}" id="loginForm" novalidate>
        @csrf

        <label class="field-lbl" for="loginInput">Login</label>
        <div class="field-wrap">
          <svg class="icon fi" aria-hidden="true"><use href="#icon-user"/></svg>
          <input class="field-input @error('login') err @enderror"
            type="text" id="loginInput" name="login"
            value="{{ old('login') }}"
            placeholder="loginni kiriting"
            autocomplete="username" required>
        </div>
        @error('login')
          <p class="err-msg" style="display:block">{{ $message }}</p>
        @enderror

        <label class="field-lbl" for="passwordInput">Parol</label>
        <div class="field-wrap">
          <svg class="icon fi" aria-hidden="true"><use href="#icon-lock"/></svg>
          <input class="field-input @error('password') err @enderror"
            type="password" id="passwordInput" name="password"
            placeholder="parolni kiriting"
            autocomplete="current-password" required>
          <button type="button" class="eye-btn" id="eyeBtn" aria-label="Parolni ko'rsatish">
            <svg class="icon" aria-hidden="true"><use id="eyeIcon" href="#icon-eye"/></svg>
          </button>
        </div>
        @error('password')
          <p class="err-msg" style="display:block">{{ $message }}</p>
        @enderror

        <div class="remember-row">
          <input type="checkbox" id="remember" name="remember" {{ old('remember', true) ? 'checked' : '' }}>
          <label class="remember-lbl" for="remember">Eslab qolish</label>
        </div>

        <button type="submit" class="login-btn" id="loginBtn">
          <span class="btn-text">
            <svg class="icon" aria-hidden="true"><use href="#icon-login"/></svg>
            Kirish
          </span>
          <div class="btn-spinner"></div>
        </button>
      </form>

      <div class="sep">
        <div class="sep-line"></div>
        <span class="sep-txt">SSL bilan himoyalangan</span>
        <div class="sep-line"></div>
      </div>

      <div class="sec-badge">
        <div class="online-dot"></div>
        <svg class="icon" aria-hidden="true" style="font-size:14px;color:#22c55e"><use href="#icon-shield"/></svg>
        <span class="sec-txt">Xavfsiz ulanish faol</span>
      </div>

    </div>
  </div>

</div>

<script>
(function(){
  var welcome=document.getElementById('welcome');
  var page=document.getElementById('page');
  var skipWelcomeAfterLogout=@json(request()->boolean('logged_out'));

  if(skipWelcomeAfterLogout){
    welcome.style.display='none';
    page.classList.add('on');
    return;
  }

  setTimeout(function(){
    welcome.classList.add('hide');
    setTimeout(function(){welcome.style.display='none';page.classList.add('on')},250);
  },2000);
})();

var themeStorageKey='global-voice-theme';
var themeIcon=document.getElementById('themeIcon');
function setTheme(isLight){
  document.body.classList.toggle('light',isLight);
  themeIcon.setAttribute('href',isLight?'#icon-sun':'#icon-moon');
  try{localStorage.setItem(themeStorageKey,isLight?'light':'dark')}catch(e){}
}
var savedTheme='';
try{savedTheme=localStorage.getItem(themeStorageKey)||''}catch(e){}
setTheme(savedTheme==='light');
document.getElementById('themeBtn').addEventListener('click',function(){
  setTheme(!document.body.classList.contains('light'));
});

var pwdInput=document.getElementById('passwordInput');
var eyeIcon=document.getElementById('eyeIcon');
document.getElementById('eyeBtn').addEventListener('click',function(){
  var show=pwdInput.type==='text';
  pwdInput.type=show?'password':'text';
  eyeIcon.setAttribute('href',show?'#icon-eye':'#icon-eye-off');
});

var form=document.getElementById('loginForm');
var loginBtn=document.getElementById('loginBtn');
form.addEventListener('submit',function(e){
  var l=document.getElementById('loginInput').value.trim();
  var p=pwdInput.value.trim();
  if(!l||!p){
    e.preventDefault();
    var ab=document.getElementById('alertBox');
    document.getElementById('alertMsg').textContent="Login va parolni to'ldiring.";
    ab.style.display='flex';
    return;
  }
  // Kabinet layouti ushbu qiymatdan tema tanlaydi. Login ekranidagi
  // tema qanday bo'lishidan qat'i nazar, tizim light mode'da ochiladi.
  try{localStorage.setItem('theme','semi')}catch(e){}
  loginBtn.classList.add('loading');
  loginBtn.disabled=true;
});

document.querySelectorAll('.field-input').forEach(function(inp){
  inp.addEventListener('input',function(){
    inp.classList.remove('err');
    document.getElementById('alertBox').style.display='none';
  });
});
</script>
</body>
</html>
