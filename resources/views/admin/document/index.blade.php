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
  --border: #e2e8f0;
  --green: #16a34a;
  --orange: #f59e0b;
  --red: #dc2626;
}

body {
  font-family: 'Poppins', sans-serif;
  background: var(--blue-bg);
  color: var(--text-color);
}

.page-wrapper {
  padding: 24px;
}

.card {
  background: var(--white);
  border-radius: 16px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  transition: 0.3s;
}
.card:hover {
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.card-header {
  font-weight: 600;
  font-size: 16px;
  color: var(--blue-main);
  border-bottom: 1px solid var(--border);
  padding: 16px 20px;
}

.card-body {
  padding: 20px;
}

.btn-custom {
  background: var(--blue-main);
  color: var(--white);
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  font-weight: 500;
  transition: 0.3s;
}
.btn-custom:hover {
  background: var(--blue-hover);
}

.filter-box {
  background: var(--white);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  padding: 20px;
  margin-bottom: 30px;
}
.filter-box select,
.filter-box input {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 8px 12px;
}
.filter-box button {
  width: 100%;
  background: var(--blue-main);
  color: var(--white);
  border: none;
  border-radius: 8px;
  padding: 10px;
  font-weight: 500;
  transition: 0.3s;
}
.filter-box button:hover {
  background: var(--blue-hover);
}

.summary-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: 0.3s;
}
.summary-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.summary-card h5 {
  color: var(--blue-main);
  margin-bottom: 4px;
}
.summary-card h3 {
  margin: 0;
}

.table thead {
  background: var(--blue-main);
  color: var(--white);
}
.table th, .table td {
  text-align: center;
  vertical-align: middle;
}

.badge {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 13px;
}
.bg-success { background: var(--green); color: #fff; }
.bg-warning { background: var(--orange); color: #fff; }
.bg-danger { background: var(--red); color: #fff; }
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 style="color: var(--blue-main); font-weight: 600;">📑 Hujjat boshqaruvi — Admin panel</h4>
      <button class="btn-custom">+ Yangi hujjat qo‘shish</button>
    </div>

    <!-- 🔍 Filtr qismi -->
    <div class="filter-box row g-3">
      <div class="col-md-2">
        <label>Filial</label>
        <select>
          <option>Barchasi</option>
          <option>Toshkent</option>
          <option>Samarqand</option>
          <option>Andijon</option>
        </select>
      </div>
      <div class="col-md-2">
        <label>Lavozim</label>
        <select>
          <option>Barchasi</option>
          <option>Bosh hujjatchi</option>
          <option>Operator</option>
          <option>Auditor</option>
        </select>
      </div>
      <div class="col-md-2">
        <label>Foydalanuvchi</label>
        <select>
          <option>Barchasi</option>
          <option>Ali Karimov</option>
          <option>Dilnoza Nur</option>
          <option>Aziz Rustamov</option>
        </select>
      </div>
      <div class="col-md-2">
        <label>Sana dan</label>
        <input type="date">
      </div>
      <div class="col-md-2">
        <label>Sana gacha</label>
        <input type="date">
      </div>
      <div class="col-md-2">
        <label>&nbsp;</label>
        <button>Filtrlash</button>
      </div>
    </div>

    <!-- 📊 Statistik kartalar -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="summary-card">
          <h5>Umumiy hujjatlar</h5>
          <h3 style="color: var(--blue-main);">4 850 ta</h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="summary-card">
          <h5>Joriy oyda yuklangan</h5>
          <h3 style="color: var(--orange);">430 ta</h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="summary-card">
          <h5>Tasdiqlangan</h5>
          <h3 style="color: var(--green);">4 120 ta</h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="summary-card">
          <h5>Rad etilgan</h5>
          <h3 style="color: var(--red);">230 ta</h3>
        </div>
      </div>
    </div>

    <!-- 🏢 Filiallar jadvali -->
    <div class="card mb-4">
      <div class="card-header">🏢 Filiallar faoliyati</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Filial</th>
              <th>Yuklangan</th>
              <th>Tasdiqlangan</th>
              <th>Rad etilgan</th>
              <th>Faollik (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Toshkent</td><td>1900</td><td>1720</td><td>80</td><td>91%</td></tr>
            <tr><td>Samarqand</td><td>1450</td><td>1320</td><td>130</td><td>88%</td></tr>
            <tr><td>Andijon</td><td>980</td><td>880</td><td>100</td><td>89%</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 👥 Xodimlar jadvali -->
    <div class="card mb-4">
      <div class="card-header">👥 Xodimlar bo‘yicha hujjat ishlash statistikasi</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Foydalanuvchi</th>
              <th>Lavozim</th>
              <th>Filial</th>
              <th>Yuklangan</th>
              <th>Tasdiqlangan</th>
              <th>Jarayonda</th>
              <th>Rad etilgan</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Ali Karimov</td><td>Bosh hujjatchi</td><td>Toshkent</td><td>320</td><td>290</td><td>20</td><td>10</td></tr>
            <tr><td>Dilnoza Nur</td><td>Operator</td><td>Samarqand</td><td>280</td><td>250</td><td>18</td><td>12</td></tr>
            <tr><td>Aziz Rustamov</td><td>Auditor</td><td>Andijon</td><td>260</td><td>230</td><td>20</td><td>10</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 📋 Admin o‘z hujjatlari -->
    <div class="card mb-4">
      <div class="card-header">📋 Siz ishlagan hujjatlar (Admin)</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Hujjat nomi</th>
              <th>Turi</th>
              <th>Filial</th>
              <th>Sana</th>
              <th>Holat</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Shartnoma #452</td><td>Shartnoma</td><td>Toshkent</td><td>2025-10-16</td><td><span class="badge bg-success">Tasdiqlangan</span></td></tr>
            <tr><td>2</td><td>Hisobot #788</td><td>Hisobot</td><td>Samarqand</td><td>2025-10-14</td><td><span class="badge bg-warning">Jarayonda</span></td></tr>
            <tr><td>3</td><td>Faktura #332</td><td>Faktura</td><td>Andijon</td><td>2025-10-10</td><td><span class="badge bg-danger">Rad etilgan</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
