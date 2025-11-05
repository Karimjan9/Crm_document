@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

:root {
  --blue-main: #007bff;
  --blue-hover: #0056b3;
  --danger: #ff4d4d;
  --light-gray: #f4f6f8;
  --border: #ddd;
}

.page-wrapper {
  font-family: 'Poppins', sans-serif;
  background: var(--light-gray);
  min-height: 100vh;
  padding: 20px;
}

.page-content {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 30px;
  display: grid;  
  grid-template-columns: 1fr 300px;
  gap: 32px;
  align-items: start;
}

.calendar-main { flex: 1; }

.controls {
  display:flex;
  gap:8px;
  align-items:center;
  margin-bottom:10px;
}
.ctrl-btn {
  border:1px solid var(--border);
  background:#fff;
  padding:8px 10px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.ctrl-btn:hover { background: rgba(0,123,255,0.06); }

.calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  margin-top: 12px;
}

.day {
  background: #fff;
  border: 1px solid var(--border);
  text-align: center;
  padding: 14px 10px;
  border-radius: 8px;
  font-weight: 500;
  transition: 0.15s;
  cursor: pointer;
  min-height:72px;
  display:flex;
  flex-direction:column;
  justify-content:flex-start;
}
.day:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(13,38,59,0.05); }

.day-header {
  font-weight: 700;
  background: var(--blue-main);
  color: #fff;
  border-radius: 8px;
  padding: 10px 6px;
  cursor: default;
  text-align:center;
}

.holiday {
  background: linear-gradient(180deg, rgba(239,68,68,0.12), rgba(239,68,68,0.04));
  border: 1px solid rgba(239,68,68,0.14);
  color: var(--danger);
}

.holiday .date-num { color: var(--danger); font-weight:700; }

.x-mark {
  position:absolute;
  top:8px;
  right:8px;
  background: var(--danger);
  color:#fff;
  width:22px;
  height:22px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:6px;
  font-weight:700;
  font-size:12px;
}

.current-day {
  border: 2px solid var(--blue-main);
  background: linear-gradient(180deg, rgba(14,165,255,0.06), rgba(14,165,255,0.02));
}

.calendar-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--blue-main);
  margin-bottom: 4px;
}
.calendar-subtitle { color:#666; margin-bottom:10px; }

/* Sidebar */
.months-sidebar {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  padding: 18px;
  height: fit-content;
  position: sticky;
  top: 20px;
}

.sidebar-header {
  font-size: 16px;
  font-weight: 700;
  color: var(--blue-main);
  margin-bottom: 12px;
  text-align: center;
  border-bottom: 2px solid rgba(0,123,255,0.12);
  padding-bottom: 10px;
}

.year-section { margin-bottom: 14px; }

.year-label {
  font-size: 13px;
  font-weight: 700;
  color:#333;
  background:#fff;
  padding:6px 8px;
  border-radius:6px;
  margin-bottom:8px;
  text-align:center;
  border-left: 3px solid var(--blue-main);
}

.months-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.month-box {
  background: #fff;
  border: 1px solid var(--border);
  padding: 8px 6px;
  border-radius: 8px;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
  height:46px;
  display:flex;
  align-items:center;
  justify-content:center;
}

.month-box:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.06); border-color:var(--blue-main); }

.month-box.has-holiday { background: linear-gradient(180deg, rgba(255,77,77,0.12), #fff); border-color: rgba(255,77,77,0.2); color:var(--danger); }

.month-box.current { border: 2px solid var(--blue-main); background: linear-gradient(135deg, #e3f2fd, #bbdefb); }

.legend { margin-top:12px; font-size:13px; color:#666; }

.legend-item { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
.legend-color { width:14px; height:14px; border-radius:4px; }

/* responsive */
@media (max-width: 1000px) {
  .page-content { grid-template-columns: 1fr; padding:20px; }
  .months-sidebar { position: static; margin-top:16px; }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">
    <div class="calendar-main">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
          <div class="calendar-title">📅 Kalendar</div>
          <div class="calendar-subtitle">O‘ng tomondagi istalgan oynani bosib oching — bayram kunlari avtomatik ravishda qizil rangda ajratiladi.</div>
        </div>

     <div class="controls">
  <button class="ctrl-btn" id="prevMonthBtn">⬅️ Oldingi</button>
  <button class="ctrl-btn" id="todayBtn">📅 Bugun</button>
  <button class="ctrl-btn" id="nextMonthBtn">➡️ Keyingi</button>
</div>

      </div>

      <div id="calendar" class="calendar" role="grid" aria-label="Calendar"></div>
    </div>

    <aside class="months-sidebar" aria-label="Months sidebar">
      <div class="sidebar-header">📆 Oylar</div>
      <div id="monthsContainer"></div>

      <div class="legend">
        <div class="legend-item"><div class="legend-color" style="background:var(--danger)"></div><div>Bayram kuni</div></div>
        <div class="legend-item"><div class="legend-color" style="background:#e3f2fd; border:1px solid var(--blue-main)"></div><div>Joriy oy</div></div>
      </div>
    </aside>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const serverHolidays = [
        {date:'2025-01-01', title:'Yangi yil'},
        {date:'2025-03-08', title:'Xotin-qizlar kuni'},
        {date:'2025-03-21', title:'Navro‘z'},
        {date:'2025-09-01', title:'Mustaqillik kuni'},
        {date:'2025-12-31', title:'Yil oxiri'}
    ];

    const holidaysMap = {};
    serverHolidays.forEach(h => holidaysMap[h.date] = h.title);

    const weekDays = ["Dushanba","Seshanba","Chorshanba","Payshanba","Juma","Shanba","Yakshanba"];
    const monthNames = ["Yanvar","Fevral","Mart","Aprel","May","Iyun","Iyul","Avgust","Sentabr","Oktabr","Noyabr","Dekabr"];
    const monthShort = ["Yan","Fev","Mar","Apr","May","Iyun","Iyul","Avg","Sen","Okt","Noy","Dek"];

    const calendar = document.getElementById('calendar');
    const monthsContainer = document.getElementById('monthsContainer');

    let today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    function pad2(n){ return String(n).padStart(2,'0'); }
    function isoDate(y,m,d){ return `${y}-${pad2(m+1)}-${pad2(d)}`; }

    function renderCalendar(m, y){
        calendar.innerHTML = '';
        const title = document.createElement('div');
        title.style.gridColumn = '1 / -1';
        title.textContent = `${monthNames[m]} ${y}`;
        title.style.fontWeight = '700';
        title.style.color = 'var(--blue-main)';
        title.style.marginBottom = '6px';
        calendar.appendChild(title);

        weekDays.forEach(w=>{
            const el=document.createElement('div');
            el.className='day-header';
            el.textContent=w;
            calendar.appendChild(el);
        });

        const first=new Date(y,m,1);
        const last=new Date(y,m+1,0);
        let startIdx=(first.getDay()+6)%7;

        for(let i=0;i<startIdx;i++){
            const blank=document.createElement('div');
            blank.className='day';
            calendar.appendChild(blank);
        }

        for(let d=1;d<=last.getDate();d++){
            const dateKey=isoDate(y,m,d);
            const cell=document.createElement('div');
            cell.className='day';
            const num=document.createElement('div');
            num.textContent=d;
            num.className='date-num';
            cell.appendChild(num);

            if(holidaysMap[dateKey]){
                cell.classList.add('holiday');
                const x=document.createElement('div');
                x.className='x-mark';
                x.textContent='X';
                cell.appendChild(x);
                const t=document.createElement('div');
                t.textContent=holidaysMap[dateKey];
                t.style.fontSize='12px';
                t.style.fontWeight='700';
                t.style.color='var(--danger)';
                t.style.marginTop='8px';
                cell.appendChild(t);
            }

            const now=new Date();
            if(d===now.getDate()&&m===now.getMonth()&&y===now.getFullYear()) cell.classList.add('current-day');
            calendar.appendChild(cell);
        }
    }

    function renderMonths(){
        monthsContainer.innerHTML='';
        for(let y=currentYear-1;y<=currentYear+1;y++){
            const section=document.createElement('div');
            section.className='year-section';
            const label=document.createElement('div');
            label.className='year-label';
            label.textContent=y;
            section.appendChild(label);
            const grid=document.createElement('div');
            grid.className='months-grid';

            for(let m=0;m<12;m++){
                const box=document.createElement('div');
                box.className='month-box';
                box.dataset.month=m;
                box.dataset.year=y;
                box.textContent=monthShort[m];
                if(m===currentMonth && y===currentYear) box.classList.add('current');
                if(monthHasHoliday(m,y)) box.classList.add('has-holiday');
                box.onclick=function(){
                    currentMonth=m; currentYear=y;
                    renderCalendar(currentMonth,currentYear);
                    updateMonthsSelection();
                };
                grid.appendChild(box);
            }
            section.appendChild(grid);
            monthsContainer.appendChild(section);
        }
    }

    function monthHasHoliday(m,y){
        for(const key in holidaysMap){
            const [yy,mm]=key.split('-');
            if(+yy===y && +mm===m+1) return true;
        }
        return false;
    }

    function updateMonthsSelection(){
        document.querySelectorAll('.month-box').forEach(b=>b.classList.remove('current'));
        document.querySelectorAll('.month-box').forEach(b=>{
            if(+b.dataset.month===currentMonth && +b.dataset.year===currentYear)
                b.classList.add('current');
        });
    }

    document.getElementById('prevMonthBtn').onclick=function(){
        currentMonth--; if(currentMonth<0){currentMonth=11;currentYear--;}
        renderCalendar(currentMonth,currentYear); renderMonths(); updateMonthsSelection();
    };
    document.getElementById('nextMonthBtn').onclick=function(){
        currentMonth++; if(currentMonth>11){currentMonth=0;currentYear++;}
        renderCalendar(currentMonth,currentYear); renderMonths(); updateMonthsSelection();
    };
    document.getElementById('todayBtn').onclick=function(){
        today=new Date(); currentMonth=today.getMonth(); currentYear=today.getFullYear();
        renderCalendar(currentMonth,currentYear); renderMonths(); updateMonthsSelection();
    };

    renderMonths();
    renderCalendar(currentMonth,currentYear);
    updateMonthsSelection();
});
</script>
@endsection
