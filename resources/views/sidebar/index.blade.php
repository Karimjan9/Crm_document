<div class="sidebar-wrapper" data-simplebar="true"> 

   <style>
.sidebar-header {
    background: linear-gradient(135deg, rgba(0,102,255,0.85), rgba(0,170,255,0.8)) !important;
    color: #ffffff !important;
    padding: 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgb(17, 14, 53);
    backdrop-filter: blur(10px);
}

.sidebar-header .logo-text {
    color: #fff;
    font-weight: 600;
    font-size: 17px;
    margin-left: 10px;
    letter-spacing: 0.5px;
}

.sidebar-wrapper {
    background: #15172a !important;
    backdrop-filter: blur(16px);
    border-right: 1px solid rgba(233, 215, 215, 0.1); 
}

.metismenu a {
    display: flex;
    align-items: center;
    color: #fff;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.metismenu a:hover {
    background: rgba(255,255,255,0.1);
    box-shadow: 0 0 12px rgba(0,102,255,0.35);
    color: #00d1ff !important;
    transform: translateX(2px);
}

.metismenu li.active > a {
    background: rgba(0,102,255,0.15);
    box-shadow: 0 0 8px rgba(0,102,255,0.3);
    color: #00d1ff !important;
}

/* ===================== ICON ANIMATIONS ===================== */
/* Asosiy bo‘lim ikonlari */
.parent-icon i {
    font-size: 28px;
    margin-right: 10px;
    transition: transform 0.3s ease, color 0.3s ease;
    animation: pulse 2.5s infinite, slide 3s infinite alternate;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes slide {
    0% { transform: translateX(0); }
    50% { transform: translateX(8px); }
    100% { transform: translateX(0); }
}

.parent-icon i:hover {
    transform: rotate(20deg) scale(1.2) translateX(4px);
    color: #00d1ff;
}

/* Submenu ikonlar */
.metismenu ul li a i {
    font-size: 20px;
    margin-right: 8px;
    transition: transform 0.3s ease, color 0.3s ease;
    animation: submenuPulse 3s infinite alternate;
}

.metismenu ul li a:hover i {
    transform: translateX(6px) rotate(10deg);
    color: #00d1ff;
}

@keyframes submenuPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Sidebar ranglar */
.metismenu li:nth-child(1) .parent-icon i { color: #007bff; }
.metismenu li:nth-child(2) .parent-icon i { color: #00aaff; }
.metismenu li:nth-child(3) .parent-icon i { color: #28a745; }
.metismenu li:nth-child(4) .parent-icon i { color: #6f42c1; }
.metismenu li:nth-child(5) .parent-icon i { color: #e91e63; }
.metismenu li:nth-child(6) .parent-icon i { color: #ff9800; }

/* Submenu va border */
.metismenu ul {
    background: rgb(38, 39, 66) !important;
    border-left: 3px solid rgba(0,102,255,0.3);
    border-radius: 6px;
    padding-left: 10px;
    margin: 4px 0;
}

.metismenu ul li a {
    color: #fff;
    font-size: 14px;
}

/* ===================== ADMIN FILIAL (PRO) ===================== */
.metismenu .filial-item > a {
    position: relative;
    background: rgba(14, 165, 164, 0.08);
    border: 1px solid rgba(14, 165, 164, 0.15);
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.12);
}
.metismenu .filial-item > a::before {
    content: "";
    position: absolute;
    left: 6px;
    top: 8px;
    bottom: 8px;
    width: 3px;
    border-radius: 999px;
    background: linear-gradient(180deg, #22d3ee, #2563eb);
    opacity: 0;
    transition: opacity 0.25s ease;
}
.metismenu .filial-item > a:hover::before,
.metismenu .filial-item.active > a::before {
    opacity: 1;
}
.metismenu .filial-item .parent-icon i {
    color: #38bdf8;
    animation: gentleFloat 6s ease-in-out infinite;
}
.metismenu .filial-item > a:hover {
    transform: translateX(4px);
    background: rgba(14, 165, 164, 0.15);
    box-shadow: 0 10px 26px rgba(2, 132, 199, 0.18);
}
.metismenu .filial-item > a:hover .parent-icon i {
    transform: scale(1.12) rotate(-4deg);
}
.metismenu .filial-item ul li a i {
    color: #93c5fd;
}

@keyframes gentleFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

@media (prefers-reduced-motion: reduce) {
    .parent-icon i,
    .metismenu ul li a i,
    .metismenu .filial-item .parent-icon i {
        animation: none !important;
        transition: none !important;
    }
}

/* ===================== DYNAMIC COLOR CHANGE (JS) ===================== */
</style>


 
        <br>

    <div class="sidebar-header">
        <div class="logo-text">Admin Panel</div>
    </div>

    <!--navigation-->
    <ul class="metismenu" id="menu">
       @hasanyrole('super_admin|admin_manager')
       
<li>
    <a href="javascript:;" class="has-arrow" aria-expanded="false">
        <div class="parent-icon">
            <i class='bx bx-folder'></i> <!-- CRM document asosiy ikon -->
        </div>
        <div class="menu-title">Crm document</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('superadmin.document.statistika') }}">
                <i class='bx bx-bar-chart-alt-2'></i> Document
            </a>
        </li>
        <li>
            <a href="{{ route('superadmin.document.index') }}">
                <i class='bx bx-task'></i> Document work
            </a>
        </li>
    </ul>
</li>


        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-user-circle'></i></div>
                <div class="menu-title">Foydalanuvchilar</div>
            </a>
            <ul>
                <li><a href="{{ route('superadmin.index') }}"><div class="mm-active-gray"><i class='bx bx-user'></i> Foydalanuvchilar</div></a></li>
                <li><a href="#"><div class="mm-active-gray"><i class='bx bx-buildings'></i> Bo'limlar</div></a></li>
                <li><a href="#"><div class="mm-active-gray"><i class='bx bx-cog'></i> Bo'lim va Jihozlar</div></a></li>
            </ul>
        </li>

        <!-- Filiallar -->
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-git-branch'></i></div>
                <div class="menu-title">Filiallar</div>
            </a>
            <ul>
                <li><a href="{{ route('superadmin.filial.index') }}"><div class="mm-active-gray"><i class='bx bx-map'></i> Filial qismi</div></a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-package'></i></div>
                <div class="menu-title">Kuryer</div>
            </a>
            <ul>
                <li>
                    <a href="{{ route('superadmin.filial.index') }}">
                        <div class="mm-active-gray">
                            <i class='bx bx-user-pin'></i> Employee
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('superadmin.filial.index') }}">
                        <div class="mm-active-gray">
                            <i class='bx bx-cycling'></i> Courierba
                        </div>
                    </a>
                </li>
            </ul>
        </li>

       
      <li>

    <a href="javascript:;" class="has-arrow" aria-expanded="false">
        <div class="parent-icon">
            <i class='bx bx-wallet'></i>
        </div>
        <div class="menu-title">Xarajatlar</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('superadmin.expense.index') }}">
                <i class='bx bx-receipt'></i> Xarajatlar faoliyat
            </a>
        </li>
        <li>
            <a href="{{ route('superadmin.statistika') }}">
                <i class='bx bx-bar-chart-alt-2'></i> Xarajatlar statistika
            </a>
        </li>
    </ul>

</li>


<li>
    <a href="javascript:;" class="has-arrow" aria-expanded="false">
        <div class="parent-icon">
            <i class='bx bx-file-find'></i> <!-- Hujjatlar asosiy ikon -->
        </div>
        <div class="menu-title">Hujjat sozlamalari</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('superadmin.document_type.index') }}">
                <i class='bx bx-file'></i> Legalizatsiya
            </a>
        </li>
        <li>
            <a href="{{ route('superadmin.direction_type.index') }}">
                <i class='bx bx-map'></i> Apostil
            </a>
        </li>
        <li>
            <a href="{{ route('superadmin.consulation.index') }}">
                <i class='bx bx-user-check'></i> Konsullik
            </a>
        </li>
    </ul>
</li>


<li>
    <a href="javascript:;" class="has-arrow" aria-expanded="false">
        <div class="parent-icon">
            <i class='bx bx-chat'></i> <!-- SMS asosiy ikon -->
        </div>
        <div class="menu-title">SMS Xabarnoma</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('superadmin.sms_message_text.index') }}">
                <i class='bx bx-receipt'></i> SMS Xabarnoma hisobot
            </a>
        </li>
        <li>
            <a href="{{ route('superadmin.sms_message_text.index') }}">
                <i class='bx bx-cog'></i> SMS Xabarnoma sozlamalar
            </a>
        </li>
    </ul>
</li>


    <a href="{{ route('superadmin.calendar.index') }}" class="d-flex align-items-center">
        <div class="parent-icon" style="color:#ff9800;"><i class='bx bx-calendar'></i></div>
        <div class="menu-title">Kalendar</div>
    </a>
</li>

<li>
    <a href="{{ route('superadmin.service.index') }}" class="d-flex align-items-center">
        <div class="parent-icon" style="color:#00aaff;"><i class='bx bx-conversation'></i></div>
        <div class="menu-title">Xizmatlar</div>
    </a>
</li>

        <!-- 🗓 Kalendar bo‘limi -->

        @endrole
        @hasanyrole('admin_filial')
             <li class="filial-item">
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-buildings'></i></div>
                <div class="menu-title">Filial Xodimlar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin_filial.index') }}"><div class="mm-active-gray"><i class='bx bx-group'></i> Xodimlar </div></a></li>
                <li><a href="{{ route('admin_filial.index') }}"><div class="mm-active-gray"><i class='bx bx-line-chart'></i> Statistika </div></a></li>
            </ul>
        </li>
          <li class="filial-item">
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-folder-open'></i></div>
                <div class="menu-title">Hujjatlar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin_filial.document.index') }}"><i class='bx bx-file-blank'></i> Hujjatlar </a></li>
                <li><a href="{{ route('admin_filial.doc_summary') }}"><i class='bx bx-bar-chart-alt-2'></i> Hujjat hisobot </a></li>
            </ul>
        </li>
          <li class="filial-item">
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                <div class="menu-title">Xarajatlar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin_filial.expense_admin.index') }}"><i class='bx bx-receipt'></i> Xarajatlar </a></li>
                <li><a href="{{ route('admin_filial.expense.statistika') }}"><i class='bx bx-trending-up'></i> Xarajatlar hisobot </a></li>
            </ul>
        </li>
        @endrole
           @hasanyrole('employee')
            <li class="filial-item">
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-folder-open'></i></div>
                    <div class="menu-title">Hujjatlar</div>
                </a>
                <ul>
                    <li><a href="{{ route('employee.document.index') }}"><i class='bx bx-file-blank'></i> Hujjatlarim </a></li>
                    <li><a href="{{ route('employee.doc_summary') }}"><i class='bx bx-bar-chart-alt-2'></i> Hujjat hisobot </a></li>
                </ul>
            </li>
            <li class="filial-item">
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                    <div class="menu-title">Xarajatlar</div>
                </a>
                <ul>
                    <li><a href="{{ route('employee.expense_admin.index') }}"><i class='bx bx-receipt'></i> Xarajatlarim </a></li>
                    <li><a href="{{ route('employee.expense.statistika') }}"><i class='bx bx-trending-up'></i> Xarajatlar hisobot </a></li>
                </ul>
            </li>
        @endrole

    </ul>

</div>

<script>
    function redrect(url) {
        window.location.href = url;
    }

    document.addEventListener('DOMContentLoaded', () => {
    const icons = Array.from(document.querySelectorAll('.parent-icon i'))
        .filter(icon => !icon.closest('.filial-item'));
    const colors = ['#007bff','#00aaff','#28a745','#6f42c1','#e91e63','#ff9800'];
    let index = 0;

    setInterval(() => {
        icons.forEach(icon => {
            icon.style.color = colors[index];
        });
        index = (index + 1) % colors.length;
    }, 5000);
});
</script>
