<header>
    <!-- Google Font -->
   

    <style>
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
            height: 75px !important;
            border-bottom: 1px solid #00d1ff33 !important;
        }

        .navbar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        /* 🔹 Global Voice */
        .brand-animated {
            font-family: 'Poppins', sans-serif !important;
            font-size: 30px !important;
            font-weight: 700 !important;
            color: #00d1ff !important;
            letter-spacing: 1px !important;
            animation: fadeIn 1s ease-in-out !important;
            position: absolute !important;
            left: 50px !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-toggle-menu i {
            font-size: 26px !important;
            color: #fff !important;
            cursor: pointer !important;
            transition: 0.3s !important;
        }

        .mobile-toggle-menu i:hover {
            color: #00d1ff !important;
        }

        .weather-date {
            display: flex !important;
            align-items: center !important;
            gap: 25px !important;
            color: #fff !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            position: absolute !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }

        .weather-date i {
            color: #00d1ff !important;
            margin-right: 6px !important;
            font-size: 22px !important;
        }

        .user-box {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

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
            font-size: 15px !important;
            margin: 0 !important;
        }

        .dropdown-menu {
            background-color: #1f2137 !important;
            border: none !important;
            border-radius: 10px !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4) !important;
        }

        .dropdown-item {
            color: #fff !important;
            transition: 0.3s !important;
        }

        .dropdown-item:hover {
            background-color: #00d1ff !important;
            color: #15172a !important;
        }

        .bx {
            color: #fff !important;
        }
    </style>

    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            
           
            <div class="brand-animated" id="brandName">Global Voice</div>

            <!-- 🔹 Ob-havo va sana -->
            <div class="weather-date" id="weatherDate">
                <div class="weather"><i class='bx bx-cloud'></i><span id="weatherInfo">Yuklanmoqda...</span></div>
                <div class="date"><i class='bx bx-calendar'></i><span id="dateInfo"></span></div>
            </div>

            <!-- 🔹 Foydalanuvchi paneli -->
            <div class="user-box dropdown ms-auto">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('avatar-2.png') }}" class="user-img" alt="user avatar">
                    <div class="user-info ps-3">
                        <p class="user-name mb-0">{{ auth()->user()->name }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('destroy') }}">
                            <i class='bx bx-log-out-circle'></i>
                            <span>{{ __("exit") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

   <header style="display:flex; align-items:center; justify-content:space-between; padding:10px 40px; background-color:#15172a; color:white; position:fixed; top:0; width:100%; z-index:999;">
    <!-- Chap tomonda animatsiyali matn -->
    <div id="brandName" style="font-size:26px; font-weight:600; font-family:'Poppins', sans-serif; letter-spacing:1px;">
        Global Voice
    </div>

    <!-- O‘rta qismda ob-havo -->
    <div id="weatherInfo" style="font-size:20px; text-align:center; flex:1;">
        Ob-havo yuklanmoqda...
    </div>

    <!-- O‘ng tomonda sana -->
    <div id="dateInfo" style="font-size:18px; text-align:right; min-width:220px;">
        Sana yuklanmoqda...
    </div>

    <script>
        // === Sana ===
        function updateDate() {
            const now = new Date();
            const days = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];
            const months = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentyabr', 'oktyabr', 'noyabr', 'dekabr'];
            const day = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            document.getElementById("dateInfo").innerText = `${day}, ${date} ${month} ${year}`;
        }
        updateDate();

        // === Ob-havo ===
        const apiKey = "2a540431e9b3f8a9b6c46e2055b7b001";
        const city = "Bukhara";
        async function getWeather() {
            try {
                const res = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}&lang=uz`);
                const data = await res.json();
                const temp = Math.round(data.main.temp);
                const desc = data.weather[0].description;
                document.getElementById("weatherInfo").innerText = `${city}: ${temp}°C, ${desc}`;
            } catch (err) {
                document.getElementById("weatherInfo").innerText = "Ob-havo olinmadi";
            }
        }
        getWeather();

        // === Brend animatsiyasi (har 10s da o'zgaradi) ===
        const brandTexts = ["Global Voice", "Innovative Future", "Smart Planet", "Creative Vision"];
        let index = 0;
        function changeBrandName() {
            const el = document.getElementById("brandName");
            el.style.opacity = 0;
            setTimeout(() => {
                index = (index + 1) % brandTexts.length;
                el.innerText = brandTexts[index];
                el.style.opacity = 1;
            }, 500);
        }
        setInterval(changeBrandName, 10000);
    </script>
</header>

<!-- Zamonaviy shrift -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
#brandName {
    transition: opacity 0.6s ease-in-out;
}
</style>

</header>
