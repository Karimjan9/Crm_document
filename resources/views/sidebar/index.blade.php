<div class="sidebar-wrapper" data-simplebar="true">
    <style>
        
        .sidebar-wrapper {
            background-color: #ffffff !important;
            color: #15172a !important;
            font-family: "Inter", "system-ui", sans-serif;
            transition: 0.3s ease;
        }

        .sidebar-header {
            background: linear-gradient(135deg, #0066ff, #00aaff);
            color: #fff !important;
            padding: 16px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e6f0ff;
        }

        .sidebar-header .logo-text {
            color: #fff;
            font-weight: 600;
            font-size: 17px;
            margin-left: 10px;
        }

        .metismenu a {
            color: #15172a !important;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 3px 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .metismenu a:hover {
            background: #f0f7ff;
            color: #0066ff !important;
        }

        .metismenu li.active > a {
            background: #e6f2ff;
            color: #0066ff !important;
        }

        
        .parent-icon i {
            font-size: 25px;
            margin-right: 10px;
            transition: 0.3s ease;
        }


        .metismenu li:nth-child(1) .parent-icon i { color: #007bff; }   
        .metismenu li:nth-child(2) .parent-icon i { color: #28a745; }  
        .metismenu li:nth-child(3) .parent-icon i { color: #ff9800; }  
        .metismenu li:nth-child(4) .parent-icon i { color: #6f42c1; }   
        .metismenu li:nth-child(5) .parent-icon i { color: #e91e63; }   

    
        [data-simplebar="true"] .metismenu li:nth-child(1) .parent-icon i { color: #007bff; }
        [data-simplebar="true"] .metismenu li:nth-child(2) .parent-icon i { color: #ff9800; }
        [data-simplebar="true"] .metismenu li:nth-child(3) .parent-icon i { color: #28a745; }
        [data-simplebar="true"] .metismenu li:nth-child(4) .parent-icon i { color: #6f42c1; }

        
        .metismenu ul li a i {
            font-size: 18px;
            margin-right: 8px;
            color: #0095ff;
            transition: transform 0.3s ease, color 0.2s ease;
        }

        .metismenu ul li a:hover i {
            transform: translateX(3px);
            color: #0066ff;
        }

        .metismenu ul {
            background: #f9fbff;
            border-left: 3px solid #007bff20;
            border-radius: 6px;
            padding-left: 10px;
        }

        .badge.bg-warning.text-dark {
            background-color: #e0ecff !important;
            color: #15172a !important;
            font-size: 11px;
        }
    </style>

    <div class="sidebar-header">
        <div>
            
            <img src="{{ url('logo-icon.png') }}" class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">{{ "Global Voice" }}</h4>
        </div>
        <div class="toggle-icon ms-auto">
            <i class='bx bx-chevron-left'></i>
        
        </div>
    </div>
    
    <!--navigation-->
    <ul class="metismenu" id="menu">
        @role('admin')
            <li>
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-user-circle'></i></div>
                    <div class="menu-title">Foydalanuvchilar</div>
                </a>
                <ul>
                    <li><a href="#"><i class='bx bx-user'></i> Foydalanuvchilar</a></li>
                    <li><a href="#"><i class='bx bx-buildings'></i> Bo'limlar</a></li>
                    <li><a href="#"><i class='bx bx-cog'></i> Bo'lim va Jihozlar</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-package'></i></div>
                    <div class="menu-title">Cargo</div>
                </a>
                <ul>
                    <li><a href="#"><i class='bx bx-send'></i> Jo'natma</a></li>
                    <li><a href="#"><i class='bx bx-cube'></i> Cargo</a></li>
                    <li><a href="#"><i class='bx bx-user-pin'></i> Jo'natuvchi</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-chip'></i></div>
                    <div class="menu-title">Jihozlar</div>
                </a>
                <ul>
                    <li><a href="#"><i class='bx bx-shape-square'></i> Jihoz modeli</a></li>
                    <li><a href="#"><i class='bx bx-rename'></i> Jihoz nomi</a></li>
                    <li><a href="#"><i class='bx bx-category'></i> Jihoz turi</a></li>
                    <li><a href="#"><i class='bx bx-barcode'></i> Jihoz birligi</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-file'></i></div>
                    <div class="menu-title">Tugma excel uchun</div>
                </a>
                <ul>
                    <li><a href="#"><i class='bx bx-spreadsheet'></i> Jihoz modeli</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript:;" class="has-arrow" aria-expanded="false">
                    <div class="parent-icon"><i class='bx bx-briefcase'></i></div>
                    <div class="menu-title">Mansablar</div>
                </a>
                <ul>
                    <li><a href="#"><i class='bx bx-cog'></i> Mansab sozlash</a></li>
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
