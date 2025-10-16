@extends('template')

@section('style')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

    :root {
        --text-color: #15172a;
        --blue-main: #1e3a8a;
        --blue-light: #2563eb;
        --blue-bg: #f0f6ff;
        --white: #ffffff;
        --border-color: #e5e7eb;
        --danger: #dc2626;
        --success: #16a34a;
    }

    body {
        font-family: "Inter", sans-serif;
        background: var(--blue-bg);
        color: var(--text-color);
    }

    .page-wrapper { padding: 24px; }

    .page-breadcrumb {
        background: var(--white);
        border-radius: 12px;
        padding: 12px 20px;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
    }

    .breadcrumb-title { font-weight: 600; color: var(--text-color); }

    .card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }

    .card:hover { box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15); }

    .card-body { padding: 25px; }

    h6 { color: var(--blue-main); font-weight: 600; }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        font-size: 14px;
    }

    thead { background: var(--blue-main); color: var(--white); }

    th, td {
        text-align: center;
        vertical-align: middle;
        padding: 12px 8px;
    }

    tbody tr { background: var(--white); transition: background 0.25s ease; }
    tbody tr:hover { background-color: #e0edff; }

    .fixed_header2 {
        position: sticky;
        top: 0;
        background: var(--blue-main);
        color: white;
        z-index: 10;
    }

    .btn-custom {
        background: var(--blue-light);
        border: none;
        color: white;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background: var(--blue-main);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.25);
    }

    /* Harakat tugmalari */
    .btn-action {
        border: none;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-edit {
        background: var(--success);
        color: white;
    }
    .btn-edit:hover {
        background: #15803d;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
    }

    .btn-delete {
        background: var(--danger);
        color: white;
    }
    .btn-delete:hover {
        background: #b91c1c;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3">Xodimlar</div>
            <a href="{{ route('admin.create') }}" class="btn btn-custom">+ Yangi xodim</a>
        </div>

        <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 text-uppercase">Xodimlar bazasi</h6>
        </div>
        <hr>

        <div class="card radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mytable" class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="fixed_header2 align-middle">#</th>
                                <th class="fixed_header2 align-middle">F.I.O</th>
                                <th class="fixed_header2 align-middle">Telefon</th>
                                <th class="fixed_header2 align-middle">Rol</th>
                                <th class="fixed_header2 align-middle">Login</th>
                                <th class="fixed_header2 align-middle">Filial</th>
                                <th class="fixed_header2 align-middle">Harakatlar</th>
                            </tr>
                        </thead>
                        <tbody id="data_list">
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>+998 {{ $user->phone }}</td>
                                    <td>{{ $user->roles[0]->name }}</td>
                                    <td>{{ $user->login }}</td>
                                    <td>{{ $user->filial ? $user->filial->name : 'Berilmagan' }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('admin.edit', $user->id) }}" class="btn-action btn-edit">
                                                <i class="bx bx-edit"></i> Tahrirlash
                                            </a>

                                            <form action="{{ route('admin.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Haqiqatan o‘chirmoqchimisiz?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete">
                                                    <i class="bx bx-trash"></i> O‘chirish
                                                </button>
                                            </form>
                                        </div>
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
<input type="hidden" id="sort_status_flag" value="asc">
@endsection
