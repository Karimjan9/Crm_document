<div class="sidebar-wrapper" data-simplebar="true">

<style>
:root {
    --main-color: #00aaff;
    --bg-dark: #15172a;
    --bg-hover: rgba(255,255,255,0.08);
}

/* Sidebar */
.sidebar-wrapper {
    background: var(--bg-dark) !important;
    border-right: 1px solid rgba(255,255,255,0.08);
}

/* Header */
.sidebar-header {
    background: var(--bg-dark);
    color: #fff;
    padding: 16px;
    font-weight: 600;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* Menu */
.metismenu a {
    display: flex;
    align-items: center;
    color: #cfd3ff;
    padding: 10px 12px;
    border-radius: 8px;
    transition: 0.2s ease;
}

/* Hover */
.metismenu a:hover {
    background: var(--bg-hover);
    color: #fff;
}

/* Active */
.metismenu li.active > a {
    background: rgba(0,170,255,0.15);
    color: var(--main-color) !important;
}

/* ICON */
.parent-icon i {
    font-size: 22px;
    margin-right: 10px;
    color: var(--main-color);
}

/* Submenu */
.metismenu ul {
    background: transparent;
    padding-left: 15px;
}

.metismenu ul li a {
    font-size: 14px;
    color: #aeb4ff;
}

.metismenu ul li a i {
    color: var(--main-color);
    font-size: 18px;
    margin-right: 8px;
}
</style>

<br>

<div class="sidebar-header">
    <div class="logo-text">Admin Panel</div>
</div>

<ul class="metismenu" id="menu">

@hasanyrole('super_admin|admin_manager')

<!-- CRM -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-folder-open'></i></div>
        <div class="menu-title">Crm document</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.document.statistika') }}"><i class='bx bx-bar-chart'></i> Document</a></li>
        <li><a href="{{ route('superadmin.document.index') }}"><i class='bx bx-task'></i> Document work</a></li>
    </ul>
</li>

<!-- Users -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-user'></i></div>
        <div class="menu-title">Foydalanuvchilar</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.index') }}"><i class='bx bx-user'></i> Foydalanuvchilar</a></li>
        <li><a href="{{ route('superadmin.filial.index') }}"><i class='bx bx-group'></i> Bo'limlar</a></li>
        <li><a href="{{ route('superadmin.service.index') }}"><i class='bx bx-cog'></i> Jihozlar</a></li>
    </ul>
</li>

<!-- Filial -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-buildings'></i></div>
        <div class="menu-title">Filiallar</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.filial.index') }}"><i class='bx bx-map'></i> Filial qismi</a></li>
    </ul>
</li>

<!-- Kuryer -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-package'></i></div>
        <div class="menu-title">Kuryer</div>
    </a>
    <ul>
        <li><a href="#"><i class='bx bx-id-card'></i> Employee</a></li>
        <li><a href="#"><i class='bx bx-cycling'></i> Courier</a></li>
    </ul>
</li>

<!-- Xarajat -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-wallet'></i></div>
        <div class="menu-title">Xarajatlar</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.expense.index') }}"><i class='bx bx-receipt'></i> Xarajatlar</a></li>
        <li><a href="{{ route('superadmin.statistika') }}"><i class='bx bx-line-chart'></i> Statistika</a></li>
    </ul>
</li>

<!-- Hujjat sozlama -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-file'></i></div>
        <div class="menu-title">Hujjat sozlamalari</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.document_type.index') }}"><i class='bx bx-file'></i> Legalizatsiya</a></li>
        <li><a href="{{ route('superadmin.direction_type.index') }}"><i class='bx bx-map-alt'></i> Apostil</a></li>
        <li><a href="{{ route('superadmin.consulation.index') }}"><i class='bx bx-user-check'></i> Konsullik</a></li>
        <li><a href="{{ route('superadmin.template_package.index') }}"><i class='bx bx-layer'></i> Shablonlar</a></li>
    </ul>
</li>

<!-- SMS -->
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-message'></i></div>
        <div class="menu-title">SMS Xabarnoma</div>
    </a>
    <ul>
        <li><a href="{{ route('superadmin.sms_message_text.report') }}"><i class='bx bx-list-ul'></i> Hisobot</a></li>
        <li><a href="{{ route('superadmin.sms_message_text.index') }}"><i class='bx bx-cog'></i> Sozlamalar</a></li>
    </ul>
</li>

<!-- Kalendar -->
<li>
    <a href="{{ route('superadmin.calendar.full.index') }}">
        <div class="parent-icon"><i class='bx bx-calendar'></i></div>
        <div class="menu-title">Kalendar</div>
    </a>
</li>

<!-- Xizmat -->
<li>
    <a href="{{ route('superadmin.service.index') }}">
        <div class="parent-icon"><i class='bx bx-briefcase'></i></div>
        <div class="menu-title">Xizmatlar</div>
    </a>
</li>

@endrole

</ul>

</div>
