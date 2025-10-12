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
    }

    body {
        font-family: "Inter", "ui-sans-serif", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Helvetica Neue", "Arial", "Noto Sans", "sans-serif", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        background: var(--blue-bg);
        color: var(--text-color);
        margin: 0;
        padding: 0;
    }

    .page-wrapper {
        padding: 24px;
    }


    .page-breadcrumb {
        background: var(--white);
        border-radius: 12px;
        padding: 12px 20px;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
    }

    .breadcrumb-title {
        font-weight: 600;
        color: var(--text-color);
    }

    
    .card {
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .card:hover {
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15);
    }

    .card-body {
        padding: 25px;
    }

    h6 {
        color: var(--blue-main);
        font-weight: 600;
        letter-spacing: 0.5px;
    }


    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        font-size: 14px;
    }

    thead {
        background: var(--blue-main);
        color: var(--white);
    }

    th, td {
        text-align: center;
        vertical-align: middle;
        padding: 12px 8px;
    }

    tbody tr {
        background-color: var(--white);
        transition: background 0.25s ease;
    }

    tbody tr:hover {
        background-color: #e0edff;
    }

    .fixed_header2 {
        position: sticky;
        top: 0;
        background: var(--blue-main);
        color: white;
        z-index: 10;
    }

    /* Custom button */
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
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3">Xodimlar</div>
            <button class="btn btn-custom">+ Yangi xodim</button>
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
                                <th id="sort_status" class="fixed_header2 align-middle">
                                    Status 
                                    <div id="icon_s"><i class="lni lni-arrow-up"></i></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="data_list">
                            <tr>
                                <td>1</td>
                                <td>Aliyev Jamshid</td>
                                <td><span class="badge bg-success">Faol</span></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Qodirova Dilnoza</td>
                                <td><span class="badge bg-danger">Bo‘shatilgan</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<input type="hidden" id="sort_status_flag" value="asc">
@endsection
