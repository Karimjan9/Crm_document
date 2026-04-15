<header>
  @php
    $authUser = auth()->user();
    $primaryRole = optional($authUser->roles->first())->name ?? 'user';
    $filialName = optional($authUser->filial)->name ?? 'Biriktirilmagan';
    $weatherCity = $authUser->setting('weather_city', 'Bukhara');
    $reducedMotion = (bool) $authUser->setting('reduced_motion', false);
    $canExcelExport = $authUser->hasRole('super_admin');
  @endphp

  <style>
  * { box-sizing: border-box !important; }

  .topbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background-color: #15172a !important;
    padding: 10px 25px !important;
    width: 100% !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    z-index: 1000 !important;
    border-bottom: 1px solid #0c0f0f33 !important;
    flex-wrap: wrap !important;
  }

  .navbar {
    display: flex !important;
    align-items: flex-end !important;
    justify-content: space-between !important;
    width: 100% !important;
  }

  .brand-animated {
    font-family: 'Poppins', sans-serif !important;
    font-size: 30px !important;
    font-weight: 700 !important;
    color: #00d1ff !important;
    transition: color 0.5s ease !important;
  }

  .weather-date {
    position: absolute !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    display: flex !important;
    align-items: center !important;
    gap: 20px !important;
    color: #fff !important;
    font-size: 18px !important;
    font-weight: 600 !important;
  }

  .weather-date i {
    color: #00d1ff !important;
    font-size: 22px !important;
    margin-right: 6px !important;
  }

  .user-box {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    cursor: pointer !important;
    transition: transform 0.2s ease, color 0.2s ease !important;
  }

  .topbar-actions {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
  }

  .topbar-shortcut {
    border: 1px solid rgba(0, 209, 255, 0.24);
    background: rgba(255,255,255,0.06);
    color: #e8f6ff;
    border-radius: 14px;
    padding: 10px 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .topbar-shortcut i {
    font-size: 18px;
    color: #00d1ff;
  }

  .topbar-shortcut:hover {
    transform: translateY(-1px);
    background: rgba(0, 209, 255, 0.12);
    border-color: rgba(0, 209, 255, 0.48);
    box-shadow: 0 12px 24px rgba(0, 209, 255, 0.12);
  }

  .deadline-bell {
    position: relative;
  }

  .deadline-bell__trigger {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(121, 186, 255, 0.22);
    background: linear-gradient(145deg, rgba(18, 35, 68, 0.94), rgba(9, 18, 38, 0.98));
    color: #eef7ff;
    border-radius: 20px;
    min-width: 188px;
    padding: 10px 14px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    box-shadow: 0 18px 36px rgba(6, 13, 29, 0.24);
    transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease, background 0.24s ease;
  }

  .deadline-bell__trigger::before {
    content: "";
    position: absolute;
    inset: 1px;
    border-radius: 19px;
    background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0));
    pointer-events: none;
  }

  .deadline-bell__trigger:hover,
  .deadline-bell.is-open .deadline-bell__trigger {
    transform: translateY(-2px);
    border-color: rgba(0, 209, 255, 0.42);
    box-shadow: 0 22px 44px rgba(0, 209, 255, 0.14);
  }

  .deadline-bell__orb {
    position: absolute;
    width: 76px;
    height: 76px;
    top: -26px;
    left: -18px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0, 209, 255, 0.38), rgba(0, 209, 255, 0) 68%);
    animation: bellOrbDrift 5s ease-in-out infinite;
    pointer-events: none;
  }

  .deadline-bell__icon-wrap {
    position: relative;
    z-index: 1;
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.06));
    border: 1px solid rgba(255,255,255,0.14);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.10);
  }

  .deadline-bell__icon {
    font-size: 22px;
    color: #f7fbff;
    animation: bellFloat 2.9s ease-in-out infinite;
    transform-origin: top center;
  }

  .deadline-bell__copy {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    text-align: left;
    gap: 2px;
  }

  .deadline-bell__eyebrow {
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(189, 221, 255, 0.72);
  }

  .deadline-bell__summary {
    font-size: 14px;
    line-height: 1.2;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
  }

  .deadline-bell__badge {
    position: relative;
    z-index: 1;
    min-width: 32px;
    padding: 6px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.10);
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    text-align: center;
    color: #ffffff;
    margin-left: auto;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
  }

  .deadline-bell.is-critical .deadline-bell__trigger {
    border-color: rgba(255, 115, 138, 0.34);
    background: linear-gradient(145deg, rgba(61, 24, 49, 0.95), rgba(13, 18, 39, 0.98));
    box-shadow: 0 22px 44px rgba(255, 99, 132, 0.16);
  }

  .deadline-bell.is-critical .deadline-bell__icon {
    animation: bellRing 2.7s ease-in-out infinite;
  }

  .deadline-bell.is-critical .deadline-bell__badge {
    background: linear-gradient(135deg, rgba(255, 121, 141, 0.88), rgba(255, 164, 86, 0.92));
    border-color: rgba(255,255,255,0.10);
    animation: bellBadgePulse 2.2s ease-in-out infinite;
  }

  .deadline-bell__panel {
    position: absolute;
    top: calc(100% + 16px);
    right: 0;
    width: min(430px, calc(100vw - 24px));
    border-radius: 28px;
    background: linear-gradient(180deg, rgba(11, 21, 43, 0.98), rgba(4, 10, 25, 0.99));
    border: 1px solid rgba(116, 184, 255, 0.22);
    box-shadow: 0 28px 80px rgba(0, 0, 0, 0.44);
    backdrop-filter: blur(18px);
    padding: 20px;
    color: #edf7ff;
    z-index: 1400;
    overflow: hidden;
    animation: deadlinePanelIn 0.24s cubic-bezier(0.22, 1, 0.36, 1);
  }

  .deadline-bell__panel[hidden] {
    display: none !important;
  }

  .deadline-bell__panel-glow {
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at top right, rgba(0, 209, 255, 0.18), transparent 30%),
      radial-gradient(circle at bottom left, rgba(91, 106, 255, 0.16), transparent 26%);
    pointer-events: none;
  }

  .deadline-bell__panel-head,
  .deadline-bell__stats,
  .deadline-bell__list,
  .deadline-bell__footer,
  .deadline-bell__empty {
    position: relative;
    z-index: 1;
  }

  .deadline-bell__panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
  }

  .deadline-bell__panel-kicker {
    font-size: 11px;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #8edfff;
    margin-bottom: 8px;
  }

  .deadline-bell__panel-head h3 {
    margin: 0;
    font-size: 24px;
    line-height: 1.1;
    color: #ffffff;
  }

  .deadline-bell__panel-head p {
    margin: 8px 0 0;
    color: rgba(223, 238, 255, 0.72);
    font-size: 13px;
    line-height: 1.55;
    max-width: 290px;
  }

  .deadline-bell__close {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    color: #d8ecff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.22s ease, background 0.22s ease, border-color 0.22s ease;
  }

  .deadline-bell__close:hover {
    transform: rotate(90deg);
    background: rgba(255,255,255,0.12);
    border-color: rgba(255,255,255,0.22);
  }

  .deadline-bell__stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
  }

  .deadline-bell__stat {
    padding: 13px 14px;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.05);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
  }

  .deadline-bell__stat-label {
    display: block;
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(205, 226, 255, 0.68);
    margin-bottom: 8px;
  }

  .deadline-bell__stat strong {
    display: block;
    font-size: 22px;
    line-height: 1;
    color: #ffffff;
  }

  .deadline-bell__stat.is-overdue {
    background: linear-gradient(180deg, rgba(255, 107, 136, 0.14), rgba(255, 107, 136, 0.06));
    border-color: rgba(255, 107, 136, 0.20);
  }

  .deadline-bell__stat.is-today {
    background: linear-gradient(180deg, rgba(255, 192, 83, 0.16), rgba(255, 192, 83, 0.06));
    border-color: rgba(255, 192, 83, 0.20);
  }

  .deadline-bell__list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: min(440px, 58vh);
    overflow-y: auto;
    margin-right: -6px;
    padding-right: 6px;
  }

  .deadline-bell__list::-webkit-scrollbar {
    width: 8px;
  }

  .deadline-bell__list::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: rgba(136, 193, 255, 0.28);
  }

  .deadline-bell__item {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 14px;
    padding: 14px 16px 14px 20px;
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,0.08);
    background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
    text-decoration: none;
    color: inherit;
    overflow: hidden;
    transition: transform 0.22s ease, border-color 0.22s ease, background 0.22s ease, box-shadow 0.22s ease;
  }

  .deadline-bell__item:hover {
    transform: translateY(-2px);
    border-color: rgba(124, 196, 255, 0.20);
    background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
    box-shadow: 0 18px 32px rgba(2, 8, 24, 0.28);
  }

  .deadline-bell__item-rail {
    position: absolute;
    left: 0;
    top: 14px;
    bottom: 14px;
    width: 4px;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(0, 209, 255, 0.88), rgba(91, 106, 255, 0.90));
  }

  .deadline-bell__item-main {
    min-width: 0;
  }

  .deadline-bell__item-top {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 8px;
  }

  .deadline-bell__code,
  .deadline-bell__flag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    min-height: 24px;
    padding: 4px 10px;
    font-size: 11px;
    line-height: 1;
    font-weight: 700;
  }

  .deadline-bell__code {
    color: #d9efff;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.10);
  }

  .deadline-bell__flag {
    color: #7b4102;
    background: rgba(255, 204, 122, 0.92);
  }

  .deadline-bell__item h4 {
    margin: 0;
    font-size: 16px;
    line-height: 1.25;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .deadline-bell__item p {
    margin: 4px 0 0;
    font-size: 13px;
    line-height: 1.45;
    color: rgba(228, 241, 255, 0.84);
  }

  .deadline-bell__meta {
    margin-top: 8px;
    font-size: 12px;
    line-height: 1.55;
    color: rgba(189, 215, 242, 0.72);
  }

  .deadline-bell__item-side {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: 6px;
    min-width: 104px;
    text-align: right;
  }

  .deadline-bell__due {
    font-size: 13px;
    font-weight: 800;
    color: #88e6ff;
  }

  .deadline-bell__date,
  .deadline-bell__remaining {
    font-size: 12px;
    line-height: 1.4;
    color: rgba(210, 230, 255, 0.74);
  }

  .deadline-bell__item.urgency-overdue .deadline-bell__item-rail {
    background: linear-gradient(180deg, #ff7993, #ffb36a);
  }

  .deadline-bell__item.urgency-overdue .deadline-bell__due {
    color: #ff9bae;
  }

  .deadline-bell__item.urgency-today .deadline-bell__item-rail {
    background: linear-gradient(180deg, #ffca67, #ff8b54);
  }

  .deadline-bell__item.urgency-today .deadline-bell__due {
    color: #ffd384;
  }

  .deadline-bell__item.urgency-soon .deadline-bell__item-rail {
    background: linear-gradient(180deg, #84f0ff, #4a8dff);
  }

  .deadline-bell__empty {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,0.08);
    background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.03));
    margin-bottom: 14px;
  }

  .deadline-bell__empty-icon {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(78, 223, 160, 0.28), rgba(0, 209, 255, 0.24));
    border: 1px solid rgba(131, 245, 202, 0.20);
    color: #aaffd9;
    font-size: 24px;
  }

  .deadline-bell__empty strong {
    display: block;
    font-size: 16px;
    color: #ffffff;
    margin-bottom: 6px;
  }

  .deadline-bell__empty p {
    margin: 0;
    font-size: 13px;
    line-height: 1.55;
    color: rgba(215, 232, 252, 0.76);
  }

  .deadline-bell__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 14px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
    flex-wrap: wrap;
  }

  .deadline-bell__footer-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #8fe6ff;
    font-size: 13px;
    font-weight: 700;
  }

  .deadline-bell__footer-link i {
    font-size: 18px;
  }

  .deadline-bell__footer-note {
    font-size: 12px;
    line-height: 1.5;
    color: rgba(204, 224, 248, 0.68);
  }

  @keyframes bellFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-2px); }
  }

  @keyframes bellRing {
    0%, 100% { transform: rotate(0deg); }
    8% { transform: rotate(18deg); }
    16% { transform: rotate(-16deg); }
    24% { transform: rotate(11deg); }
    32% { transform: rotate(-8deg); }
    40% { transform: rotate(4deg); }
  }

  @keyframes bellOrbDrift {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
    50% { transform: translate3d(6px, -4px, 0) scale(1.06); }
  }

  @keyframes bellBadgePulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 121, 141, 0.28); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(255, 121, 141, 0); }
  }

  @keyframes deadlinePanelIn {
    from {
      opacity: 0;
      transform: translateY(-10px) scale(0.97);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .user-box:hover { transform: scale(1.03) !important; }

  .user-box img {
    width: 45px !important;
    height: 45px !important;
    border-radius: 50% !important;
    border: 2px solid #00d1ff !important;
    object-fit: cover !important;
  }

  .user-info .user-name {
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 16px !important;
    margin: 0 !important;
  }

  .profile-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(8, 15, 31, 0.58);
    backdrop-filter: blur(10px);
    justify-content: center;
    align-items: center;
    z-index: 2000;
    animation: fadeIn 0.25s ease;
    padding: 18px;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .profile-content {
    background: linear-gradient(180deg, rgba(15, 24, 47, 0.96), rgba(9, 18, 37, 0.94));
    border: 1px solid rgba(63, 196, 255, 0.22);
    border-radius: 28px;
    padding: 28px;
    color: #fff;
    width: 100%;
    max-width: 660px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.42);
    animation: scaleUp 0.25s ease;
    position: relative;
    overflow: hidden;
  }

  .profile-content::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at top right, rgba(0, 209, 255, 0.14), transparent 32%),
      radial-gradient(circle at bottom left, rgba(62, 114, 255, 0.14), transparent 28%);
    pointer-events: none;
  }

  @keyframes scaleUp {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }

  .profile-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.14);
    background: rgba(255,255,255,0.06);
    color: #d8ecff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 1;
  }

  .profile-close:hover {
    background: rgba(255,255,255,0.12);
    transform: rotate(90deg);
  }

  .profile-header {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 18px;
    text-align: left;
    margin-bottom: 22px;
    padding-right: 56px;
  }

  .profile-header img {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid #00d1ff;
    object-fit: cover;
    box-shadow: 0 10px 30px rgba(0, 209, 255, 0.18);
  }

  .profile-title {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: #7edbff;
    margin-bottom: 6px;
  }

  .profile-content h3 {
    margin: 0;
    font-size: 28px;
    color: #fff;
    font-weight: 700;
  }

  .profile-content p {
    margin: 8px 0 0;
    font-size: 14px;
    color: rgba(221, 235, 255, 0.8);
  }

  .profile-badges {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 22px;
  }

  .profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.09);
    color: #ddecff;
    font-size: 13px;
    font-weight: 600;
  }

  .profile-badge i {
    color: #00d1ff;
    font-size: 16px;
  }

  .modal-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  .modal-btn {
    text-decoration: none;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 18px;
    padding: 16px 18px;
    color: #fff;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.24s ease;
    text-align: left;
    min-height: 76px;
  }

  .modal-btn i {
    font-size: 20px;
    color: #00d1ff;
    transition: all 0.24s ease;
    flex-shrink: 0;
  }

  .modal-btn strong {
    display: block;
    font-size: 15px;
    color: #fff;
    margin-bottom: 4px;
  }

  .modal-btn span {
    display: block;
    font-size: 12px;
    color: rgba(220, 234, 255, 0.72);
    line-height: 1.45;
  }

  .modal-btn:hover,
  .modal-btn.is-active {
    transform: translateY(-2px);
    border-color: rgba(0, 209, 255, 0.45);
    background: linear-gradient(135deg, rgba(0, 209, 255, 0.16), rgba(62, 114, 255, 0.12));
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
    color: #fff;
  }

  .modal-btn:hover i,
  .modal-btn.is-active i {
    color: #9deaff;
  }

  .modal-btn-danger:hover {
    border-color: rgba(255, 99, 132, 0.5);
    background: linear-gradient(135deg, rgba(255, 99, 132, 0.18), rgba(255, 87, 87, 0.15));
  }

  .profile-panel-wrap {
    position: relative;
    z-index: 1;
    margin-top: 18px;
  }

  .profile-panel {
    display: none;
    border-radius: 20px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 18px;
  }

  .profile-panel.is-visible { display: block; }

  .profile-panel-title {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 14px;
  }

  .profile-panel-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .profile-stat {
    padding: 14px;
    border-radius: 16px;
    background: rgba(7, 17, 34, 0.48);
    border: 1px solid rgba(255,255,255,0.06);
  }

  .profile-stat-label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(174, 206, 236, 0.76);
    margin-bottom: 6px;
  }

  .profile-stat-value {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
    word-break: break-word;
  }

  .profile-panel-note {
    font-size: 13px;
    line-height: 1.6;
    color: rgba(221, 235, 255, 0.8);
    margin: 0;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: minmax(170px, 210px) 1fr;
    gap: 20px;
    align-items: start;
  }

  .avatar-editor {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
  }

  .avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(5, 12, 26, 0.55);
  }

  .avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .avatar-meta {
    text-align: center;
    color: rgba(221, 235, 255, 0.72);
    font-size: 12px;
    line-height: 1.5;
  }

  .crop-shell {
    width: 100%;
    display: none;
    flex-direction: column;
    gap: 12px;
    padding: 14px;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(7, 17, 34, 0.42);
  }

  .crop-shell.is-active { display: flex; }

  .crop-stage {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: 20px;
    background:
      linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
      linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
      linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.03) 75%),
      linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.03) 75%),
      rgba(5, 12, 26, 0.72);
    background-size: 24px 24px;
    background-position: 0 0, 0 12px, 12px -12px, -12px 0;
    cursor: grab;
    touch-action: none;
  }

  .crop-stage.is-dragging { cursor: grabbing; }

  .crop-stage img {
    position: absolute;
    top: 0;
    left: 0;
    user-select: none;
    -webkit-user-drag: none;
    transform-origin: top left;
    max-width: none;
  }

  .crop-stage::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 20px;
    box-shadow:
      inset 0 0 0 1px rgba(255,255,255,0.1),
      inset 0 0 0 999px rgba(3, 10, 22, 0.08);
    pointer-events: none;
  }

  .crop-grid {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
      linear-gradient(rgba(255,255,255,0.16) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.16) 1px, transparent 1px);
    background-size: 33.333% 33.333%;
    opacity: 0.5;
  }

  .crop-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .crop-toolbar .field-label {
    min-width: 64px;
  }

  .crop-slider {
    flex: 1;
    accent-color: #00d1ff;
  }

  .crop-reset {
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.04);
    color: #fff;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .crop-reset:hover {
    border-color: rgba(0, 209, 255, 0.32);
    background: rgba(0, 209, 255, 0.08);
  }

  .file-trigger {
    width: 100%;
    border: 1px dashed rgba(0, 209, 255, 0.35);
    background: rgba(255,255,255,0.04);
    color: #ddecff;
    border-radius: 14px;
    padding: 12px 14px;
    cursor: pointer;
    text-align: center;
    font-weight: 600;
    transition: all 0.22s ease;
  }

  .file-trigger:hover {
    border-color: rgba(0, 209, 255, 0.52);
    background: rgba(0, 209, 255, 0.08);
  }

  .hidden-input { display: none; }

  .account-fields,
  .readonly-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .field-group { display: flex; flex-direction: column; gap: 8px; }
  .field-group.is-full { grid-column: 1 / -1; }

  .field-label {
    color: #e8f5ff;
    font-size: 13px;
    font-weight: 600;
  }

  .field-input {
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(6, 15, 31, 0.72);
    color: #fff;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .field-input:focus {
    border-color: rgba(0, 209, 255, 0.6);
    box-shadow: 0 0 0 4px rgba(0, 209, 255, 0.12);
    background: rgba(6, 18, 36, 0.92);
  }

  .field-input.has-error {
    border-color: rgba(255, 99, 132, 0.72);
    box-shadow: 0 0 0 4px rgba(255, 99, 132, 0.12);
  }

  .field-input[readonly] {
    opacity: 0.72;
    cursor: default;
  }

  .field-error {
    min-height: 18px;
    color: #ff98a9;
    font-size: 12px;
    line-height: 1.4;
  }

  .field-help {
    color: rgba(221, 235, 255, 0.62);
    font-size: 12px;
    line-height: 1.4;
  }

  .readonly-card {
    border-radius: 16px;
    padding: 14px;
    background: rgba(7, 17, 34, 0.48);
    border: 1px solid rgba(255,255,255,0.06);
  }

  .readonly-label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(174, 206, 236, 0.76);
    margin-bottom: 6px;
  }

  .readonly-value {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    word-break: break-word;
  }

  .panel-feedback {
    display: none;
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
    border: 1px solid transparent;
  }

  .panel-feedback.is-visible { display: block; }

  .panel-feedback.is-success {
    background: rgba(34, 197, 94, 0.12);
    border-color: rgba(34, 197, 94, 0.28);
    color: #b7f3cb;
  }

  .panel-feedback.is-error {
    background: rgba(255, 99, 132, 0.12);
    border-color: rgba(255, 99, 132, 0.28);
    color: #ffd1dc;
  }

  .panel-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
  }

  .panel-submit {
    border: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, #00d1ff, #3578ff);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    padding: 12px 18px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    box-shadow: 0 12px 24px rgba(0, 209, 255, 0.14);
  }

  .panel-submit:hover { transform: translateY(-1px); }
  .panel-submit:disabled { opacity: 0.7; cursor: wait; }

  .settings-card {
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(7, 17, 34, 0.42);
    padding: 16px;
    margin-top: 8px;
  }

  .export-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 16px;
  }

  .export-card {
    text-decoration: none;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(7, 17, 34, 0.44);
    padding: 16px;
    color: #fff;
    transition: all 0.22s ease;
    min-height: 122px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .export-card:hover {
    transform: translateY(-2px);
    border-color: rgba(0, 209, 255, 0.42);
    background: linear-gradient(135deg, rgba(0, 209, 255, 0.14), rgba(62, 114, 255, 0.12));
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.18);
    color: #fff;
  }

  .export-card i {
    font-size: 24px;
    color: #00d1ff;
  }

  .export-card strong {
    font-size: 15px;
    color: #fff;
  }

  .export-card span {
    display: block;
    font-size: 12px;
    line-height: 1.6;
    color: rgba(221, 235, 255, 0.76);
  }

  .export-note {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(221, 235, 255, 0.76);
    font-size: 12px;
    line-height: 1.6;
  }

  .toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 0 0;
    margin-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.06);
  }

  .toggle-copy {
    font-size: 13px;
    line-height: 1.6;
    color: rgba(221, 235, 255, 0.76);
  }

  .switch {
    position: relative;
    width: 54px;
    height: 30px;
    flex-shrink: 0;
  }

  .switch input { display: none; }

  .switch-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.08);
    transition: 0.2s ease;
    cursor: pointer;
  }

  .switch-slider::before {
    content: "";
    position: absolute;
    width: 22px;
    height: 22px;
    left: 3px;
    top: 3px;
    border-radius: 50%;
    background: #fff;
    transition: 0.2s ease;
  }

  .switch input:checked + .switch-slider {
    background: linear-gradient(135deg, #00d1ff, #3578ff);
  }

  .switch input:checked + .switch-slider::before {
    transform: translateX(24px);
  }

  .reduce-motion .profile-modal,
  .reduce-motion .profile-content,
  .reduce-motion .modal-btn,
  .reduce-motion .user-box,
  .reduce-motion .profile-close,
  .reduce-motion .panel-submit,
  .reduce-motion .brand-animated,
  .reduce-motion .deadline-bell__trigger,
  .reduce-motion .deadline-bell__icon,
  .reduce-motion .deadline-bell__orb,
  .reduce-motion .deadline-bell__badge,
  .reduce-motion .deadline-bell__panel,
  .reduce-motion .deadline-bell__item,
  .reduce-motion .deadline-bell__close {
    animation: none !important;
    transition: none !important;
  }

  @media (max-width: 992px) {
    .profile-content { padding: 22px; }
    .modal-grid,
    .profile-panel-grid,
    .profile-form-grid,
    .account-fields,
    .readonly-grid { grid-template-columns: 1fr; }
    .deadline-bell__panel {
      width: min(420px, calc(100vw - 32px));
    }
    .deadline-bell__item {
      grid-template-columns: 1fr;
    }
    .deadline-bell__item-side {
      align-items: flex-start;
      text-align: left;
      min-width: 0;
    }
  }

  @media (max-width: 768px) {
    .weather-date {
      position: static !important;
      transform: none !important;
      width: 100% !important;
      justify-content: center !important;
      margin: 8px 0 !important;
      font-size: 14px !important;
      flex-wrap: wrap !important;
    }

    .navbar {
      align-items: center !important;
      gap: 10px !important;
      flex-wrap: wrap !important;
    }

    .topbar-actions {
      width: 100% !important;
      justify-content: flex-end !important;
      flex-wrap: wrap !important;
    }

    .deadline-bell__trigger {
      min-width: 0;
      padding-inline: 12px;
    }

    .deadline-bell__summary {
      font-size: 13px;
    }

    .deadline-bell__panel {
      right: 0;
      width: min(100vw - 24px, 400px);
      padding: 18px;
    }

    .deadline-bell__stats {
      grid-template-columns: 1fr;
    }

    .profile-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 14px;
    }
  }

  .snowflake {
    position: fixed;
    top: -10px;
    color: #fff;
    font-size: 1.2rem;
    user-select: none;
    pointer-events: none;
    z-index: 999;
    opacity: 0.8;
    animation: fall linear infinite;
  }

  @keyframes fall {
    0% { transform: translateY(-10px) rotate(0deg); }
    100% { transform: translateY(110vh) rotate(360deg); }
  }
  </style>

  <script>
    const snowCount = 50;
    for (let i = 0; i < snowCount; i++) {
      const snow = document.createElement('div');
      snow.classList.add('snowflake');
      snow.textContent = 'вќ„';
      snow.style.left = Math.random() * window.innerWidth + 'px';
      snow.style.fontSize = (Math.random() * 12 + 8) + 'px';
      snow.style.opacity = Math.random();
      snow.style.animationDuration = (Math.random() * 5 + 5) + 's';
      snow.style.animationDelay = Math.random() * 5 + 's';
    }

    window.addEventListener('resize', () => {
      const snowflakes = document.querySelectorAll('.snowflake');
      snowflakes.forEach(s => s.style.left = Math.random() * window.innerWidth + 'px');
    });

    document.addEventListener('DOMContentLoaded', () => {
      const brand = document.getElementById('brandName');
      const colors = ['#00d1ff', '#ff9800', '#28a745', '#e91e63', '#6f42c1', '#007bff'];
      let index = 0;

      setInterval(() => {
        brand.style.setProperty('color', colors[index], 'important');
        index = (index + 1) % colors.length;
      }, 5000);
    });
  </script>

  <div class="topbar">
    <nav class="navbar navbar-expand">
      <div class="brand-animated" id="brandName">Global Voice</div>

      <div class="weather-date" id="weatherDate">
        <div class="weather"><i class='bx bx-cloud'></i><span id="weatherInfo">Yuklanmoqda...</span></div>
        <div class="date"><i class='bx bx-calendar'></i><span id="dateInfo"></span></div>
      </div>

      <div class="topbar-actions">
        @if($canExcelExport)
          <button type="button" class="topbar-shortcut" id="openExcelPanel">
            <i class='bx bx-spreadsheet'></i>
            <span>Excel</span>
          </button>
        @endif

        @include('partials.deadline_bell')

        <div class="user-box" id="openProfile">
          <img src="{{ $authUser->avatar_url }}" alt="user avatar" id="topbarAvatar">
          <div class="user-info">
            <p class="user-name mb-0" id="topbarUserName">{{ $authUser->name }}</p>
          </div>
        </div>
      </div>
    </nav>
  </div>

  <div class="profile-modal" id="profileModal">
    <div class="profile-content">
      <button type="button" class="profile-close" id="closeProfileModal" aria-label="Yopish">
        <i class='bx bx-x'></i>
      </button>

      <div class="profile-header">
        <img src="{{ $authUser->avatar_url }}" alt="User" id="modalAvatar">
        <div>
          <div class="profile-title">Shaxsiy kabinet</div>
          <h3 id="modalUserName">{{ $authUser->name }}</h3>
          <p>Profil, xavfsizlik va shaxsiy interfeys sozlamalari shu yerda boshqariladi.</p>
        </div>
      </div>

      <div class="profile-badges">
        <div class="profile-badge" id="badgeRole"><i class='bx bx-shield-quarter'></i> {{ ucfirst(str_replace('_', ' ', $primaryRole)) }}</div>
        <div class="profile-badge" id="badgeFilial"><i class='bx bx-buildings'></i> {{ $filialName }}</div>
        <div class="profile-badge" id="badgeLogin"><i class='bx bx-id-card'></i> {{ $authUser->login }}</div>
      </div>

      <div class="modal-grid">
        <button type="button" class="modal-btn is-active" data-panel-target="panel-profile">
          <i class='bx bx-user-circle'></i>
          <div>
            <strong>Profil</strong>
            <span>Rasm, ism va telefon raqamingizni yangilang.</span>
          </div>
        </button>
        <button type="button" class="modal-btn" data-panel-target="panel-password">
          <i class='bx bx-lock-alt'></i>
          <div>
            <strong>Parolni o'zgartirish</strong>
            <span>Joriy parolni tasdiqlab yangi parol qo'ying.</span>
          </div>
        </button>
        <button type="button" class="modal-btn" data-panel-target="panel-settings">
          <i class='bx bx-cog'></i>
          <div>
            <strong>Sozlamalar</strong>
            <span>Ob-havo shahri va animatsiya rejimini boshqaring.</span>
          </div>
        </button>
        @if($canExcelExport)
          <button type="button" class="modal-btn" data-panel-target="panel-export">
            <i class='bx bx-spreadsheet'></i>
            <div>
              <strong>Excel</strong>
              <span>Mijozlar, dokumentlar va xodimlar bazasini yuklab oling.</span>
            </div>
          </button>
        @endif
        <a href="{{ route('destroy') }}" class="modal-btn modal-btn-danger">
          <i class='bx bx-log-out-circle'></i>
          <div>
            <strong>Chiqish</strong>
            <span>Joriy sessiyani xavfsiz yakunlaydi va tizimdan chiqaradi.</span>
          </div>
        </a>
      </div>

      <div class="profile-panel-wrap">
        <section class="profile-panel is-visible" id="panel-profile">
          <div class="profile-panel-title">Profil ma'lumotlari</div>
          <p class="profile-panel-note">Avatar, ism va telefon raqamingiz shu joydan yangilanadi. Login, rol va filial ma'lumotlari ma'lumot uchun ko'rsatiladi.</p>
          <div class="panel-feedback" data-feedback="profile"></div>

          <form id="profileForm" action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="profile-form-grid">
              <div class="avatar-editor">
                <div class="avatar-preview">
                  <img src="{{ $authUser->avatar_url }}" alt="Avatar preview" id="avatarPreview">
                </div>
                <label class="file-trigger" for="avatarInput">Rasm yuklash</label>
                <input type="file" name="avatar" id="avatarInput" class="hidden-input" accept=".jpg,.jpeg,.png,.webp">
                <div class="crop-shell" id="avatarCropShell">
                  <div class="crop-stage" id="avatarCropStage">
                    <img src="" alt="Avatar crop" id="avatarCropImage" draggable="false">
                    <div class="crop-grid"></div>
                  </div>
                  <div class="crop-toolbar">
                    <label class="field-label" for="avatarZoom">Zoom</label>
                    <input type="range" id="avatarZoom" class="crop-slider" min="100" max="300" step="1" value="100">
                    <button type="button" class="crop-reset" id="avatarRecenter">Markazga qaytarish</button>
                  </div>
                </div>
                <div class="avatar-meta">JPG, PNG yoki WEBP. Maksimal 3 MB. Rasmni tanlagach, drag va zoom bilan crop qilinadi.</div>
                <div class="field-error" data-error-for="avatar"></div>
              </div>

              <div>
                <div class="account-fields">
                  <div class="field-group is-full">
                    <label class="field-label" for="profileName">F.I.O</label>
                    <input type="text" class="field-input" id="profileName" name="name" value="{{ $authUser->name }}">
                    <div class="field-error" data-error-for="name"></div>
                  </div>
                  <div class="field-group">
                    <label class="field-label" for="profilePhone">Telefon</label>
                    <input type="text" class="field-input" id="profilePhone" name="phone" value="{{ $authUser->phone }}" maxlength="19" inputmode="numeric" autocomplete="tel" placeholder="+998 (__) ___-__-__">
                    <div class="field-help">Format: +998 (__) ___-__-__. Tizim saqlashda raqamni avtomatik tozalaydi.</div>
                    <div class="field-error" data-error-for="phone"></div>
                  </div>
                  <div class="field-group">
                    <label class="field-label" for="profileLogin">Login</label>
                    <input type="text" class="field-input" id="profileLogin" value="{{ $authUser->login }}" readonly>
                  </div>
                </div>

                <div class="readonly-grid">
                  <div class="readonly-card">
                    <span class="readonly-label">Rol</span>
                    <span class="readonly-value" id="profileRoleLabel">{{ ucfirst(str_replace('_', ' ', $primaryRole)) }}</span>
                  </div>
                  <div class="readonly-card">
                    <span class="readonly-label">Filial</span>
                    <span class="readonly-value" id="profileFilialLabel">{{ $filialName }}</span>
                  </div>
                </div>

                <div class="panel-actions">
                  <button type="submit" class="panel-submit" data-submit-label="Saqlash">Saqlash</button>
                </div>
              </div>
            </div>
          </form>
        </section>

        <section class="profile-panel" id="panel-password">
          <div class="profile-panel-title">Parolni o'zgartirish</div>
          <p class="profile-panel-note">Xavfsizlik uchun avval joriy parolni kiriting. Yangi parol kamida 6 belgidan iborat bo'lishi va tasdiqlanishi kerak.</p>
          <div class="panel-feedback" data-feedback="password"></div>

          <form id="passwordForm" action="{{ route('account.password.update') }}" method="POST" novalidate>
            @csrf
            <div class="account-fields">
              <div class="field-group is-full">
                <label class="field-label" for="currentPassword">Joriy parol</label>
                <input type="password" class="field-input" id="currentPassword" name="current_password" autocomplete="current-password">
                <div class="field-error" data-error-for="current_password"></div>
              </div>
              <div class="field-group">
                <label class="field-label" for="newPassword">Yangi parol</label>
                <input type="password" class="field-input" id="newPassword" name="password" autocomplete="new-password">
                <div class="field-error" data-error-for="password"></div>
              </div>
              <div class="field-group">
                <label class="field-label" for="confirmPassword">Parolni tasdiqlang</label>
                <input type="password" class="field-input" id="confirmPassword" name="password_confirmation" autocomplete="new-password">
                <div class="field-error" data-error-for="password_confirmation"></div>
              </div>
            </div>

            <div class="panel-actions">
              <button type="submit" class="panel-submit" data-submit-label="Parolni yangilash">Parolni yangilash</button>
            </div>
          </form>
        </section>

        <section class="profile-panel" id="panel-settings">
          <div class="profile-panel-title">Shaxsiy sozlamalar</div>
          <p class="profile-panel-note">Bu sozlamalar faqat sizning profilingizga bog'lanadi va header ichidagi account tajribasini moslaydi.</p>
          <div class="panel-feedback" data-feedback="settings"></div>

          <form id="settingsForm" action="{{ route('account.settings.update') }}" method="POST" novalidate>
            @csrf
            <div class="settings-card">
              <div class="field-group is-full">
                <label class="field-label" for="weatherCity">Ob-havo shahri</label>
                <input type="text" class="field-input" id="weatherCity" name="weather_city" value="{{ $weatherCity }}" placeholder="Masalan: Tashkent">
                <div class="field-error" data-error-for="weather_city"></div>
              </div>

              <div class="toggle-row">
                <div class="toggle-copy">
                  <strong>Animatsiyalarni kamaytirish</strong><br>
                  Rang almashishi va modal o'tishlarini soddalashtiradi. Sekin qurilmalarda foydali.
                </div>
                <label class="switch">
                  <input type="checkbox" id="reducedMotion" name="reduced_motion" {{ $reducedMotion ? 'checked' : '' }}>
                  <span class="switch-slider"></span>
                </label>
              </div>
            </div>

            <div class="panel-actions">
              <button type="submit" class="panel-submit" data-submit-label="Sozlamalarni saqlash">Sozlamalarni saqlash</button>
            </div>
          </form>
        </section>

        @if($canExcelExport)
          <section class="profile-panel" id="panel-export">
            <div class="profile-panel-title">Excel eksportlari</div>
            <p class="profile-panel-note">Har bir eksport relationlar bilan boyitilgan va Excel uchun tayyor workbook ko'rinishida yuklanadi.</p>

            <div class="export-grid">
              <a href="{{ route('superadmin.excel.download', ['dataset' => 'clients']) }}" class="export-card">
                <i class='bx bx-group'></i>
                <div>
                  <strong>Mijozlar</strong>
                  <span>Mijozlar bazasi, hujjatlar statistikasi va mijoz-hujjat relationlari bilan.</span>
                </div>
              </a>

              <a href="{{ route('superadmin.excel.download', ['dataset' => 'documents']) }}" class="export-card">
                <i class='bx bx-folder-open'></i>
                <div>
                  <strong>Dokumentlar</strong>
                  <span>Asosiy hujjatlar, to'lovlar, fayllar, courier va process charge sheetlari bilan.</span>
                </div>
              </a>

              <a href="{{ route('superadmin.excel.download', ['dataset' => 'employees']) }}" class="export-card">
                <i class='bx bx-id-card'></i>
                <div>
                  <strong>Xodimlar</strong>
                  <span>Xodimlar bazasi, rollar, filiallar va yaratilgan hujjatlar kesimida.</span>
                </div>
              </a>

              <a href="{{ route('superadmin.excel.download', ['dataset' => 'all']) }}" class="export-card">
                <i class='bx bx-layer'></i>
                <div>
                  <strong>Barchasi</strong>
                  <span>Uchala bazani ham bitta workbook ichida to'liq yuklab beradi.</span>
                </div>
              </a>
            </div>

            <div class="export-note">
              Fayllar `.xls` ko'rinishida yuklanadi va Excel yoki LibreOffice'da to'g'ridan-to'g'ri ochiladi.
            </div>
          </section>
        @endif
      </div>
    </div>
  </div>

  <script>
    const headerAccountConfig = {
      csrf: @json(csrf_token()),
      weatherCity: @json($weatherCity),
      reducedMotion: @json($reducedMotion),
      canExcelExport: @json($canExcelExport),
    };

    const weatherTranslations = {
      'clear sky': 'Osmon tiniq',
      'few clouds': 'Kam bulutli',
      'scattered clouds': 'Ortacha bulutli',
      'broken clouds': 'Bulutli',
      'overcast clouds': 'To\'liq bulutli',
      'shower rain': 'Yomg\'ir yog\'adi',
      'light rain': 'Yengil yomg\'ir',
      'moderate rain': 'Yomg\'ir',
      'heavy intensity rain': 'Kuchli yomg\'ir',
      'very heavy rain': 'Juda kuchli yomg\'ir',
      'extreme rain': 'Ekstremal yomg\'ir',
      'freezing rain': 'Muzli yomg\'ir',
      'light snow': 'Yengil qor',
      snow: 'Qor',
      'heavy snow': 'Kuchli qor',
      sleet: 'Qor aralash yomg\'ir',
      mist: 'Tuman',
      smoke: 'Tutun',
      haze: 'Xiralik',
      'sand/ dust whirls': 'Qum yoki chang bo\'roni',
      fog: 'Tuman',
      sand: 'Qum bo\'roni',
      dust: 'Chang',
      'volcanic ash': 'Vulqon kuli',
      squalls: 'Bo\'ron',
      tornado: 'Tornado',
    };

    function updateDate() {
      const now = new Date();
      const days = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];
      const months = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentyabr', 'oktyabr', 'noyabr', 'dekabr'];

      document.getElementById('dateInfo').innerText =
        `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }

    const API_KEY = '6d325d5ac3fbc4b0a3f6e1021e50896c';
    const openExcelPanel = document.getElementById('openExcelPanel');
    const openProfile = document.getElementById('openProfile');
    const profileModal = document.getElementById('profileModal');
    const closeProfileModal = document.getElementById('closeProfileModal');
    const panelButtons = document.querySelectorAll('[data-panel-target]');
    const panels = document.querySelectorAll('.profile-panel');
    const weatherInfo = document.getElementById('weatherInfo');
    const topbarAvatar = document.getElementById('topbarAvatar');
    const topbarUserName = document.getElementById('topbarUserName');
    const modalAvatar = document.getElementById('modalAvatar');
    const modalUserName = document.getElementById('modalUserName');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarInput = document.getElementById('avatarInput');
    const avatarCropShell = document.getElementById('avatarCropShell');
    const avatarCropStage = document.getElementById('avatarCropStage');
    const avatarCropImage = document.getElementById('avatarCropImage');
    const avatarZoom = document.getElementById('avatarZoom');
    const avatarRecenter = document.getElementById('avatarRecenter');
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const settingsForm = document.getElementById('settingsForm');
    const profilePhone = document.getElementById('profilePhone');
    const weatherCityInput = document.getElementById('weatherCity');
    const reducedMotionInput = document.getElementById('reducedMotion');
    const deadlineBell = document.getElementById('deadlineBell');
    const deadlineBellTrigger = document.getElementById('deadlineBellTrigger');
    const deadlineBellPanel = document.getElementById('deadlineBellPanel');
    const deadlineBellClose = document.getElementById('deadlineBellClose');
    const profileRoleLabel = document.getElementById('profileRoleLabel');
    const profileFilialLabel = document.getElementById('profileFilialLabel');
    const badgeRole = document.getElementById('badgeRole');
    const badgeFilial = document.getElementById('badgeFilial');
    const badgeLogin = document.getElementById('badgeLogin');
    const profileLogin = document.getElementById('profileLogin');
    let avatarObjectUrl = null;
    const avatarCropCanvas = document.createElement('canvas');
    avatarCropCanvas.width = 512;
    avatarCropCanvas.height = 512;
    const avatarCropContext = avatarCropCanvas.getContext('2d');
    const avatarCropState = {
      imageLoaded: false,
      naturalWidth: 0,
      naturalHeight: 0,
      scale: 1,
      minScale: 1,
      maxScale: 3,
      offsetX: 0,
      offsetY: 0,
      dragging: false,
      pointerId: null,
      startX: 0,
      startY: 0,
      startOffsetX: 0,
      startOffsetY: 0,
    };

    async function legacyGetWeather() {
      try {
        const res = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric`);
        const data = await res.json();

        if (data.main) {
          const temp = Math.round(data.main.temp);
          let desc = data.weather[0].description;

          const translate = {
            "clear sky": "Osmon tiniq",
            "few clouds": "Kam bulutli",
            "scattered clouds": "Ortacha bulutli",
            "broken clouds": "Bulutli",
            "overcast clouds": "Toliq bulutli",
            "shower rain": "Yomgir yogadi",
            "light rain": "Yengil yomgir",
            "moderate rain": "Yomgir",
            "heavy intensity rain": "Kuchli yomgir",
            "very heavy rain": "Juda kuchli yomgir",
            "extreme rain": "Ekstremal yomgir",
            "freezing rain": "Muzli yomgir",
            "light snow": "Yengil qor",
            "snow": "Qor",
            "heavy snow": "Kuchli qor",
            "sleet": "Qor aralash yomgir",
            "mist": "Tuman",
            "smoke": "Tutun",
            "haze": "Shamol bilan bulutli",
            "sand/ dust whirls": "Qum/tuproq boroni",
            "fog": "Tuman",
            "sand": "Qum boroni",
            "dust": "Chang",
            "volcanic ash": "Vulqon kul",
            "squalls": "Boron",
            "tornado": "Tornado"
          };

          desc = translate[desc] || desc;
          document.getElementById('weatherInfo').innerText = `${city}: ${temp}°C, ${desc}`;
        } else {
          document.getElementById('weatherInfo').innerText = "Ob-havo olinmadi";
        }
      } catch {
        document.getElementById('weatherInfo').innerText = "Ob-havo olinmadi";
      }
    }

    function openProfileModal() {
      setDeadlineBellOpen(false);
      profileModal.style.display = 'flex';
    }

    function closeProfile() {
      profileModal.style.display = 'none';
    }

    function setDeadlineBellOpen(open) {
      if (!deadlineBell || !deadlineBellTrigger || !deadlineBellPanel) {
        return;
      }

      deadlineBell.classList.toggle('is-open', Boolean(open));
      deadlineBellTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      deadlineBellPanel.hidden = !open;
    }

    function activatePanel(targetId) {
      panelButtons.forEach(button => {
        button.classList.toggle('is-active', button.dataset.panelTarget === targetId);
      });

      panels.forEach(panel => {
        panel.classList.toggle('is-visible', panel.id === targetId);
      });
    }

    function formatRole(role) {
      if (!role) {
        return 'Biriktirilmagan';
      }

      return role
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
    }

    function appendCacheBuster(url) {
      if (!url) {
        return '';
      }

      const separator = url.includes('?') ? '&' : '?';
      return `${url}${separator}v=${Date.now()}`;
    }

    function applyReducedMotion(enabled) {
      document.documentElement.classList.toggle('reduce-motion', Boolean(enabled));
    }

    async function getWeather(cityName = headerAccountConfig.weatherCity) {
      const city = (cityName || 'Bukhara').trim();

      if (!city) {
        weatherInfo.innerText = 'Ob-havo shahri tanlanmagan';
        return;
      }

      weatherInfo.innerText = 'Ob-havo yuklanmoqda...';

      try {
        const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=metric`);
        const data = await response.json();

        if (!response.ok || !data.main) {
          weatherInfo.innerText = 'Ob-havo olinmadi';
          return;
        }

        const temp = Math.round(data.main.temp);
        const rawDescription = data.weather?.[0]?.description || '';
        const description = weatherTranslations[rawDescription] || rawDescription;
        weatherInfo.innerText = `${city}: ${temp}°C, ${description}`;
      } catch (error) {
        weatherInfo.innerText = 'Ob-havo olinmadi';
      }
    }

    function feedbackElement(panelName) {
      return document.querySelector(`[data-feedback="${panelName}"]`);
    }

    function clearFeedback(panelName) {
      const element = feedbackElement(panelName);

      if (!element) {
        return;
      }

      element.textContent = '';
      element.classList.remove('is-visible', 'is-success', 'is-error');
    }

    function showFeedback(panelName, message, type = 'success') {
      const element = feedbackElement(panelName);

      if (!element) {
        return;
      }

      element.textContent = message;
      element.classList.add('is-visible');
      element.classList.toggle('is-success', type === 'success');
      element.classList.toggle('is-error', type === 'error');
    }

    function clearErrors(form) {
      form.querySelectorAll('.field-error').forEach((element) => {
        element.textContent = '';
      });

      form.querySelectorAll('.field-input').forEach((element) => {
        element.classList.remove('has-error');
      });
    }

    function setFieldError(form, fieldName, message) {
      const errorElement = form.querySelector(`[data-error-for="${fieldName}"]`);
      const input = form.querySelector(`[name="${fieldName}"]`);

      if (errorElement) {
        errorElement.textContent = message;
      }

      if (input) {
        input.classList.toggle('has-error', Boolean(message));
      }
    }

    function fillErrors(form, errors) {
      Object.entries(errors).forEach(([field, messages]) => {
        setFieldError(form, field, Array.isArray(messages) ? messages[0] : messages);
      });
    }

    function setBadgeContent(element, iconClass, text) {
      if (!element) {
        return;
      }

      element.replaceChildren();

      const icon = document.createElement('i');
      icon.className = iconClass;
      element.appendChild(icon);
      element.appendChild(document.createTextNode(` ${text}`));
    }

    function normalizePhoneDigits(value) {
      let digits = String(value || '').replace(/\D/g, '');

      if (digits.startsWith('998')) {
        digits = digits.slice(3);
      }

      return digits.slice(0, 9);
    }

    function formatPhoneDigits(value) {
      const digits = normalizePhoneDigits(value);

      if (!digits) {
        return '+998 ';
      }

      let formatted = '+998';

      if (digits.length > 0) {
        formatted += ` (${digits.slice(0, 2)}`;
      }

      if (digits.length >= 2) {
        formatted += ')';
      }

      if (digits.length > 2) {
        formatted += ` ${digits.slice(2, 5)}`;
      }

      if (digits.length > 5) {
        formatted += `-${digits.slice(5, 7)}`;
      }

      if (digits.length > 7) {
        formatted += `-${digits.slice(7, 9)}`;
      }

      return formatted;
    }

    function applyPhoneMask(value = profilePhone.value) {
      profilePhone.value = formatPhoneDigits(value);
    }

    function clampAvatarOffsets() {
      const stageSize = avatarCropStage.clientWidth || 1;
      const renderedWidth = avatarCropState.naturalWidth * avatarCropState.scale;
      const renderedHeight = avatarCropState.naturalHeight * avatarCropState.scale;
      const minOffsetX = Math.min(0, stageSize - renderedWidth);
      const minOffsetY = Math.min(0, stageSize - renderedHeight);

      avatarCropState.offsetX = Math.min(0, Math.max(minOffsetX, avatarCropState.offsetX));
      avatarCropState.offsetY = Math.min(0, Math.max(minOffsetY, avatarCropState.offsetY));
    }

    function drawAvatarPreview() {
      if (!avatarCropState.imageLoaded) {
        return;
      }

      const stageSize = avatarCropStage.clientWidth || 1;
      const sourceX = Math.max(0, -avatarCropState.offsetX / avatarCropState.scale);
      const sourceY = Math.max(0, -avatarCropState.offsetY / avatarCropState.scale);
      const sourceWidth = Math.min(avatarCropState.naturalWidth, stageSize / avatarCropState.scale);
      const sourceHeight = Math.min(avatarCropState.naturalHeight, stageSize / avatarCropState.scale);

      avatarCropContext.clearRect(0, 0, avatarCropCanvas.width, avatarCropCanvas.height);
      avatarCropContext.drawImage(
        avatarCropImage,
        sourceX,
        sourceY,
        sourceWidth,
        sourceHeight,
        0,
        0,
        avatarCropCanvas.width,
        avatarCropCanvas.height
      );

      avatarPreview.src = avatarCropCanvas.toDataURL('image/webp', 0.92);
    }

    function renderAvatarCrop() {
      if (!avatarCropState.imageLoaded) {
        return;
      }

      clampAvatarOffsets();

      const renderedWidth = avatarCropState.naturalWidth * avatarCropState.scale;
      const renderedHeight = avatarCropState.naturalHeight * avatarCropState.scale;

      avatarCropImage.style.width = `${renderedWidth}px`;
      avatarCropImage.style.height = `${renderedHeight}px`;
      avatarCropImage.style.transform = `translate(${avatarCropState.offsetX}px, ${avatarCropState.offsetY}px)`;
      drawAvatarPreview();
    }

    function resetAvatarCropPosition() {
      if (!avatarCropState.imageLoaded) {
        return;
      }

      const stageSize = avatarCropStage.clientWidth || 1;
      const baseScale = Math.max(
        stageSize / avatarCropState.naturalWidth,
        stageSize / avatarCropState.naturalHeight
      );

      avatarCropState.minScale = baseScale;
      avatarCropState.scale = baseScale * (Number(avatarZoom.value) / 100);
      avatarCropState.offsetX = (stageSize - (avatarCropState.naturalWidth * avatarCropState.scale)) / 2;
      avatarCropState.offsetY = (stageSize - (avatarCropState.naturalHeight * avatarCropState.scale)) / 2;
      renderAvatarCrop();
    }

    function hideAvatarCropEditor() {
      avatarCropShell.classList.remove('is-active');
      avatarCropStage.classList.remove('is-dragging');
      avatarCropImage.removeAttribute('src');
      avatarCropImage.style.width = '';
      avatarCropImage.style.height = '';
      avatarCropImage.style.transform = '';
      avatarZoom.value = 100;
      avatarCropState.imageLoaded = false;
      avatarCropState.dragging = false;
      avatarCropState.pointerId = null;
    }

    function revokeAvatarObjectUrl() {
      if (avatarObjectUrl) {
        URL.revokeObjectURL(avatarObjectUrl);
        avatarObjectUrl = null;
      }
    }

    function resetAvatarPreview() {
      revokeAvatarObjectUrl();
    }

    function clearAvatarSelection() {
      resetAvatarPreview();
      hideAvatarCropEditor();
      avatarInput.value = '';
      avatarPreview.src = modalAvatar.src;
    }

    async function loadAvatarForCropping(file) {
      resetAvatarPreview();
      avatarObjectUrl = URL.createObjectURL(file);

      await new Promise((resolve, reject) => {
        avatarCropImage.onload = resolve;
        avatarCropImage.onerror = reject;
        avatarCropImage.src = avatarObjectUrl;
      });

      avatarCropState.imageLoaded = true;
      avatarCropState.naturalWidth = avatarCropImage.naturalWidth;
      avatarCropState.naturalHeight = avatarCropImage.naturalHeight;
      avatarCropShell.classList.add('is-active');
      avatarZoom.value = 100;
      resetAvatarCropPosition();
    }

    async function buildProfileFormData() {
      const formData = new FormData(profileForm);
      formData.set('phone', normalizePhoneDigits(profilePhone.value));

      if (!avatarInput.files?.length) {
        formData.delete('avatar');
        return formData;
      }

      if (!avatarCropState.imageLoaded) {
        return formData;
      }

      const blob = await new Promise((resolve) => {
        avatarCropCanvas.toBlob((result) => resolve(result), 'image/webp', 0.92);
      });

      formData.delete('avatar');

      if (blob) {
        formData.append('avatar', blob, `avatar-${Date.now()}.webp`);
      }

      return formData;
    }

    async function parseJsonResponse(response) {
      const text = await response.text();

      if (!text) {
        return {};
      }

      try {
        return JSON.parse(text);
      } catch (error) {
        return {
          message: response.ok
            ? 'Server javobi qabul qilindi.'
            : `Server JSON qaytarmadi (status ${response.status}).`,
        };
      }
    }

    async function sendForm(form, panelName, options = {}) {
      const submitButton = form.querySelector('.panel-submit');
      const initialLabel = submitButton.dataset.submitLabel || submitButton.textContent.trim();

      clearErrors(form);
      clearFeedback(panelName);
      submitButton.disabled = true;
      submitButton.textContent = 'Saqlanmoqda...';

      try {
        const formData = options.buildFormData
          ? await options.buildFormData()
          : new FormData(form);

        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': headerAccountConfig.csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        const payload = await parseJsonResponse(response);

        if (!response.ok) {
          if (response.status === 422 && payload.errors) {
            fillErrors(form, payload.errors);
            showFeedback(panelName, 'Maydonlarni tekshirib qayta urinib ko\'ring.', 'error');
            return null;
          }

          throw new Error(payload.message || 'Saqlashda xatolik yuz berdi.');
        }

        showFeedback(panelName, payload.message || 'Ma\'lumotlar saqlandi.');
        return payload;
      } catch (error) {
        showFeedback(panelName, error.message || 'Saqlashda kutilmagan xatolik yuz berdi.', 'error');
        return null;
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = initialLabel;
      }
    }

    function updateProfileUI(user) {
      if (!user) {
        return;
      }

      const avatarUrl = appendCacheBuster(user.avatar_url);
      const roleLabel = formatRole(user.role);
      const filialLabel = user.filial || 'Biriktirilmagan';

      topbarUserName.textContent = user.name;
      modalUserName.textContent = user.name;
      profilePhone.value = formatPhoneDigits(user.phone);
      profileRoleLabel.textContent = roleLabel;
      profileFilialLabel.textContent = filialLabel;
      setBadgeContent(badgeRole, 'bx bx-shield-quarter', roleLabel);
      setBadgeContent(badgeFilial, 'bx bx-buildings', filialLabel);
      setBadgeContent(badgeLogin, 'bx bx-id-card', user.login);
      profileLogin.value = user.login;

      if (avatarUrl) {
        topbarAvatar.src = avatarUrl;
        modalAvatar.src = avatarUrl;
        avatarPreview.src = avatarUrl;
      }
    }

    if (openExcelPanel) {
      openExcelPanel.addEventListener('click', () => {
        openProfileModal();
        activatePanel('panel-export');
      });
    }

    if (deadlineBellTrigger && deadlineBellPanel) {
      deadlineBellTrigger.addEventListener('click', (event) => {
        event.stopPropagation();
        setDeadlineBellOpen(deadlineBellPanel.hidden);
      });

      deadlineBellPanel.addEventListener('click', (event) => {
        event.stopPropagation();
      });

      deadlineBellClose?.addEventListener('click', () => {
        setDeadlineBellOpen(false);
      });

      document.addEventListener('click', (event) => {
        if (!deadlineBell?.contains(event.target)) {
          setDeadlineBellOpen(false);
        }
      });
    }

    openProfile.addEventListener('click', openProfileModal);
    closeProfileModal.addEventListener('click', closeProfile);

    profileModal.addEventListener('click', (event) => {
      if (event.target === profileModal) {
        closeProfile();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !deadlineBellPanel?.hidden) {
        setDeadlineBellOpen(false);
      }

      if (event.key === 'Escape' && profileModal.style.display === 'flex') {
        closeProfile();
      }
    });

    panelButtons.forEach((button) => {
      button.addEventListener('click', () => activatePanel(button.dataset.panelTarget));
    });

    profilePhone.addEventListener('focus', () => {
      if (!normalizePhoneDigits(profilePhone.value)) {
        profilePhone.value = '+998 ';
      }
    });

    profilePhone.addEventListener('input', () => {
      applyPhoneMask(profilePhone.value);
    });

    avatarInput.addEventListener('change', async () => {
      clearFeedback('profile');
      setFieldError(profileForm, 'avatar', '');
      const file = avatarInput.files?.[0];

      if (!file) {
        clearAvatarSelection();
        return;
      }

      const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

      if (!allowedMimeTypes.includes(file.type)) {
        setFieldError(profileForm, 'avatar', 'Avatar JPG, PNG yoki WEBP formatda bo\'lishi kerak.');
        clearAvatarSelection();
        return;
      }

      if (file.size > 3 * 1024 * 1024) {
        setFieldError(profileForm, 'avatar', 'Avatar hajmi 3 MB dan oshmasligi kerak.');
        clearAvatarSelection();
        return;
      }

      try {
        await loadAvatarForCropping(file);
      } catch (error) {
        setFieldError(profileForm, 'avatar', 'Rasmni tayyorlashda xatolik yuz berdi.');
        clearAvatarSelection();
      }
    });

    avatarZoom.addEventListener('input', () => {
      if (!avatarCropState.imageLoaded) {
        return;
      }

      avatarCropState.scale = avatarCropState.minScale * (Number(avatarZoom.value) / 100);
      renderAvatarCrop();
    });

    avatarRecenter.addEventListener('click', () => {
      resetAvatarCropPosition();
    });

    avatarCropStage.addEventListener('pointerdown', (event) => {
      if (!avatarCropState.imageLoaded) {
        return;
      }

      avatarCropState.dragging = true;
      avatarCropState.pointerId = event.pointerId;
      avatarCropState.startX = event.clientX;
      avatarCropState.startY = event.clientY;
      avatarCropState.startOffsetX = avatarCropState.offsetX;
      avatarCropState.startOffsetY = avatarCropState.offsetY;
      avatarCropStage.classList.add('is-dragging');
      avatarCropStage.setPointerCapture(event.pointerId);
    });

    avatarCropStage.addEventListener('pointermove', (event) => {
      if (!avatarCropState.dragging || event.pointerId !== avatarCropState.pointerId) {
        return;
      }

      avatarCropState.offsetX = avatarCropState.startOffsetX + (event.clientX - avatarCropState.startX);
      avatarCropState.offsetY = avatarCropState.startOffsetY + (event.clientY - avatarCropState.startY);
      renderAvatarCrop();
    });

    function stopAvatarDragging(event) {
      if (event.pointerId !== undefined && event.pointerId !== avatarCropState.pointerId) {
        return;
      }

      avatarCropState.dragging = false;
      avatarCropStage.classList.remove('is-dragging');

      if (avatarCropState.pointerId !== null) {
        avatarCropStage.releasePointerCapture(avatarCropState.pointerId);
      }

      avatarCropState.pointerId = null;
    }

    avatarCropStage.addEventListener('pointerup', stopAvatarDragging);
    avatarCropStage.addEventListener('pointercancel', stopAvatarDragging);
    avatarCropStage.addEventListener('pointerleave', (event) => {
      if (avatarCropState.dragging) {
        stopAvatarDragging(event);
      }
    });

    window.addEventListener('resize', () => {
      if (avatarCropState.imageLoaded) {
        resetAvatarCropPosition();
      }
    });

    profileForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = await sendForm(profileForm, 'profile', { buildFormData: buildProfileFormData });

      if (!payload?.user) {
        return;
      }

      clearAvatarSelection();
      updateProfileUI(payload.user);
    });

    passwordForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = await sendForm(passwordForm, 'password');

      if (!payload) {
        return;
      }

      passwordForm.reset();
    });

    settingsForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const payload = await sendForm(settingsForm, 'settings');

      if (!payload?.settings) {
        return;
      }

      headerAccountConfig.weatherCity = payload.settings.weather_city || headerAccountConfig.weatherCity;
      headerAccountConfig.reducedMotion = Boolean(payload.settings.reduced_motion);
      weatherCityInput.value = headerAccountConfig.weatherCity;
      reducedMotionInput.checked = headerAccountConfig.reducedMotion;
      applyReducedMotion(headerAccountConfig.reducedMotion);
      getWeather(headerAccountConfig.weatherCity);
    });

    applyReducedMotion(headerAccountConfig.reducedMotion);
    applyPhoneMask(profilePhone.value);
    activatePanel(panelButtons[0]?.dataset.panelTarget || 'panel-profile');
    updateDate();
    setInterval(updateDate, 60000);
    getWeather();
  </script>

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</header>
