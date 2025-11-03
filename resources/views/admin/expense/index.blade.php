@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

:root {
  --text-color: #1e293b;
  --blue-main: #1d4ed8;
  --blue-hover: #1e40af;
  --blue-bg: #f3f6fc;
  --white: #ffffff;
  --border-color: #e2e8f0;
  --green: #16a34a;
  --red: #dc2626;
}

body {
  font-family: 'Poppins', sans-serif;
  background: var(--blue-bg);
  color: var(--text-color);
}

.page-wrapper { padding: 28px; }

.page-breadcrumb {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-breadcrumb .breadcrumb-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--blue-main);
}

.btn-custom {
  background: var(--blue-main);
  color: var(--white);
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
  transition: 0.3s;
  border: none;
}

.btn-custom:hover { background: var(--blue-hover); }

.card {
  background: var(--white);
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.08);
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.card:hover { box-shadow: 0 6px 18px rgba(29, 78, 216, 0.12); }

.card-header {
  font-weight: 600;
  color: var(--blue-main);
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
  font-size: 16px;
}

.card-body { padding: 20px; }

.summary-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--white);
  padding: 22px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 12px rgba(0,0,0,0.08);
}

.summary-card h5 {
  margin-bottom: 6px;
  font-size: 18px;
  color: var(--blue-main);
}

.summary-card span {
  font-size: 14px;
  color: #64748b;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
  gap: 18px;
}

.filter-box {
  background: var(--white);
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  margin-bottom: 28px;
}

.filter-box label {
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 4px;
  display: block;
}

.filter-box select,
.filter-box input {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 9px 12px;
  width: 100%;
  font-size: 14px;
  color: var(--text-color);
}

.filter-box button {
  background: var(--blue-main);
  color: var(--white);
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  width: 100%;
  font-weight: 500;
  transition: 0.3s;
}

.filter-box button:hover { background: var(--blue-hover); }

.table th {
  background: var(--blue-main) !important;
  color: var(--white);
  font-weight: 500;
}

.table td {
  font-size: 14px;
  color: var(--text-color);
}

@media (max-width: 768px) {
  .page-wrapper { padding: 16px; }
  .card-header { font-size: 15px; }
  .breadcrumb-title { font-size: 17px; }
  .summary-card { flex-direction: column; align-items: flex-start; gap: 6px; }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">

    <div class="page-breadcrumb">
      <div class="breadcrumb-title">📊 Filial va foydalanuvchilar statistikasi</div>
      <button class="btn-custom">+ Yangi foydalanuvchi</button>
    </div>

    <!-- Filter box -->
    <div class="filter-box row g-3 align-items-end">
      <div class="col-md-3">
        <label>Filial</label>
        <select>
          <option value="">Barchasi</option>
          <option>Toshkent</option>
          <option>Samarqand</option>
          <option>Andijon</option>
          <option>Farg‘ona</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>Foydalanuvchi</label>
        <select>
          <option value="">Barchasi</option>
          <option>Ali Karimov</option>
          <option>Dilnoza Nur</option>
          <option>Aziz Rustamov</option>
        </select>
      </div>
      <div class="col-md-2">
        <label>Dan</label>
        <input type="date">
      </div>
      <div class="col-md-2">
        <label>Gacha</label>
        <input type="date">
      </div>
      <div class="col-md-2">
        <button>Filtrlash</button>
      </div>
    </div>

    <!-- Summary -->
    <div class="stat-grid mb-4">
      <div class="summary-card">
        <div>
          <h5>Umumiy foydalanuvchilar</h5>
          <span>Faol holatda</span>
        </div>
        <div class="text-end">
          <h3 style="color: var(--blue-main); font-weight:600;">84</h3>
        </div>
      </div>

      <div class="summary-card">
        <div>
          <h5>Umumiy filiallar</h5>
          <span>Respublika bo‘yicha</span>
        </div>
        <div class="text-end">
          <h3 style="color: var(--green); font-weight:600;">12</h3>
        </div>
      </div>

      <div class="summary-card">
        <div>
          <h5>So‘nggi oydagi o‘sish</h5>
          <span>Yangi foydalanuvchilar</span>
        </div>
        <div class="text-end">
          <h3 style="color: var(--red); font-weight:600;">+18%</h3>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-header">📈 Filiallar bo‘yicha foydalanuvchilar soni</div>
          <div class="card-body">
            <canvas id="branchChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-header">👥 Foydalanuvchi faoliyati (oylik)</div>
          <div class="card-body">
            <canvas id="userActivityChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card mt-4">
      <div class="card-header">📋 Foydalanuvchi va filial kesimida batafsil jadval</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Filial nomi</th>
              <th>Foydalanuvchi</th>
              <th>Xizmatlar soni</th>
              <th>Umumiy xarajat (so‘m)</th>
              <th>Oxirgi faoliyat</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Toshkent</td><td>Ali Karimov</td><td>5</td><td>1 250 000</td><td>2025-10-18</td></tr>
            <tr><td>2</td><td>Samarqand</td><td>Dilnoza Nur</td><td>3</td><td>850 000</td><td>2025-10-19</td></tr>
            <tr><td>3</td><td>Andijon</td><td>Aziz Rustamov</td><td>8</td><td>2 100 000</td><td>2025-10-17</td></tr>
            <tr><td>4</td><td>Farg‘ona</td><td>Madina Jo‘raeva</td><td>4</td><td>1 050 000</td><td>2025-10-15</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripte_include_end_body')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

  // 📊 Filiallar bo‘yicha foydalanuvchilar soni
  new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: {
      labels: ['Toshkent', 'Samarqand', 'Andijon', 'Farg‘ona', 'Namangan'],
      datasets: [{
        label: 'Foydalanuvchilar soni',
        data: [25, 18, 15, 12, 14],
        backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed'],
        borderRadius: 8
      }]
    },
    options: {
      plugins: { legend: { display: false }},
      scales: { y: { beginAtZero: true, ticks: { stepSize: 5 }}}
    }
  });

  // 👥 Foydalanuvchi faoliyati (oylik)
  new Chart(document.getElementById('userActivityChart'), {
    type: 'line',
    data: {
      labels: ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt'],
      datasets: [{
        label: 'Faol foydalanuvchilar',
        data: [35, 40, 38, 45, 50, 47, 54, 60, 62, 68],
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37,99,235,0.1)',
        fill: true,
        tension: 0.35
      }]
    },
    options: {
      plugins: { legend: { display: false }},
      scales: { y: { beginAtZero: true }}
    }
  });

});
</script>
@endsection
