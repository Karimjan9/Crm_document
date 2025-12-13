<header>
  <style>
  * { box-sizing: border-box !important; }

  /* ===================== EXISTING HEADER STYLES ===================== */
  .topbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background-color: #15172a !important;
    padding: 10px 25px !important;
    width: 100% !important;
    position: fixed !important;
    top: 0 !important;
    left:0 !important;
    z-index: 1000 !important;
    border-bottom: 1px solid #0c0f0f33 !important;
    flex-wrap: wrap !important;
  }

  .navbar { display: flex !important; align-items: flex-end !important; justify-content: space-between !important; width: 100% !important; }

  .brand-animated { font-family: 'Poppins', sans-serif !important; font-size: 30px !important; font-weight: 700 !important; color: #00d1ff !important; transition: opacity 0.6s ease-in-out !important; }

  .weather-date { position: absolute !important; left: 50% !important; transform: translateX(-50%) !important; display: flex !important; align-items: center !important; gap: 20px !important; color: #fff !important; font-size: 18px !important; font-weight: 600 !important; }

  .weather-date i { color: #00d1ff !important; font-size: 22px !important; margin-right: 6px !important; }

  .user-box { display: flex !important; align-items: center !important; gap: 10px !important; cursor: pointer !important; transition: transform 0.2s ease, color 0.2s ease !important; }
  .user-box:hover { transform: scale(1.03) !important; }
  .user-box img { width: 45px !important; height: 45px !important; border-radius: 50% !important; border: 2px solid #00d1ff !important; object-fit: cover !important; }

  .user-info .user-name { color: #fff !important; font-weight: 600 !important; font-size: 16px !important; margin: 0 !important; }

  /* ===================== MODAL ===================== */
  .profile-modal { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); justify-content: center; align-items: center; z-index: 2000; animation: fadeIn 0.3s ease; }
  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

  .profile-content { background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(15px); border: 1px solid #00d1ff55; border-radius: 25px; padding: 40px 50px; text-align: center; color: #fff; width: 550px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); animation: scaleUp 0.3s ease; }
  @keyframes scaleUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

  .profile-content img { width: 90px; height: 90px; border-radius: 50%; border: 3px solid #00d1ff; margin-bottom: 15px; object-fit: cover; }
  .profile-content h3 { margin: 0; font-size: 22px; color: #00d1ff; }
  .profile-content p { margin: 6px 0 25px; font-size: 15px; opacity: 0.8; }

  .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .modal-btn { background: rgba(255,255,255,0.05); border: 1px solid #00d1ff33; border-radius: 12px; padding: 14px 0; color: #fff; font-weight: 600; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease; }
  .modal-btn i { font-size: 20px; color: #00d1ff; transition: all 0.3s ease; }
  .modal-btn:hover { background: #00d1ff; color: #fff; transform: translateY(-3px); }
  .modal-btn:hover i { color: #fff; }

  @media (max-width: 992px) { .profile-content { width: 90%; padding: 25px; } .modal-grid { grid-template-columns: 1fr; } }

  /* ===================== NEW: Snowfall Animation ===================== */
  .snowflake {
    position: fixed;
    top: -10px;
    color: #fff;
    font-size: 1.2rem;
    user-select: none;
    pointer-events: none;
    z-index: 999; /* yuqorida ko‘rinadi */
    opacity: 0.8;
    animation: fall linear infinite;
  }

  @keyframes fall {
    0% { transform: translateY(-10px) rotate(0deg); }
    100% { transform: translateY(110vh) rotate(360deg); }
  }
</style>

<script>
  // ===================== Snowfall JS =====================
  const snowCount = 50; // qor parchalar soni
  for(let i=0;i<snowCount;i++){
      const snow = document.createElement('div');
      snow.classList.add('snowflake');
      snow.textContent = '❄';
      snow.style.left = Math.random() * window.innerWidth + 'px';
      snow.style.fontSize = (Math.random() * 12 + 8) + 'px';
      snow.style.opacity = Math.random();
      snow.style.animationDuration = (Math.random()*5 + 5) + 's';
      snow.style.animationDelay = Math.random() * 5 + 's';
      document.body.appendChild(snow);
  }

  // Window resize uchun snowflake larni qayta joylash
  window.addEventListener('resize', () => {
      const snowflakes = document.querySelectorAll('.snowflake');
      snowflakes.forEach(s => s.style.left = Math.random() * window.innerWidth + 'px');
  });
</script>


  <div class="topbar">
    <nav class="navbar navbar-expand">
      <div class="brand-animated" id="brandName">Global Voice</div>

      <div class="weather-date" id="weatherDate">
        <div class="weather"><i class='bx bx-cloud'></i><span id="weatherInfo">Yuklanmoqda...</span></div>
        <div class="date"><i class='bx bx-calendar'></i><span id="dateInfo"></span></div>
      </div>

      <div class="user-box" id="openProfile">
        <img src="{{ url('avatar-4.png') }}" alt="user avatar">
        <div class="user-info">
          <p class="user-name mb-0">{{ auth()->user()->name }}</p>
        </div>
      </div>
    </nav>
  </div>

  <!-- ✅ MODAL -->
  <div class="profile-modal" id="profileModal">
    <div class="profile-content">
      <img src="{{ url('avatar-4.png') }}" alt="User">      
      {{-- <h3>{{ auth()->user()->name }}</h3> --}}
      <p>Shaxsiy kabinet</p>

      <div class="modal-grid">
        <a href="#" class="modal-btn"><i class='bx bx-user'></i> Profil</a>
        <a href="#" class="modal-btn"><i class='bx bx-lock-alt'></i> Parolni o‘zgartirish</a>
        <a href="#" class="modal-btn"><i class='bx bx-cog'></i> Sozlamalar</a>
        <a href="{{ route('destroy') }}" class="modal-btn"><i class='bx bx-log-out-circle'></i> Chiqish</a>
      </div>
    </div>
  </div>

  <script>
 function updateDate() {
  const now = new Date();
  const days = ['Yakshanba','Dushanba','Seshanba','Chorshanba','Payshanba','Juma','Shanba'];
  const months = ['yanvar','fevral','mart','aprel','may','iyun','iyul','avgust','sentyabr','oktyabr','noyabr','dekabr'];
  document.getElementById("dateInfo").innerText =
    `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}
updateDate();

const API_KEY = "6d325d5ac3fbc4b0a3f6e1021e50896c";
const city = "Bukhara";

async function getWeather() {
  try {
    const res = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric`);
    const data = await res.json();

    if (data.main) {
      const temp = Math.round(data.main.temp);
      let desc = data.weather[0].description;

      // 🌤 100% o‘zbekcha tarjima lug‘ati
      const translate = {
        "clear sky": "Osmon tiniq",
        "few clouds": "Kam bulutli",
        "scattered clouds": "O‘rtacha bulutli",
        "broken clouds": "Bulutli",
        "overcast clouds": "To‘liq bulutli",
        "shower rain": "Yomg‘ir yog‘adi",
        "light rain": "Yengil yomg‘ir",
        "moderate rain": "Yomg‘ir",
        "heavy intensity rain": "Kuchli yomg‘ir",
        "very heavy rain": "Juda kuchli yomg‘ir",
        "extreme rain": "Ekstremal yomg‘ir",
        "freezing rain": "Muzli yomg‘ir",
        "light snow": "Yengil qor",
        "snow": "Qor",
        "heavy snow": "Kuchli qor",
        "sleet": "Qor aralash yomg‘ir",
        "mist": "Tuman",
        "smoke": "Tutun",
        "haze": "Shamol bilan bulutli",
        "sand/ dust whirls": "Qum/tuproq bo‘roni",
        "fog": "Tuman",
        "sand": "Qum bo‘roni",
        "dust": "Chang",
        "volcanic ash": "Vulqon kul",
        "squalls": "Bo‘ron",
        "tornado": "Tornado"
      };

      desc = translate[desc] || desc;
      document.getElementById("weatherInfo").innerText = `${city}: ${temp}°C, ${desc}`;
    } else {
      document.getElementById("weatherInfo").innerText = "Ob-havo olinmadi";
    }
  } catch {
    document.getElementById("weatherInfo").innerText = "Ob-havo olinmadi";
  }
}

// 🪄 Modal funksiyasi
const openProfile = document.getElementById("openProfile");
const profileModal = document.getElementById("profileModal");

openProfile.addEventListener("click", () => {
  profileModal.style.display = "flex";
});

profileModal.addEventListener("click", (e) => {
  if (e.target === profileModal) profileModal.style.display = "none";
});

getWeather();

  </script>

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</header>
