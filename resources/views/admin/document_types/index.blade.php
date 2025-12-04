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
        --danger-color: #dc3545;
        --danger-hover: #b52b3a;
    }

    body {
        font-family: "Inter", "ui-sans-serif", "system-ui", "-apple-system", "Segoe UI", "Roboto", "Helvetica Neue", "Arial", "Noto Sans", "sans-serif";
        background: var(--blue-bg);
        color: var(--text-color);
        margin: 0;
        padding: 0;
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
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .card:hover { box-shadow: 0 6px 16px rgba(37, 99, 235, 0.15); }

    .card-body { padding: 25px; }

    h6 { color: var(--blue-main); font-weight: 600; letter-spacing: 0.5px; }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        font-size: 14px;
    }

    thead { background: var(--blue-main); color: var(--white); }

    th, td { text-align: center; vertical-align: middle; padding: 12px 8px; }

    tbody tr { background-color: var(--white); transition: background 0.25s ease; }
    tbody tr:hover { background-color: #e0edff; }

    .fixed_header2 { position: sticky; top: 0; background: var(--blue-main); color: white; z-index: 10; }

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

    .btn-danger {
        background: var(--danger-color);
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        transition: 0.2s;
    }

    .btn-danger:hover { background: var(--danger-hover); transform: translateY(-1px); }

    /* Modal styles */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-backdrop.active { display: flex; }

    .modal-content-custom {
        background: var(--white);
        padding: 20px 25px;
        border-radius: 12px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 6px 18px rgba(30,58,138,0.25);
        text-align: center;
        animation: fadeInScale 0.3s ease forwards;
    }

    .modal-content-custom h5 { margin-bottom: 15px; font-weight: 600; color: var(--text-color); }
    .modal-content-custom p { font-size: 14px; color: #555; margin-bottom: 20px; }

    .modal-actions button {
        min-width: 100px;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        margin: 0 5px;
        border: none;
        cursor: pointer;
        transition: 0.2s;
    }

    .modal-actions .btn-cancel { background: #6c757d; color: white; }
    .modal-actions .btn-cancel:hover { background: #5a6268; transform: translateY(-1px); }

    .modal-actions .btn-confirm { background: var(--danger-color); color: white; }
    .modal-actions .btn-confirm:hover { background: var(--danger-hover); transform: translateY(-1px); }

    @keyframes fadeInScale {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>
@endsection

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            <strong><i class="lni lni-checkmark-circle"></i></strong> 
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if (session('danger') || session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            <strong><i class="lni lni-warning"></i></strong> 
            {{ session('danger') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="page-breadcrumb d-flex align-items-center mb-3 justify-content-between">
            <div class="breadcrumb-title pe-3">Hujjat turi</div>
            <a href="{{ route('superadmin.document_type.create') }}" class="btn btn-custom">+ Yangi Hujjat turi</a>
        </div>

        <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 text-uppercase">Hujjat turi bazasi</h6>
        </div>
        <hr>

        <div class="card radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mytable" class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="fixed_header2 align-middle">#</th>
                                <th class="fixed_header2 align-middle">Hujjat turi nomi</th>
                           
                                <th class="fixed_header2 align-middle">Hujjat turi  izoh</th>
                                <th class="fixed_header2 align-middle">Harakatlar</th>
                            </tr>
                        </thead>
                        <tbody id="data_list">
                            @foreach ($documentTypes as $key=>$documentType)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $documentType->name }}</td>
                                <td>{{ $documentType->description }}</td>
                             
                                <td>
                                    <a class="btn btn-warning" href="{{ route('superadmin.document_type.edit',['document_type'=>$documentType->id]) }}">O'zgartirish</a>

                                    <!-- Delete Form -->
                                    <form action="{{ route('superadmin.document_type.destroy',['document_type'=>$documentType->id]) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-delete" data-name="{{ $documentType->name }}">O'chirish</button>
                                    </form>
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

<!-- Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-content-custom">
        <h5>Haqiqatan ham o'chirmoqchimisiz?</h5>
        <p id="modal-text">Bu amalni qaytarib bo'lmaydi.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelBtn">Bekor qilish</button>
            <button class="btn-confirm" id="confirmBtn">O'chirish</button>
        </div>
    </div>
</div>

@endsection

@section('scripte_include_end_body')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('deleteModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmBtn = document.getElementById('confirmBtn');
        let currentForm = null;

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const name = this.getAttribute('data-name');
                document.getElementById('modal-text').textContent = `"${name}" filialini o'chirishni tasdiqlaysizmi?`;
                modal.classList.add('active');
                currentForm = this.closest('form');
            });
        });

        cancelBtn.addEventListener('click', function() {
            modal.classList.remove('active');
            currentForm = null;
        });

        confirmBtn.addEventListener('click', function() {
            if(currentForm) currentForm.submit();
        });

        // ESC tugmasi bilan yopish
        window.addEventListener('keydown', function(e) {
            if(e.key === "Escape") modal.classList.remove('active');
        });
    });
</script>
@endsection
