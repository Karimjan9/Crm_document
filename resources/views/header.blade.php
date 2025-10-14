<header>
  <style>
    * {
        box-sizing: border-box !important;
    }

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
        flex-wrap: wrap !important;
    }

    .navbar {
        display: flex !important;
        align-items: flex-end !important; /* 🔽 markazdan pastga tushirdi */
        justify-content: space-between !important;
        width: 100% !important;
    }

    /* Brend */
    .brand-animated {
        font-family: 'Poppins', sans-serif !important;
        font-size: 30px !important;
        font-weight: 700 !important;
        color: #00d1ff !important;
        letter-spacing: 1px !important;
        transition: opacity 0.6s ease-in-out !important;
    }

    /* 🔹 Ob-havo markazda joylashadi */
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
        text-align: center !important;
    }

    .weather-date i {
        color: #00d1ff !important;
        font-size: 22px !important;
        margin-right: 6px !important;
    }

    /* 🔹 Admin qismi */
    .user-box {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        transform: translateY(4px) !important; /* 🔽 pastga surildi */
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

    /* Responsiv */
    @media (max-width: 992px) {
        .topbar {
            flex-direction: column !important;
            height: auto !important;
            padding: 15px !important;
            text-align: center !important;
        }

        .weather-date {
            position: static !important;
            transform: none !important;
            flex-direction: column !important;
            gap: 5px !important;
            margin-top: 10px !important;
        }

        .brand-animated {
            font-size: 24px !important;
        }

        .user-box {
            transform: none !important;
            margin-top: 10px !important;
            justify-content: center !important;
        }
    }
  </style>

  <div class="topbar">
      <nav class="navbar navbar-expand">
          <div class="brand-animated" id="brandName">Global Voice</div>

          <div class="weather-date" id="weatherDate">
              <div class="weather"><i class='bx bx-cloud'></i><span id="weatherInfo">Yuklanmoqda...</span></div>
              <div class="date"><i class='bx bx-calendar'></i><span id="dateInfo"></span></div>
          </div>

          <div class="user-box dropdown ms-auto">
              <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret"
                 href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="{{ url('avatar-4.png') }}" class="user-img" alt="user avatar">
                  <div class="user-info ps-3">
                      <p class="user-name mb-0">{{ auth()->user()->name }}</p>
                  </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="#"><i class='bx bx-user'></i> Profil</a></li>
                  <li><a class="dropdown-item" href="#"><i class='bx bx-lock-alt'></i> Parolni o‘zgartirish</a></li>
                  <li><a class="dropdown-item" href="#"><i class='bx bx-cog'></i> Sozlamalar</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="{{ route('destroy') }}"><i class='bx bx-log-out-circle'></i> Chiqish</a></li>
              </ul>
          </div>
      </nav>
  </div>

  <script>
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

      const API_KEY = "6d325d5ac3fbc4b0a3f6e1021e50896c";
      const city = "Bukhara";
      async function getWeather() {
          try {
              const res = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric`);
              const data = await res.json();
              if (data.main) {
                  const temp = Math.round(data.main.temp);
                  const desc = data.weather[0].description;
                  document.getElementById("weatherInfo").innerText = `${city}: ${temp}°C, ${desc}`;
              } else {
                  document.getElementById("weatherInfo").innerText = "Ob-havo olinmadi";
              }
          } catch (err) {
              document.getElementById("weatherInfo").innerText = "Ob-havo olinmadi";
          }
      }
      getWeather();

      const brandTexts = ["Welcome to Global Voice", "Innovative Future", "Smart Planet", "Creative Vision"];
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

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</header>
