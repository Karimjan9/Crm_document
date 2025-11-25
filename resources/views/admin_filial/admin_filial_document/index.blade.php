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
  --warning: #facc15;
  --shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

body { font-family: "Inter", sans-serif; background: var(--bg); color: var(--text-color); }

/* Layout */
.page-wrapper { padding: 24px; }
.page-breadcrumb { background: var(--white); border-radius: 14px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow); border: 1px solid var(--border); }
.breadcrumb-title { font-size: 18px; font-weight: 600; color: var(--blue-dark); }

/* Buttons */
.btn-custom { background: linear-gradient(135deg, var(--blue-main), var(--blue-light)); border: none; color: #fff; padding: 10px 18px; border-radius: 10px; font-weight: 500; transition: 0.3s; }
.btn-custom:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }

.btn-action { border: none; border-radius: 8px; padding: 8px 12px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; font-weight: 500; cursor: pointer; transition: all 0.25s ease; }
.btn-edit { background: var(--success); color: white; }
.btn-edit:disabled { background: #a3a3a3; cursor: not-allowed; }
.btn-delete { background: var(--danger); color: white; }

/* Cards */
.card { background: var(--white); border-radius: 18px; box-shadow: var(--shadow); border: 1px solid var(--border); transition: 0.3s; }
.card:hover { box-shadow: 0 8px 18px rgba(37, 99, 235, 0.15); }
.card-body { padding: 28px; }

/* Table */
.table-responsive { max-height: 600px; overflow-y: auto; }
table { width: 100%; border-collapse: collapse; border-radius: 12px; font-size: 14px; }
thead { background: var(--blue-main); color: white; position: sticky; top: 0; z-index: 2; }
th, td { text-align: center; padding: 12px 10px; }
tbody tr { background: var(--white); transition: background 0.25s ease; }
tbody tr:hover { background: #eef4ff; }

/* Badges */
.badge { padding: 6px 10px; border-radius: 8px; font-size: 13px; font-weight: 500; }
.bg-success { background-color: var(--success); color: #fff; }
.bg-warning { background-color: var(--warning); color: #1e293b; }

/* Filter Panel */
.filter-panel { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-panel select, .filter-panel input { padding: 6px 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 14px; }

/* Responsive */
@media (max-width: 768px) {
  .page-wrapper { padding: 12px; }
  .breadcrumb-title { font-size: 16px; }
  .btn-custom { padding: 8px 14px; font-size: 13px; }
  table { font-size: 12px; }
  .card-body { padding: 16px; }
}
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex justify-content-between mb-3">
            <div class="breadcrumb-title">Hujjatlar ro‘yxati</div>
            <a href="{{ route('admin_filial.document.create') }}" class="btn btn-custom">+ Yangi hujjat</a>
        </div>

        <div class="card radius-10">
            <div class="card-body">

                <!-- Filter Panel -->
                <div class="filter-panel">
                    <input type="text" id="search-client" placeholder="Mijoz bo'yicha qidirish">
                    <input type="text" id="search-service" placeholder="Xizmat bo'yicha qidirish">
                    <select id="filter-status">
                        <option value="">Status bo‘yicha</option>
                        <option value="yopilgan">Yopilgan</option>
                        <option value="jarayonda">Jarayonda</option>
                    </select>
                    <select id="sort-deadline">
                        <option value="">Deadline tartib</option>
                        <option value="asc">O‘sish</option>
                        <option value="desc">Kamayish</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="documents-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document Code</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Harakatlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $key => $doc)
                                @php
                                    $statusText = $doc->paid_amount >= $doc->final_price ? 'yopilgan' : 'jarayonda';
                                    $editDisabled = \Carbon\Carbon::now()->diffInHours($doc->created_at) > 24 ? 'disabled' : '';
                                @endphp
                                <tr data-status="{{ $statusText }}" data-client="{{ $doc->client->name ?? '' }}" data-service="{{ $doc->service->name ?? '' }}" data-deadline="{{ $doc->deadline_time }}">
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $doc->document_code ?? '-' }}</td>
                                    <td>{{ $doc->client->name ?? '-' }}</td>
                                    <td>{{ $doc->service->name ?? '-' }}</td>
                                    <td>{{ $doc->deadline_time }}-kun</td>
                                    <td>
                                        @if($doc->paid_amount >= $doc->final_price)
                                            <span class="badge bg-success">Yopilgan</span>
                                        @else
                                            <span class="badge bg-warning">Jarayonda</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-edit" href="{{ route('admin_filial.document.edit',['document'=>$doc->id]) }}" {{ $editDisabled }}>O'zgartirish</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('documents-table').getElementsByTagName('tbody')[0];
    const clientInput = document.getElementById('search-client');
    const serviceInput = document.getElementById('search-service');
    const statusSelect = document.getElementById('filter-status');
    const sortDeadline = document.getElementById('sort-deadline');

    function filterAndSortTable() {
        const clientVal = clientInput.value.toLowerCase();
        const serviceVal = serviceInput.value.toLowerCase();
        const statusVal = statusSelect.value;
        const sortVal = sortDeadline.value;

        // Filter
        Array.from(table.rows).forEach(row => {
            const client = row.dataset.client.toLowerCase();
            const service = row.dataset.service.toLowerCase();
            const status = row.dataset.status.toLowerCase();
            let show = true;

            if (!client.includes(clientVal)) show = false;
            if (!service.includes(serviceVal)) show = false;
            if (statusVal && status !== statusVal) show = false;

            row.style.display = show ? '' : 'none';
        });

        // Sort
        let rowsArray = Array.from(table.rows).filter(row => row.style.display !== 'none');
        if (sortVal === 'asc') {
            rowsArray.sort((a,b) => parseInt(a.dataset.deadline) - parseInt(b.dataset.deadline));
        } else if (sortVal === 'desc') {
            rowsArray.sort((a,b) => parseInt(b.dataset.deadline) - parseInt(a.dataset.deadline));
        }
        rowsArray.forEach(row => table.appendChild(row));
    }

    clientInput.addEventListener('input', filterAndSortTable);
    serviceInput.addEventListener('input', filterAndSortTable);
    statusSelect.addEventListener('change', filterAndSortTable);
    sortDeadline.addEventListener('change', filterAndSortTable);
});
</script>
@endsection
