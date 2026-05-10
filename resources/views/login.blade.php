<!doctype html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Global Voice — Kirish</title>
<link rel="icon" href="{{ url('logo.png') }}" type="image/png"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --blue:#3b82f6;
  --blue-d:#1d4ed8;
  --cyan:#22d3ee;
  --indigo:#818cf8;
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
#welcome.hide{animation:wOut .7s ease forwards}
@keyframes wOut{to{opacity:0;pointer-events:none}}

.w-bg-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(59,130,246,.06) 1px,transparent 1px),
    linear-gradient(90deg,rgba(59,130,246,.06) 1px,transparent 1px);
  background-size:52px 52px;
}
.w-orb{position:absolute;border-radius:50%;pointer-events:none}
.w-orb1{
  width:460px;height:460px;top:-140px;left:-120px;
  background:radial-gradient(circle,rgba(59,130,246,.2),transparent 68%);
  animation:wDrift1 7s ease-in-out infinite;
}
.w-orb2{
  width:360px;height:360px;bottom:-80px;right:-80px;
  background:radial-gradient(circle,rgba(34,211,238,.16),transparent 68%);
  animation:wDrift2 9s ease-in-out infinite;
}
@keyframes wDrift1{0%,100%{transform:translate(0,0)}50%{transform:translate(22px,28px)}}
@keyframes wDrift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-18px,-22px)}}

.w-body{position:relative;z-index:2;text-align:center}
.w-ring-wrap{position:relative;width:100px;height:100px;margin:0 auto 28px}
.w-ring{
  position:absolute;inset:0;border-radius:50%;
  border:1.5px solid rgba(59,130,246,.35);
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
  animation:wLogoIn .5s cubic-bezier(.34,1.56,.64,1) .3s both;
}
@keyframes wLogoIn{from{transform:scale(.4);opacity:0}to{transform:scale(1);opacity:1}}

.w-title{
  font-family:'Orbitron',sans-serif;
  font-size:clamp(2rem,6vw,3.4rem);
  font-weight:700;color:#fff;letter-spacing:4px;
  opacity:0;animation:wFadeUp .7s ease .8s forwards;
}
.w-title span{color:var(--blue)}
.w-sub{
  font-size:15px;color:var(--muted);margin-top:10px;
  opacity:0;animation:wFadeUp .6s ease 1.2s forwards;
}
.w-progress{
  width:180px;height:2px;background:var(--border);
  border-radius:2px;margin:28px auto 0;overflow:hidden;
  opacity:0;animation:wFadeUp .5s ease 1.4s forwards;
}
.w-bar{
  height:100%;width:0;
  background:linear-gradient(90deg,var(--blue),var(--cyan));
  animation:wBarFill 1.2s ease 1.6s forwards;
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
  opacity:0;transition:opacity .9s ease;
}
#page.on{opacity:1}

/* ===== BACKGROUND ===== */
.bg-wrap{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}
.bg-grid{
  position:absolute;inset:0;
  background-image:
    linear-gradient(rgba(59,130,246,.045) 1px,transparent 1px),
    linear-gradient(90deg,rgba(59,130,246,.045) 1px,transparent 1px);
  background-size:52px 52px;
}
.bg-orb{position:absolute;border-radius:50%}
.bg-orb1{
  width:600px;height:600px;top:-180px;left:-160px;
  background:radial-gradient(circle,rgba(59,130,246,.16),transparent 65%);
  animation:bgDA 14s ease-in-out infinite;
}
.bg-orb2{
  width:500px;height:500px;bottom:-120px;right:-100px;
  background:radial-gradient(circle,rgba(34,211,238,.12),transparent 65%);
  animation:bgDB 18s ease-in-out infinite;
}
.bg-orb3{
  width:340px;height:340px;top:50%;left:50%;
  transform:translate(-50%,-50%);
  background:radial-gradient(circle,rgba(129,140,248,.1),transparent 68%);
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
.globe-wrap{width:min(400px,90%);height:min(400px,90%);flex-shrink:0}
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

/* ===== RIGHT — Stat cards ===== */
.side-right{
  position:fixed;right:0;top:0;bottom:0;
  width:calc(50% - 215px);
  z-index:1;pointer-events:none;
  display:flex;align-items:center;justify-content:flex-start;
  padding-left:30px;overflow:hidden;
}
.cards-wrap{position:relative;width:min(240px,90%);height:min(380px,90%)}

.float-card{
  position:absolute;
  background:rgba(15,25,50,.7);
  border:1px solid rgba(59,130,246,.18);
  border-radius:14px;
  padding:14px 16px;
  animation:fcFloat 6s ease-in-out infinite;
  width:210px;
}
.float-card:nth-child(1){top:4%;  left:0; animation-delay:0s;   animation-duration:6s}
.float-card:nth-child(2){top:36%; left:14px; animation-delay:1.8s; animation-duration:7.5s}
.float-card:nth-child(3){top:68%; left:0; animation-delay:3.4s; animation-duration:5.8s}
@keyframes fcFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-13px)}}

.fc-lbl{font-size:10px;color:var(--muted);letter-spacing:.6px;text-transform:uppercase;margin-bottom:6px}
.fc-val{font-size:19px;font-weight:600;color:var(--blue)}
.fc-sub{font-size:11px;color:var(--dim);margin-top:2px}
.fc-bars{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.fc-row-lbl{display:flex;justify-content:space-between;font-size:10px;color:var(--dim);margin-bottom:2px}
.fc-bar-bg{height:4px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden}
.fc-bar-f{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--blue),var(--cyan))}

/* floating dots */
.pdot{
  position:absolute;width:5px;height:5px;border-radius:50%;
  opacity:.35;animation:pdotF 8s ease-in-out infinite;
}
@keyframes pdotF{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-18px) scale(1.4)}}

/* ===== CENTER — Login card ===== */
.login-wrap{position:relative;z-index:10;width:420px;max-width:92vw}

.login-card{
  background:rgba(10,18,36,.84);
  backdrop-filter:blur(24px);
  border:1px solid rgba(59,130,246,.22);
  border-radius:22px;
  padding:2.6rem 2.2rem;
  box-shadow:
    0 0 0 1px rgba(59,130,246,.07) inset,
    0 32px 80px rgba(0,0,0,.55),
    0 0 60px rgba(59,130,246,.07);
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
  border-color:rgba(59,130,246,.55);
  background:rgba(59,130,246,.07);
  box-shadow:0 0 0 3px rgba(59,130,246,.1);
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
.login-btn:hover{opacity:.88;transform:translateY(-1px);box-shadow:0 8px 28px rgba(59,130,246,.35)}
.login-btn:active{transform:scale(.98)}
.btn-spinner{
  display:none;width:16px;height:16px;
  border:2px solid rgba(255,255,255,.3);border-top-color:#fff;
  border-radius:50%;animation:spin .7s linear infinite;
}
.login-btn.loading .btn-text{display:none}
.login-btn.loading .btn-spinner{display:block}
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

/* Light mode */
body.light{background:#eef2ff}
body.light .login-card{
  background:rgba(255,255,255,.93);
  border-color:rgba(59,130,246,.22);
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
body.light .float-card{background:rgba(255,255,255,.7);border-color:rgba(59,130,246,.2)}
body.light .fc-lbl{color:#94a3b8}
body.light .fc-sub{color:#94a3b8}

/* ===== RESPONSIVE ===== */

/* Tablet — yon animatsiyalar yashirinadi */
@media(max-width:960px){
  .side-left,.side-right{display:none}
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
      <div class="w-logo"><i class="ti ti-globe" aria-hidden="true"></i></div>
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
    <i class="ti ti-moon" id="themeIcon" aria-hidden="true"></i>
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

        <text x="160" y="167" text-anchor="middle" font-size="22" fill="#93c5fd" opacity=".85" font-family="sans-serif">⊕</text>
        <text x="160" y="300" text-anchor="middle" font-size="10" fill="#3b82f6" opacity=".55" font-family="'Orbitron',sans-serif" letter-spacing="3">GLOBAL VOICE</text>
      </svg>
    </div>
  </div>

  <!-- RIGHT — Floating stat cards -->
  <div class="side-right" aria-hidden="true">
    <div class="cards-wrap">

      <div class="float-card">
        <div class="fc-lbl">Foydalanuvchilar</div>
        <div class="fc-val">2,418</div>
        <div class="fc-sub">&#8593; 12% bu oy</div>
      </div>

      <div class="float-card">
        <div class="fc-lbl">Faollik darajasi</div>
        <div class="fc-bars">
          <div class="fc-row-lbl"><span>Savol</span><span>87%</span></div>
          <div class="fc-bar-bg"><div class="fc-bar-f" style="width:87%"></div></div>
          <div class="fc-row-lbl"><span>Yechim</span><span>94%</span></div>
          <div class="fc-bar-bg"><div class="fc-bar-f" style="width:94%;background:linear-gradient(90deg,#818cf8,#22d3ee)"></div></div>
        </div>
      </div>

      <div class="float-card" style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:50%;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);display:flex;align-items:center;justify-content:center;font-size:16px;color:#22c55e;flex-shrink:0">
          <i class="ti ti-shield-check" aria-hidden="true"></i>
        </div>
        <div>
          <div class="fc-lbl" style="margin-bottom:2px">Tizim holati</div>
          <div style="font-size:13px;font-weight:600;color:#22c55e">Online — 99.9%</div>
          <div class="fc-sub">SSL • Xavfsiz</div>
        </div>
      </div>

      <!-- floating dots -->
      <div class="pdot" style="top:5%;left:70%;background:#22d3ee;animation-delay:0s"></div>
      <div class="pdot" style="top:22%;left:85%;background:#3b82f6;animation-delay:1.3s"></div>
      <div class="pdot" style="top:52%;left:78%;background:#818cf8;animation-delay:2.7s"></div>
      <div class="pdot" style="top:78%;left:68%;background:#22d3ee;animation-delay:4s"></div>
      <div class="pdot" style="top:90%;left:82%;background:#3b82f6;animation-delay:5.2s"></div>
    </div>
  </div>

  <!-- CENTER — Login card -->
  <div class="login-wrap">
    <div class="login-card">

      <div class="card-top">
        <div class="card-logo-circle">
          <i class="ti ti-globe" aria-hidden="true"></i>
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
        <i class="ti ti-alert-circle" aria-hidden="true"></i>
        <span id="alertMsg">Login yoki parol noto'g'ri.</span>
      </div>

      @if(session('error'))
        <div class="alert-box" style="display:flex">
          <i class="ti ti-alert-circle" aria-hidden="true"></i>
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login_post') }}" id="loginForm" novalidate>
        @csrf

        <label class="field-lbl" for="loginInput">Login</label>
        <div class="field-wrap">
          <i class="ti ti-user fi" aria-hidden="true"></i>
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
          <i class="ti ti-lock fi" aria-hidden="true"></i>
          <input class="field-input @error('password') err @enderror"
            type="password" id="passwordInput" name="password"
            placeholder="parolni kiriting"
            autocomplete="current-password" required>
          <button type="button" class="eye-btn" id="eyeBtn" aria-label="Parolni ko'rsatish">
            <i class="ti ti-eye" id="eyeIcon" aria-hidden="true"></i>
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
            <i class="ti ti-login" aria-hidden="true"></i>
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
        <i class="ti ti-shield-check" aria-hidden="true" style="font-size:14px;color:#22c55e"></i>
        <span class="sec-txt">Xavfsiz ulanish faol</span>
      </div>

    </div>
  </div>

</div>

<script>
(function(){
  var w=document.getElementById('welcome');
  var p=document.getElementById('page');
  setTimeout(function(){
    w.classList.add('hide');
    setTimeout(function(){w.style.display='none';p.classList.add('on')},700);
  },2000);
})();

var isLight=false;
document.getElementById('themeBtn').addEventListener('click',function(){
  isLight=!isLight;
  document.body.classList.toggle('light',isLight);
  document.getElementById('themeIcon').className=isLight?'ti ti-sun':'ti ti-moon';
});

var pwdInput=document.getElementById('passwordInput');
var eyeIcon=document.getElementById('eyeIcon');
document.getElementById('eyeBtn').addEventListener('click',function(){
  var show=pwdInput.type==='text';
  pwdInput.type=show?'password':'text';
  eyeIcon.className=show?'ti ti-eye':'ti ti-eye-off';
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
