@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

:root {
  --blue-main: #4e73df;
  --blue-hover: #2e59d9;
  --danger: #e74a3b;
  --light-gray: #f8f9fc;
  --border: #dee2e6;
}

* { margin:0; padding:0; box-sizing:border-box; }

.page-wrapper {
  font-family: 'Poppins', sans-serif;
  background: var(--light-gray);
  min-height: 100vh;
  padding: 30px 20px;
}

.page-content {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  padding: 30px;
  max-width: 900px;
  margin: 0 auto;
}

h2 {
  margin-bottom: 25px;
  color: #333;
  text-align: center;
  font-size: 28px;
}

.calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  margin-bottom: 20px;
}

.day, .day-header {
  text-align: center;
  padding: 12px 0;
  border-radius: 10px;
  font-weight: 500;
  transition: 0.3s;
}

.day-header {
  background: linear-gradient(135deg, var(--blue-main), var(--blue-hover));
  color: #fff;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.day {
  background: #fff;
  border: 1px solid var(--border);
  cursor: pointer;
  min-height: 55px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
}

.day:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.holiday {
  background: #fdecea;
  border: 1px solid var(--danger);
  color: var(--danger);
  font-weight: 600;
}

/* Modal */
.modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center; padding:20px;}
.modal-overlay.active { display:flex; }

.popup-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 12px 36px rgba(0,0,0,0.18);
  padding: 25px;
  max-width: 400px;
  width: 100%;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn { from { opacity: 0; transform: translateY(-25px); } to { opacity:1; transform:translateY(0); } }

.popup-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
.popup-date { font-size:20px; font-weight:600; color:var(--blue-main); }
.popup-close { background:none; border:none; font-size:26px; cursor:pointer; color:#999; transition:0.2s; }
.popup-close:hover { color:#333; }
.popup-message { font-size:15px; color:#555; margin-bottom:20px; line-height:1.5; text-align:center; }
.popup-buttons { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }
.btn-mini { padding:8px 16px; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; transition:0.3s; }
.btn-holiday { background:var(--danger); color:#fff; }
.btn-holiday:hover { background:#c0392b; box-shadow:0 3px 12px rgba(231,74,59,0.3); }
.btn-remove { background:var(--blue-main); color:#fff; }
.btn-remove:hover { background:var(--blue-hover); box-shadow:0 3px 12px rgba(78,115,223,0.3); }
.btn-close-card { background:#e9ecef; color:#495057; }
.btn-close-card:hover { background:#dee2e6; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">
    <h2>📅 Calendar</h2>
    <div id="calendar" class="calendar"></div>
  </div>
</div>

<div id="modal-overlay" class="modal-overlay">
  <div class="popup-card">
    <div class="popup-header">
      <div class="popup-date"></div>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-message"></div>
    <div id="popup-buttons" class="popup-buttons"></div>
  </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function() {
  const calendar = document.getElementById('calendar');
  const modalOverlay = document.getElementById('modal-overlay');
  const popupDate = document.querySelector('.popup-date');
  const popupMessage = document.querySelector('.popup-message');
  const popupButtonsContainer = document.getElementById('popup-buttons');
  const closeBtn = document.querySelector('.popup-close');

  let holidays = [];
  let selectedDate = '';
  let isAdmin = true;

  function renderCalendar() {
    calendar.innerHTML = '';
    const today = new Date();
    const y = today.getFullYear();
    const m = today.getMonth();
    const firstDay = new Date(y, m, 1).getDay(); // 0-6 (Sun-Sat)
    const daysInMonth = new Date(y, m + 1, 0).getDate();
    const weekDays = ['Dush','Sesh','Chor','Pay','Jum','Shan','Yak'];

    // Weekday headers
    weekDays.forEach(w => {
      const d = document.createElement('div');
      d.textContent = w;
      d.className = 'day-header';
      calendar.appendChild(d);
    });

    // Empty cells before first day
    let emptyCells = firstDay;
    if(emptyCells === 0) emptyCells = 7; // make Sunday last
    for(let i=1; i<emptyCells; i++){
      const empty = document.createElement('div');
      calendar.appendChild(empty);
    }

    // Days
    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const cell = document.createElement('div');
      cell.className = 'day';
      cell.textContent = d;

      if (holidays.includes(dateStr)) cell.classList.add('holiday');

      cell.onclick = function() {
        selectedDate = dateStr;
        showPopup(dateStr, cell.classList.contains('holiday'));
      }

      calendar.appendChild(cell);
    }
  }

  function showPopup(date, isHoliday) {
    popupDate.textContent = date;
    popupMessage.textContent = isHoliday ? "Bugun bayram." : "Bugun ish kuni emas.";
    popupButtonsContainer.innerHTML = '';

    if (isAdmin) {
      if (isHoliday) {
        const btnRemove = document.createElement('button');
        btnRemove.className = 'btn-mini btn-remove';
        btnRemove.textContent = 'Ish kuni sifatida belgilang';
        btnRemove.onclick = function() {
          holidays = holidays.filter(h => h !== selectedDate);
          renderCalendar();
          closePopup();
        }
        popupButtonsContainer.appendChild(btnRemove);
      } else {
        const btnHoliday = document.createElement('button');
        btnHoliday.className = 'btn-mini btn-holiday';
        btnHoliday.textContent = 'Mark as holiday';
        btnHoliday.onclick = function() {
          if (!holidays.includes(selectedDate)) holidays.push(selectedDate);
          renderCalendar();
          closePopup();
        }
        popupButtonsContainer.appendChild(btnHoliday);
      }
    }

    const btnClose = document.createElement('button');
    btnClose.className = 'btn-mini btn-close-card';
    btnClose.textContent = 'Close';
    btnClose.onclick = closePopup;
    popupButtonsContainer.appendChild(btnClose);

    modalOverlay.classList.add('active');
  }

  function closePopup() { modalOverlay.classList.remove('active'); }

  closeBtn.addEventListener('click', closePopup);
  modalOverlay.addEventListener('click', e => { if(e.target===modalOverlay) closePopup(); });

  renderCalendar();
});
</script>
@endsection
