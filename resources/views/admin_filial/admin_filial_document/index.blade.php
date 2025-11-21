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

/* === Buttons === */
.btn-custom {
  background: linear-gradient(135deg, var(--blue-main), var(--blue-light));
  border: none;
  color: #fff;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 500;
  transition: 0.3s;
}
.btn-custom:hover {
  opacity: 0.9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

/* === Action Buttons === */
.btn-action {
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.25s ease;
}
.btn-edit {
  background: var(--success);
  color: white;
}
.btn-edit:hover {
  background: #16a34a;
  transform: translateY(-2px);
}
.btn-delete {
  background: var(--danger);
  color: white;
}
.btn-delete:hover {
  background: #b91c1c;
  transform: translateY(-2px);
}

/* === Icons modern look === */
.btn-action i {
  font-size: 16px;
}
.bx-edit::before {
  content: "✏️";
}
.bx-trash::before {
  content: "🗑️";
}

/* === Responsive === */
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mijoz</th>
                                <th>Xizmat</th>
                                <th>Umumiy summa</th>
                                <th>To‘langan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $key => $doc)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $doc->client->full_name ?? '-' }}</td>
                                    <td>{{ $doc->service->name ?? '-' }}</td>
                                    <td>{{ number_format($doc->final_price) }} so‘m</td>
                                    <td>{{ number_format($doc->paid_amount) }} so‘m</td>
                                    <td>
                                        @if($doc->paid_amount >= $doc->final_price)
                                            <span class="badge bg-success">Yopilgan</span>
                                        @else
                                            <span class="badge bg-warning">Jarayonda</span>
                                        @endif
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
@endsection
