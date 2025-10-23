@extends('template')

@section('style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

:root {
  --blue: #1e3a8a;
  --blue-light: #2563eb;
  --gray: #f3f4f6;
  --white: #fff;
  --border: #e5e7eb;
  --text: #111827;
  --green: #16a34a;
  --red: #dc2626;
  --orange: #f59e0b;
}

body {
  font-family: "Inter", sans-serif;
  background: var(--gray);
  color: var(--text);
  font-size: 15px;
}

.page-wrapper { padding: 24px; }

.card {
  background: var(--white);
  border-radius: 14px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s ease;
}
.card:hover { transform: translateY(-3px); }

.card-header {
  font-weight: 600;
  font-size: 16px;
  color: var(--blue);
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  background: #f9fafb;
  border-top-left-radius: 14px;
  border-top-right-radius: 14px;
}

.card-body { padding: 18px; }

.filter-box select,
.filter-box input {
  border-radius: 8px;
  border: 1px solid var(--border);
  padding: 9px 12px;
  width: 100%;
  font-size: 14px;
}

.filter-box button {
  border: none;
  background: var(--blue-light);
  color: var(--white);
  border-radius: 8px;
  padding: 10px 14px;
  width: 100%;
  font-weight: 500;
  transition: background 0.2s;
}
.filter-box button:hover { background: var(--blue); }

.summary-card {
  background: var(--white);
  padding: 18px;
  border-radius: 12px;
  border: 1px solid var(--border);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: 0.2s;
}
.summary-card:hover { transform: scale(1.02); }
.summary-card h5 { color: var(--blue); margin-bottom: 6px; font-size: 15px; }
.summary-card h3 { margin: 0; font-size: 22px; font-weight: 600; }

table th {
  background: var(--blue-light);
  color: white;
  text-align: center;
  vertical-align: middle;
  font-weight: 500;
}
table td {
  text-align: center;
  vertical-align: middle;
  font-size: 14px;
  background: #fff;
}
.badge {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 13px;
}
.bg-success { background: var(--green); color: #fff; }
.bg-warning { background: var(--orange); color: #fff; }
.bg-danger  { background: var(--red); color: #fff; }

@media (max-width: 768px) {
  .filter-box label { font-size: 13px; }
  .summary-card h3 { font-size: 18px; }
  .card-header { font-size: 14px; }
  .btn { font-size: 13px; }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
  <div class="page-content">

    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
      <h4 class="fw-semibold mb-2">📁 Hujjatlar statistikasi</h4>
      <button class="btn btn-primary px-3 py-2">+ Yangi hujjat</button>
    </div>

    <!-- 🔍 Filter qismi -->
    <div class="filter-box row g-3 mb-4">
      <div class="col-md-3">
        <label>Filial</label>
        <select>
          <option>Barchasi</option>
          <option>Toshkent</option>
          <option>Samarqand</option>
          <option>Andijon</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>Foydalanuvchi</label>
        <select>
          <option>Barchasi</option>
          <option>Ali Karimov</option>
          <option>Dilnoza Nur</option>
          <option>Aziz Rustamov</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>Sana oralig‘i</label>
        <input type="date">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button>Filtrlash</button>
      </div>
    </div>

    <!-- 📊 Umumiy statistika -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="summary-card">
          <h5>Joriy oyda hujjatlar</h5>
          <h3 style="color: var(--blue-light);">320 ta</h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-card">
          <h5>Umumiy hujjatlar</h5>
          <h3 style="color: var(--green);">4 850 ta</h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-card">
          <h5>Eng faol filial</h5>
          <h3 style="color: var(--orange);">Toshkent (34%)</h3>
        </div>
      </div>
    </div>

    <!-- 🏢 Filiallar bo‘yicha jadval -->
    <div class="card mb-4">
      <div class="card-header">🏢 Filiallar bo‘yicha hujjatlar statistikasi</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Filial</th>
              <th>Jami</th>
              <th>Tasdiqlangan</th>
              <th>Rad etilgan</th>
              <th>Jarayonda</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Toshkent</td><td>1800</td><td>1420</td><td>150</td><td>230</td></tr>
            <tr><td>Samarqand</td><td>950</td><td>720</td><td>80</td><td>150</td></tr>
            <tr><td>Andijon</td><td>710</td><td>540</td><td>60</td><td>110</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 👤 Eng faol foydalanuvchilar -->
    <div class="card mb-4">
      <div class="card-header">👤 Eng faol foydalanuvchilar</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Foydalanuvchi</th>
              <th>Filial</th>
              <th>Yuklagan</th>
              <th>Tasdiqlangan</th>
              <th>Faoliyat (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Ali Karimov</td><td>Toshkent</td><td>420</td><td>390</td><td>93%</td></tr>
            <tr><td>Dilnoza Nur</td><td>Samarqand</td><td>320</td><td>290</td><td>90%</td></tr>
            <tr><td>Aziz Rustamov</td><td>Andijon</td><td>280</td><td>250</td><td>89%</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 📄 Hujjat turlari -->
    <div class="card mb-4">
      <div class="card-header">📄 Hujjat turlari bo‘yicha taqsimot</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Hujjat turi</th>
              <th>Umumiy</th>
              <th>Tasdiqlangan</th>
              <th>Rad etilgan</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Shartnoma</td><td>1500</td><td>1300</td><td>200</td></tr>
            <tr><td>Hisobot</td><td>950</td><td>820</td><td>130</td></tr>
            <tr><td>Faktura</td><td>850</td><td>770</td><td>80</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 📋 Barcha hujjatlar -->
    <div class="card">
      <div class="card-header">📋 Barcha hujjatlar ro‘yxati</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Filial</th>
              <th>Foydalanuvchi</th>
              <th>Hujjat nomi</th>
              <th>Turi</th>
              <th>Sana</th>
              <th>Holat</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Toshkent</td><td>Ali Karimov</td><td>Shartnoma #125</td><td>Shartnoma</td><td>2025-10-17</td><td><span class="badge bg-success">Tasdiqlangan</span></td></tr>
            <tr><td>2</td><td>Samarqand</td><td>Dilnoza Nur</td><td>Faktura #458</td><td>Faktura</td><td>2025-10-16</td><td><span class="badge bg-warning">Jarayonda</span></td></tr>
            <tr><td>3</td><td>Andijon</td><td>Aziz Rustamov</td><td>Hisobot #773</td><td>Hisobot</td><td>2025-10-14</td><td><span class="badge bg-danger">Rad etilgan</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", () => {
  console.log("📊 Hujjatlar statistikasi yuklandi!");
});
</script>
@endsection
