@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

:root {
  --blue-main: #4e73df;
  --blue-hover: #2e59d9;
  --red-off: #e74a3b;
  --yellow-holiday: #f6c23e;
  --light-gray: #f8f9fc;
  --border: #dee2e6;
}

* { margin:0; padding:0; box-sizing:border-box; }
.page-wrapper { font-family: 'Poppins', sans-serif; background: var(--light-gray); min-height: 100vh; padding: 30px 20px; }
.page-content { background: #fff; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 30px; max-width: 900px; margin: 0 auto; }
h2 { margin-bottom: 25px; color: #333; text-align: center; font-size: 28px; }

.calendar-controls { display:flex; justify-content:center; align-items:center; margin-bottom:15px; gap:10px; }
.calendar-controls button { padding:6px 12px; font-size:16px; border:none; border-radius:8px; background:var(--blue-main); color:#fff; cursor:pointer; transition:0.3s; }
.calendar-controls button:hover { background:var(--blue-hover); }
.current-month { font-weight:600; font-size:18px; display:flex; align-items:center; gap:6px; }
.current-month i { color:var(--blue-main); }

.calendar { display:grid; grid-template-columns: repeat(7,1fr); gap:10px; margin-bottom:20px; }
.calendar.animate { animation: slideIn 0.4s ease-out; }
@keyframes slideIn { from { transform: translateX(50px); opacity:0; } to { transform:translateX(0); opacity:1; } }

.day, .day-header { text-align:center; padding:12px 0; border-radius:10px; font-weight:500; transition: all 0.3s ease; }
.day-header { background: linear-gradient(135deg, var(--blue-main), var(--blue-hover)); color:#fff; font-size:14px; text-transform:uppercase; letter-spacing:0.5px; }
.day { min-height:55px; display:flex; align-items:center; justify-content:center; font-size:15px; cursor:pointer; border:1px solid var(--border); }
.day:hover { transform:translateY(-4px) scale(1.05); box-shadow:0 8px 20px rgba(0,0,0,0.15); }
.day:active { transform:scale(0.95); box-shadow:0 4px 12px rgba(0,0,0,0.2); }

.day.working { background: var(--blue-main); color:#fff; }
.day.off { background: var(--red-off); color:#fff; }
.day.holiday { background: var(--yellow-holiday); color:#fff; animation: pulse 1.5s infinite; }
.day.selected { border:2px solid var(--blue-hover); }

@keyframes pulse {
  0% { box-shadow:0 0 0 0 rgba(246,194,62,0.7); }
  70% { box-shadow:0 0 10px 15px rgba(246,194,62,0); }
  100% { box-shadow:0 0 0 0 rgba(246,194,62,0); }
}

#monthStats { max-width:400px; margin:20px auto; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">
    <h2>📅 Calendar</h2>
    <div class="calendar-controls">
      <button id="prev-month"><i class="fa fa-chevron-left"></i></button>
      <div class="current-month" id="current-month"><i class="fa fa-calendar"></i></div>
      <button id="next-month"><i class="fa fa-chevron-right"></i></button>
    </div>
    <div id="calendar" class="calendar"></div>
    <canvas id="monthStats"></canvas>
  </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const calendar = document.getElementById('calendar');
  const currentMonthLabel = document.getElementById('current-month');
  const prevBtn = document.getElementById('prev-month');
  const nextBtn = document.getElementById('next-month');
  const ctx = document.getElementById('monthStats').getContext('2d');

  let holidays = JSON.parse(localStorage.getItem('holidays') || '["25.12.2025","01.01.2026"]'); // misol bayramlar
  let tasks = JSON.parse(localStorage.getItem('tasks') || '{}');
  let currentDate = new Date();
  let selectedCell = null;

  function formatDate(date) {
    const d = String(date.getDate()).padStart(2,'0');
    const m = String(date.getMonth()+1).padStart(2,'0');
    const y = date.getFullYear();
    return `${d}.${m}.${y}`;
  }

  function renderCalendar() {
    calendar.innerHTML = '';
    calendar.classList.remove('animate'); void calendar.offsetWidth; calendar.classList.add('animate');

    const y = currentDate.getFullYear();
    const m = currentDate.getMonth();
    const firstDay = (new Date(y,m,1).getDay()+6)%7;
    const daysInMonth = new Date(y,m+1,0).getDate();
    const weekDays = ['Dush','Sesh','Chor','Pay','Jum','Shan','Yak'];

    currentMonthLabel.innerHTML = `<i class="fa fa-calendar"></i> ${formatDate(currentDate)}`;

    weekDays.forEach(w => { const d = document.createElement('div'); d.textContent=w; d.className='day-header'; calendar.appendChild(d); });
    for(let i=0;i<firstDay;i++) calendar.appendChild(document.createElement('div'));

    for(let d=1; d<=daysInMonth; d++){
      const dateObj = new Date(y,m,d);
      const dateStr = formatDate(dateObj);
      const cell = document.createElement('div'); cell.className='day'; cell.textContent=d;

      if(dateObj.toDateString() === new Date().toDateString()) cell.style.border='2px solid var(--blue-hover)';

      // Rang berish
      if(holidays.includes(dateStr)){
        cell.classList.add('holiday');
      } else if(dateObj.getDay()===0 || dateObj.getDay()===6){
        cell.classList.add('off');
      } else {
        cell.classList.add('working');
      }

      if(selectedCell && selectedCell.dataset.date===dateStr) cell.classList.add('selected');

      cell.dataset.date=dateStr;
      cell.onclick=function(){
        if(selectedCell) selectedCell.classList.remove('selected');
        cell.classList.add('selected'); selectedCell=cell;

        const task = prompt("Sanaga bron qo‘shing:");
        if(task){
          tasks[dateStr] = tasks[dateStr] || [];
          tasks[dateStr].push(task);
          localStorage.setItem('tasks', JSON.stringify(tasks));
          cell.classList.add('holiday'); // bron qo‘shilgan kun bayramday
        }

        updateStats();
      }

      calendar.appendChild(cell);
    }

    updateStats();
  }

  function updateStats(){
    const y = currentDate.getFullYear();
    const m = currentDate.getMonth();
    const daysInMonth = new Date(y,m+1,0).getDate();
    let holidaysCount=0, workingCount=0, offCount=0, tasksCount=0;

    for(let d=1; d<=daysInMonth; d++){
      const dateObj = new Date(y,m,d);
      const dateStr = formatDate(dateObj);
      if(holidays.includes(dateStr)) holidaysCount++;
      else if(dateObj.getDay()===0 || dateObj.getDay()===6) offCount++;
      else workingCount++;
      if(tasks[dateStr]) tasksCount+=tasks[dateStr].length;
    }

    if(window.statsChart) window.statsChart.destroy();
    window.statsChart=new Chart(ctx,{
      type:'doughnut',
      data:{
        labels:['Bayram/Bron','Ish kuni','Dam olish','Tasklar'],
        datasets:[{
          data:[holidaysCount,workingCount,offCount,tasksCount],
          backgroundColor:['#f6c23e','#4e73df','#e74a3b','#E76F51'],
          borderColor:'#fff', borderWidth:2, hoverOffset:10
        }]
      },
      options:{
        responsive:true,
        plugins:{
          legend:{ position:'bottom', labels:{ color:'#5A3E36', font:{ size:14, weight:600 } } },
          tooltip:{ bodyColor:'#5A3E36', titleColor:'#000', backgroundColor:'rgba(255,248,240,0.95)', borderColor:'#F4A261', borderWidth:1 }
        }
      }
    });
  }

  prevBtn.addEventListener('click',()=>{ currentDate.setMonth(currentDate.getMonth()-1); currentDate.setDate(1); renderCalendar(); });
  nextBtn.addEventListener('click',()=>{ currentDate.setMonth(currentDate.getMonth()+1); currentDate.setDate(1); renderCalendar(); });

  renderCalendar();
});
</script>
@endsection
