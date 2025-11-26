@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

:root {
  --blue-main: #244b8a;
  --blue-hover: #2563eb;
  --white: #ffffff;
  --glass-bg: rgba(255, 255, 255, 0.85); /* oq fon uchun shaffof oq */
  --glass-border: rgba(200, 200, 200, 0.4);
  --text-dark: #0f172a;
  --green: #16a34a;
  --red: #dc2626;
}

/* ===== Page Background ===== */
body {
  background: #e3e5ee; /* toza oq fon */
  font-family: 'Poppins', sans-serif;
}

/* ===== Page Wrapper ===== */
.page-wrapper {
  padding: 25px;
  max-width: 100%;
  overflow-x: hidden;
}

/* ===== Breadcrumb ===== */
.page-breadcrumb {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.breadcrumb-title {
  font-size: 22px;
  font-weight: 600;
  color: #111827;
}

/* ===== Buttons ===== */
.btn-custom {
  background: var(--blue-main);
  color: white;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  position: relative;
  overflow: hidden;
  transition: 0.3s;
}

.btn-custom::after {
  content: '';
  position: absolute;
  top:0; left:0; right:0; bottom:0;
  background: rgba(37,99,235,0.2);
  opacity: 0;
  transition: 0.3s ease;
  border-radius: 10px;
}

.btn-custom:hover::after {
  opacity: 1;
}

.btn-custom:hover {
  background: var(--blue-hover);
}

/* ===== Filter Box ===== */
.filter-box {
  background: var(--glass-bg);
  padding: 18px;
  border-radius: 14px;
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(10px);
  margin-bottom: 20px;
}

.filter-box select,
.filter-box input {
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
}

/* ===== Cards ===== */
.card {
  background: var(--glass-bg);
  border-radius: 16px;
  border: 1px solid var(--glass-border);
  padding: 18px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.08);
  transition: 0.3s ease;
  backdrop-filter: blur(12px);
}

.card:hover {
  transform: translateY(-3px);
}

/* ===== Summary Cards ===== */
.summary-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  padding: 16px 18px;
  border-radius: 14px;
  min-height: 120px;
  backdrop-filter: blur(12px);
}

.summary-card h5 {
  margin: 0;
  font-size: 16px;
}

.summary-card span {
  font-size: 13px;
  color: #334155;
}

/* ===== Table ===== */
.table-wrapper {
  margin-top: 20px;
}

.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 14px;
  overflow: hidden;
  background: var(--glass-bg);
  backdrop-filter: blur(10px);
  transition: 0.3s ease;
}

.table th {
  background: var(--blue-main);
  color: white;
  font-weight: 500;
  padding: 12px;
  text-align: left;
}

.table td {
  background: rgba(255, 255, 255, 0.8);
  color: var(--text-dark);
  padding: 10px;
  font-weight: 500;
  transition: 0.3s ease;
}

.table tbody tr:hover {
  background: rgba(37,99,235,0.15); /* hoverda biroz ko‘k */
}

/* ===== Responsive ===== */
@media(max-width: 768px){
  .page-breadcrumb {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  .summary-card {
    min-height: 100px;
  }
  .card {
    padding: 14px;
  }
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

        {{-- FILTER --}}
        <div class="filter-box">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Filial</label>
                    <select>
                        <option value="">Barchasi</option>
                        <option>Toshkent</option>
                        <option>Samarqand</option>
                        <option>Andijon</option>
                        <option>Farg'ona</option>
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
                    <button class="btn-custom w-100">Filtrlash</button>
                </div>
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <h5>Umumiy foydalanuvchilar</h5>
                    <span>Faol holatda</span>
                    <h3 style="color:#667eea;">84</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="summary-card">
                    <h5>Umumiy filiallar</h5>
                    <span>Respublika bo'yicha</span>
                    <h3 style="color:var(--green);">12</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="summary-card">
                    <h5>So‘nggi oydagi o‘sish</h5>
                    <span>Yangi foydalanuvchilar</span>
                    <h3 style="color:var(--red);">+18%</h3>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">📈 Filiallar bo‘yicha foydalanuvchilar soni</div>
                    <div class="card-body">
                        <canvas id="branchChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">👥 Foydalanuvchi faoliyati</div>
                    <div class="card-body">
                        <canvas id="userActivityChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-wrapper">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Filial nomi</th>
                    <th>Foydalanuvchi</th>
                    <th>Xizmatlar soni</th>
                    <th>Umumiy xarajat</th>
                    <th>Oxirgi faoliyat</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>Toshkent</td><td>Ali Karimov</td><td>5</td><td>1 250 000</td><td>2025-10-18</td></tr>
                    <tr><td>2</td><td>Samarqand</td><td>Dilnoza Nur</td><td>3</td><td>850 000</td><td>2025-10-19</td></tr>
                    <tr><td>3</td><td>Andijon</td><td>Aziz Rustamov</td><td>8</td><td>2 100 000</td><td>2025-10-17</td></tr>
                    <tr><td>4</td><td>Farg'ona</td><td>Madina Jo‘raeva</td><td>4</td><td>1 050 000</td><td>2025-10-15</td></tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false
    };

    new Chart(document.getElementById('branchChart'), {
        type: 'bar',
        data: {
            labels: ['Toshkent','Samarqand','Andijon','Farg‘ona','Namangan'],
            datasets: [{
                label: 'Foydalanuvchilar soni',
                data: [25,18,15,12,14],
                backgroundColor: ['#667eea','#764ba2','#f093fb','#4facfe','#00f2fe'],
                borderRadius: 10
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('userActivityChart'), {
        type: 'line',
        data: {
            labels: ['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt'],
            datasets: [{
                label: 'Faol foydalanuvchilar',
                data: [35,40,38,45,50,47,54,60,62,68],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102,126,234,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: chartOptions
    });

});
</script>
@endsection
