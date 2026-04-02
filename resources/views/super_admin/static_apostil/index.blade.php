@extends('template')

@section('style')
    <style>
        /* Sizning style’ingiz qoladi (qisqartirmadim) */
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
            font-family: Inter, system-ui;
            background: var(--blue-bg);
            color: var(--text-color);
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
        }

        .card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.08);
            border: 1px solid var(--border-color);
        }

        .card-body {
            padding: 25px;
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

        th,
        td {
            text-align: center;
            vertical-align: middle;
            padding: 12px 8px;
        }

        tbody tr {
            background: var(--white);
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

        .btn-danger {
            background: var(--danger-color);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            transition: 0.2s;
        }

        .btn-danger:hover {
            background: var(--danger-hover);
            transform: translateY(-1px);
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal-content-custom {
            background: var(--white);
            padding: 20px 25px;
            border-radius: 12px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 6px 18px rgba(30, 58, 138, 0.25);
            text-align: center;
            animation: fadeInScale 0.3s ease forwards;
        }

        .modal-content-custom h5 {
            margin-bottom: 15px;
            font-weight: 600;
        }

        .modal-content-custom p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .modal-actions button {
            min-width: 110px;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-actions .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .modal-actions .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .modal-actions .btn-confirm {
            background: var(--danger-color);
            color: white;
        }

        .modal-actions .btn-confirm:hover {
            background: var(--danger-hover);
            transform: translateY(-1px);
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    background: linear-gradient(135deg, #2563eb, #1e3a8a);
    color: #ffffff !important;

    padding: 10px 18px;
    border-radius: 10px;

    font-size: 14px;
    font-weight: 500;
    text-decoration: none;

    box-shadow: 0 6px 14px rgba(30, 58, 138, 0.25);
    transition: all 0.3s ease;
}

.btn-back i {
    transition: transform 0.3s ease;
}

.btn-back:hover {
    background: linear-gradient(135deg, #1e3a8a, #172554);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(30, 58, 138, 0.35);
}

.btn-back:hover i {
    transform: translateX(-4px);
}

.btn-back:active {
    transform: scale(0.97);
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
                <div class="breadcrumb-title pe-3">Apostil Statik</div>
               <div class="page-breadcrumb mb-3">
    <a class="btn-back" href="{{ route('superadmin.direction_type.index') }}">
        <i class="lni lni-arrow-left"></i>
        <span>Orqaga</span>
    </a>
</div>
            </div>

            @php
                $groupTitles = [
                    1 => '1-guruh',
                    2 => '2-guruh',
                ];
            @endphp

            @foreach ($groups as $groupId => $items)
                <div class="d-flex align-items-center mb-2">
                    <h6 class="mb-0 text-uppercase">
                        {{ $groupTitles[$groupId] ?? 'Guruh: ' . $groupId }}
                    </h6>
                </div>
                <hr>

                <div class="card radius-10 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="fixed_header2">#</th>
                                        <th class="fixed_header2">Nomi</th>
                                        <th class="fixed_header2">Narx</th>
                                        <th class="fixed_header2">Kun</th>
                                        <th class="fixed_header2">Harakatlar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $k => $row)
                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->price }}</td>
                                            <td>{{ $row->days }}</td>
                                            <td class="d-flex gap-2 justify-content-center">

                                                {{-- EDIT MODAL BUTTON --}}
                                                <button type="button" class="btn btn-warning btn-edit"
                                                    data-id="{{ $row->id }}" data-name="{{ $row->name }}"
                                                    data-price="{{ $row->price }}" data-days="{{ $row->days }}"
                                                    data-update-url="{{ route('superadmin.apostil.update', $row->id) }}">
                                                    O'zgartirish
                                                </button>

                                                {{-- DELETE MODAL BUTTON --}}
                                                <button type="button" class="btn btn-danger btn-delete"
                                                    data-name="{{ $row->name }}"
                                                    data-delete-url="{{ route('superadmin.apostil.destroy', $row->id) }}">
                                                    O'chirish
                                                </button>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">Bu guruhda ma’lumot yo‘q.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
    <div class="modal-backdrop" id="editModal">
        <div class="modal-content-custom" style="text-align:left;">
            <h5 style="text-align:center;">Apostilni o'zgartirish</h5>

            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nomi</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Narx</label>
                    <input type="number" name="price" id="edit_price" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kun</label>
                    <input type="number" name="days" id="edit_days" class="form-control" required>
                </div>

                <div class="modal-actions" style="text-align:center;">
                    <button type="button" class="btn-cancel" id="editCancelBtn">Bekor qilish</button>
                    <button type="submit" class="btn-confirm" style="background:var(--blue-light);">
                        Saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal-backdrop" id="deleteModal">
        <div class="modal-content-custom">
            <h5>Haqiqatan ham o'chirmoqchimisiz?</h5>
            <p id="modal-text">Bu amalni qaytarib bo'lmaydi.</p>

            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Bekor qilish</button>
                    <button type="submit" class="btn-confirm" id="confirmBtn">O'chirish</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Modal -->
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

@section('script_include_end_body')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('deleteModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            const modalText = document.getElementById('modal-text');

            let currentForm = null;

            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const name = this.getAttribute('data-name') || '';
                    modalText.textContent = `"${name}" ni o'chirishni tasdiqlaysizmi?`;
                    modal.classList.add('active');
                    currentForm = this.closest('form');
                });
            });

            cancelBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                currentForm = null;
            });

            confirmBtn.addEventListener('click', function() {
                if (currentForm) currentForm.submit();
            });

            window.addEventListener('keydown', function(e) {
                if (e.key === "Escape") modal.classList.remove('active');
            });

            // backdrop bosilganda ham yopilsin (xohlasangiz)
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.classList.remove('active');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // DELETE modal
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const cancelBtn = document.getElementById('cancelBtn');
            const modalText = document.getElementById('modal-text');

            // EDIT modal
            const editModal = document.getElementById('editModal');
            const editForm = document.getElementById('editForm');
            const editCancelBtn = document.getElementById('editCancelBtn');

            const editName = document.getElementById('edit_name');
            const editPrice = document.getElementById('edit_price');
            const editDays = document.getElementById('edit_days');

            // OPEN DELETE
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const name = this.getAttribute('data-name') || '';
                    const url = this.getAttribute('data-delete-url');

                    modalText.textContent = `"${name}" ni o'chirishni tasdiqlaysizmi?`;
                    deleteForm.action = url;

                    deleteModal.classList.add('active');
                });
            });

            // OPEN EDIT
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const name = this.getAttribute('data-name') || '';
                    const price = this.getAttribute('data-price') || 0;
                    const days = this.getAttribute('data-days') || 0;
                    const url = this.getAttribute('data-update-url');

                    editName.value = name;
                    editPrice.value = price;
                    editDays.value = days;

                    editForm.action = url;
                    editModal.classList.add('active');
                });
            });

            // CLOSE buttons
            cancelBtn.addEventListener('click', () => deleteModal.classList.remove('active'));
            editCancelBtn.addEventListener('click', () => editModal.classList.remove('active'));

            // ESC close
            window.addEventListener('keydown', function(e) {
                if (e.key === "Escape") {
                    deleteModal.classList.remove('active');
                    editModal.classList.remove('active');
                }
            });

            // backdrop click close
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) deleteModal.classList.remove('active');
            });
            editModal.addEventListener('click', function(e) {
                if (e.target === editModal) editModal.classList.remove('active');
            });

        });
    </script>
@endsection
