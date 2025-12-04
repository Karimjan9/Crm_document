  @extends('template')

  @section('style')

  <style>

  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

  :root {
    --text-color: #1e293b;
    --blue-main: #2563eb;
    --blue-dark: #1e3a8a;
    --blue-light: #3b82f6;
    --bg: #f8fafc;
    --white: #ffffff;
    --border: #e2e8f0;
    --danger: #ef4444;
    --success: #22c55e;
    --shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  }

  body {
    font-family: "Inter", sans-serif;
    background: var(--bg);
    color: var(--text-color);
  }

  /* === Layout === */
  .page-wrapper {
    padding: 24px;
  }

  .page-breadcrumb {
    background: var(--white);
    border-radius: 14px;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .breadcrumb-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--blue-dark);
  }

  /* === Filter Section === */
  .filter-section {
    background: var(--white);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .filter-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--blue-dark);
  }

  .filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
  }

  .filter-group label {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--text-color);
  }

  .filter-group input,
  .filter-group select {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-family: "Inter", sans-serif;
    transition: 0.2s;
  }

  .filter-group input:focus,
  .filter-group select:focus {
    outline: none;
    border-color: var(--blue-main);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  .filter-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
  }

  /* === Buttons === */
  .btn-custom {
    background: linear-gradient(135deg, var(--blue-main), var(--blue-light));
    border: none;
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 500;
    transition: 0.3s;
    cursor: pointer;
  }

  .btn-custom:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  .btn-reset {
    background: #94a3b8;
    border: none;
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 500;
    transition: 0.3s;
    cursor: pointer;
  }

  .btn-reset:hover {
    background: #64748b;
    transform: translateY(-2px);
  }

  /* === Cards === */
  .card {
    background: var(--white);
    border-radius: 18px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: 0.3s;
  }

  .card:hover {
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.15);
  }

  .card-body {
    padding: 28px;
  }

  /* === Table === */
  table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    font-size: 14px;
  }

  thead {
    background: var(--blue-main);
    color: white;
  }

  th, td {
    text-align: center;
    padding: 12px 10px;
  }

  tbody tr {
    background: var(--white);
    transition: background 0.25s ease;
  }

  tbody tr:hover {
    background: #eef4ff;
  }

  .no-data {
    text-align: center;
    padding: 30px;
    color: #94a3b8;
  }

  /* === Responsive === */
  @media (max-width: 768px) {
    .page-wrapper { padding: 12px; }
    .breadcrumb-title { font-size: 16px; }
    .btn-custom { padding: 8px 14px; font-size: 13px; }
    table { font-size: 12px; }
    .card-body { padding: 16px; }
    .filter-row {
      grid-template-columns: 1fr;
    }
  }

  </style>

  @endsection

  @section('body')

  <div class="page-wrapper">
      <div class="page-content">

          <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
              <div class="breadcrumb-title pe-3">Admin Panel - Xodimlar va Hujjatlar</div>
          </div>

          <!-- FILTER SECTION -->
          <div class="filter-section">
              <div class="filter-title">🔍 Filtir sozlamalari</div>
              
              <div class="filter-row">
                  <div class="filter-group">
                      <label>Turini tanlang:</label>
                      <select id="typeFilter" onchange="applyFilters()">
                          <option value="">Hammasini ko'rsatish</option>
                          <option value="employee">Xodimlar</option>
                          <option value="document">Hujjatlar</option>
                      </select>
                  </div>

                  <div class="filter-group">
                      <label>Ism yoki nomi:</label>
                      <input type="text" id="nameFilter" placeholder="Izlash..." onkeyup="applyFilters()">
                  </div>

                  <div class="filter-group">
                      <label>Telefon:</label>
                      <input type="text" id="phoneFilter" placeholder="+998..." onkeyup="applyFilters()">
                  </div>

                  <div class="filter-group">
                      <label>Status:</label>
                      <select id="statusFilter" onchange="applyFilters()">
                          <option value="">Barcha status</option>
                          <option value="active">Faol</option>
                          <option value="inactive">Nofaol</option>
                      </select>
                  </div>
              </div>

              <div class="filter-buttons">
                  <button class="btn-custom" onclick="applyFilters()">🔎 Filtrlash</button>
                  <button class="btn-reset" onclick="resetFilters()">↻ Qayta tiklash</button>
              </div>
          </div>

          <!-- STATISTICS SECTION -->
          <div class="d-flex align-items-center mb-2">
              <h6 class="mb-0 text-uppercase">Xodimlar va Hujjatlar Bazasi</h6>
          </div>
          <hr>

          <!-- TABLE SECTION -->
          <div class="card radius-10">
              <div class="card-body">
                  <div class="table-responsive">
                      <table id="mytable" class="table table-bordered align-middle mb-0">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Tur</th>
                                  <th>F.I.O / Nomi</th>
                                  <th>Telefon</th>
                                  <th>Status</th>
                                
                              </tr>
                          </thead>
                          <tbody id="data_list">
                              @foreach ($users as $key => $user)
                                  <tr class="data-row" data-type="employee" data-name="{{ $user->name }}" data-phone="{{ $user->phone }}" data-status="active">
                                      <td>{{ $key + 1 }}</td>
                                      <td><span class="badge bg-info">Xodim</span></td>
                                      <td>{{ $user->name }}</td>
                                      <td>+998 {{ $user->phone }}</td>
                                      <td><span class="badge bg-success">Faol</span></td>
                                      {{-- <td>
                                          <button class="btn btn-sm btn-primary">✏️ Tahrirlash</button>
                                          <button class="btn btn-sm btn-danger">🗑️ O'chirish</button>
                                      </td> --}}
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                      <div id="noData" class="no-data" style="display:none;">
                          ℹ️ Hech qanday ma'lumot topilmadi
                      </div>
                  </div>
              </div>
          </div>

      </div>
  </div>

  <input type="hidden" id="sort_status_flag" value="asc">

  <script>

  function applyFilters() {
      const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
      const nameFilter = document.getElementById('nameFilter').value.toLowerCase();
      const phoneFilter = document.getElementById('phoneFilter').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
      
      const rows = document.querySelectorAll('.data-row');
      let visibleCount = 0;

      rows.forEach(row => {
          const rowType = row.getAttribute('data-type').toLowerCase();
          const rowName = row.getAttribute('data-name').toLowerCase();
          const rowPhone = row.getAttribute('data-phone').toLowerCase();
          const rowStatus = row.getAttribute('data-status').toLowerCase();

          let matches = true;

          if (typeFilter && rowType !== typeFilter) matches = false;
          if (nameFilter && !rowName.includes(nameFilter)) matches = false;
          if (phoneFilter && !rowPhone.includes(phoneFilter)) matches = false;
          if (statusFilter && rowStatus !== statusFilter) matches = false;

          if (matches) {
              row.style.display = '';
              visibleCount++;
          } else {
              row.style.display = 'none';
          }
      });

      const noDataMsg = document.getElementById('noData');
      if (visibleCount === 0) {
          noDataMsg.style.display = 'block';
      } else {
          noDataMsg.style.display = 'none';
      }
  }

  function resetFilters() {
      document.getElementById('typeFilter').value = '';
      document.getElementById('nameFilter').value = '';
      document.getElementById('phoneFilter').value = '';
      document.getElementById('statusFilter').value = '';
      
      document.querySelectorAll('.data-row').forEach(row => {
          row.style.display = '';
      });
      
      document.getElementById('noData').style.display = 'none';
  }

  </script>

  @endsection