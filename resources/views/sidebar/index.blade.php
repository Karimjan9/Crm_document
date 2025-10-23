<div class="sidebar-wrapper" data-simplebar="true"> 
    <style>
        /* === Sidebar dizayn (oq-kok + neon effekt) === */

        .sidebar-header {
            background: linear-gradient(135deg, rgba(0,102,255,0.85), rgba(0,170,255,0.8)) !important;
            color: #fff !important;
            padding: 16px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgb(238, 230, 230);
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
            background: rgb(255, 255, 255);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .metismenu a {
            display: flex;
            align-items: center;
            color: #0f172a;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        /* === Neon hover effekti === */
        .metismenu a:hover {
            background: rgb(253, 249, 249);
            box-shadow: 0 0 12px rgba(0,102,255,0.35);
            color: #0056ff !important;
            transform: translateX(2px);
        }

        .metismenu li.active > a {
            background: rgb(238, 240, 243);
            box-shadow: 0 0 8px rgba(0,102,255,0.3);
            color: #0066ff !important;
        }

        /* === Icon ranglar === */
        .parent-icon i {
            font-size: 22px;
            margin-right: 10px;
            transition: 0.3s ease;
        }

        .metismenu li:nth-child(1) .parent-icon i { color: #007bff; }
        .metismenu li:nth-child(2) .parent-icon i { color: #00aaff; }
        .metismenu li:nth-child(3) .parent-icon i { color: #28a745; }
        .metismenu li:nth-child(4) .parent-icon i { color: #6f42c1; }
        .metismenu li:nth-child(5) .parent-icon i { color: #e91e63; }
        .metismenu li:nth-child(6) .parent-icon i { color: #ff9800; } /* Kalendar uchun rang */

        /* === Submenyular === */
        .metismenu ul {
            background: rgb(245, 241, 241) !important;
            border-left: 3px solid rgba(0,102,255,0.3);
            border-radius: 6px;
            padding-left: 10px;
            margin: 4px 0;
        }

        .metismenu ul li a {
            color: #0f172a;
            font-size: 14px;
        }

        .metismenu ul li a:hover i {
            transform: translateX(4px);
            color: #0066ff;
        }
    </style>

    <div class="sidebar-header">
        <div class="logo-text">Admin Panel</div>
    </div>
    
    <!--navigation-->
    <ul class="metismenu" id="menu">
        @role('admin')
         <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-git-branch'></i></div>
                <div class="menu-title">Crm document</div>
            </a>
            <ul>
                <li><a href="{{ route('admin.document.statistika') }}"><i class='bx bx-map'></i> Document </a></li>
                <li><a href="{{ route('admin.document.index') }}"><i class='bx bx-map'></i> Document work </a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-user-circle'></i></div>
                <div class="menu-title">Foydalanuvchilar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin.index') }}"><i class='bx bx-user'></i> Foydalanuvchilar</a></li>
                <li><a href="#"><i class='bx bx-buildings'></i> Bo'limlar</a></li>
                <li><a href="#"><i class='bx bx-cog'></i> Bo'lim va Jihozlar</a></li>
            </ul>
        </li>

        <!-- Filiallar -->
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-git-branch'></i></div>
                <div class="menu-title">Filiallar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin.filial.index') }}"><i class='bx bx-map'></i> Filial qismi</a></li>
            </ul>
        </li>

        <!-- Kuryer bo‘limi -->
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-package'></i></div>
                <div class="menu-title">Kuryer</div>
            </a>      
            <ul>
                <li>
                    <a href="{{ route('admin.filial.index') }}">
                        <i class='bx bx-user-pin'></i> Employee
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.filial.index') }}">
                        <i class='bx bx-cycling'></i> Courierba
                    </a>
                </li>
            </ul>
        </li>

        <!-- Xarajatlar -->
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-money'></i></div>
                <div class="menu-title">Xarajatlar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin.expense.index') }}"><i class='bx bx-coin-stack'></i> Xarajatlar faoliyat </a></li>
                <li><a href="{{ route('admin.statistika') }}"><i class='bx bx-line-chart'></i> Xarajatlar statistika </a></li>
            </ul>
        </li>

        <!-- 🗓 Kalendar bo‘limi -->
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-calendar'></i></div>
                <div class="menu-title">Kalendar</div>
            </a>
            <ul>
                <li><a href="{{ route('admin.calendar.index') }}"><i class='bx bx-calendar-event'></i> sanalar </a></li>
                <li><a href="{{ route('admin.calendar.create') }}"><i class='bx bx-plus-circle'></i> Tahrirlash </a></li>
            </ul>
        </li>
        @endrole
    </ul>
    <!--end navigation-->
</div>

<script>
    function redrect(url) {
        window.location.href = url;
    }
</script>
